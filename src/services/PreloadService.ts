/**
 * PreloadService - 关键API数据预加载服务
 * 
 * 这个服务在应用初始化时预加载关键API数据，
 * 以减少用户等待时间，提高应用响应速度。
 */

import { apiCache, CacheExpiry } from './CacheService';
import { performanceMonitor, MarkType } from './PerformanceMonitor';
import { batchApi } from './BatchRequestService';

// 预加载配置
interface PreloadConfig {
  // 是否启用预加载
  enabled: boolean;
  
  // 预加载超时时间（毫秒）
  timeout: number;
  
  // 预加载的API端点配置
  endpoints: PreloadEndpoint[];
  
  // 预加载优先级（越小越优先）
  priority: number;
  
  // 是否使用批量请求
  useBatch: boolean;
  
  // 重试配置
  retry: {
    // 最大重试次数
    maxRetries: number;
    // 重试延迟（毫秒）
    delay: number;
    // 重试延迟增长因子
    backoffFactor: number;
  };
  
  // 是否在开发环境中显示日志
  debug: boolean;
}

// 预加载端点配置
interface PreloadEndpoint {
  // 端点URL
  url: string;
  
  // 请求方法
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
  
  // 请求参数
  params?: Record<string, any>;
  
  // 请求头
  headers?: Record<string, any>;
  
  // 缓存过期时间（毫秒）
  cacheExpiry: number;
  
  // 预加载优先级（越小越优先）
  priority: number;
  
  // 是否关键数据（关键数据加载失败会提示错误）
  isCritical: boolean;
  
  // 条件函数（返回true时才预加载）
  condition?: () => boolean;
  
  // 成功回调
  onSuccess?: (data: any) => void;
  
  // 错误回调
  onError?: (error: any) => void;
  
  // 唯一标识（用于缓存）
  id: string;
}

class PreloadService {
  private config: PreloadConfig;
  private preloadQueue: PreloadEndpoint[] = [];
  private preloadInProgress: boolean = false;
  private preloadCompleted: boolean = false;
  private preloadResults: Record<string, { success: boolean; data?: any; error?: any }> = {};
  private abortControllers: Record<string, AbortController> = {};
  private static instance: PreloadService;
  
  constructor(config?: Partial<PreloadConfig>) {
    this.config = {
      enabled: true,
      timeout: 10000,
      endpoints: [],
      priority: 5,
      useBatch: true,
      retry: {
        maxRetries: 2,
        delay: 1000,
        backoffFactor: 1.5,
      },
      debug: process.env.NODE_ENV === 'development',
      ...config
    };
  }

  /**
   * 获取单例实例
   */
  public static getInstance(config?: Partial<PreloadConfig>): PreloadService {
    if (!PreloadService.instance) {
      PreloadService.instance = new PreloadService(config);
    }
    return PreloadService.instance;
  }

  /**
   * 更新服务配置
   */
  public updateConfig(config: Partial<PreloadConfig>): void {
    this.config = {
      ...this.config,
      ...config
    };
  }

  /**
   * 添加预加载端点
   */
  public addEndpoint(endpoint: PreloadEndpoint): void {
    const existingIndex = this.config.endpoints.findIndex(e => e.id === endpoint.id);
    
    if (existingIndex >= 0) {
      this.config.endpoints[existingIndex] = endpoint;
    } else {
      this.config.endpoints.push(endpoint);
    }
    
    // 如果预加载已经完成，但刚添加了新端点，则将其标记为未完成
    if (this.preloadCompleted) {
      this.preloadCompleted = false;
    }
  }

  /**
   * 预加载特定类型的端点
   * @param type 端点类型（如'product', 'category'等）
   */
  public preloadByType(type: string): Promise<void> {
    const endpoints = this.config.endpoints.filter(e => e.id.startsWith(`${type}:`));
    return this.preloadEndpoints(endpoints);
  }

  /**
   * 预加载所有配置的端点
   */
  public preloadAll(): Promise<void> {
    if (!this.config.enabled) {
      this.log('预加载已禁用，跳过');
      return Promise.resolve();
    }
    
    if (this.preloadInProgress) {
      this.log('预加载已在进行中，跳过');
      return Promise.resolve();
    }
    
    if (this.preloadCompleted) {
      this.log('预加载已完成，跳过');
      return Promise.resolve();
    }
    
    this.log('开始预加载');
    this.preloadInProgress = true;
    
    // 按优先级排序端点
    const sortedEndpoints = [...this.config.endpoints].sort((a, b) => a.priority - b.priority);
    
    // 过滤掉不满足条件的端点
    const filteredEndpoints = sortedEndpoints.filter(endpoint => {
      if (endpoint.condition) {
        return endpoint.condition();
      }
      return true;
    });
    
    this.preloadQueue = filteredEndpoints;
    
    // 开始预加载
    return this.processPreloadQueue()
      .then(() => {
        this.preloadInProgress = false;
        this.preloadCompleted = true;
        this.log('预加载完成');
      })
      .catch(error => {
        this.preloadInProgress = false;
        this.log('预加载失败', error);
      });
  }

  /**
   * 预加载指定的端点
   */
  public preloadEndpoints(endpoints: PreloadEndpoint[]): Promise<void> {
    if (!this.config.enabled || endpoints.length === 0) {
      return Promise.resolve();
    }
    
    // 按优先级排序端点
    const sortedEndpoints = [...endpoints].sort((a, b) => a.priority - b.priority);
    
    // 过滤掉不满足条件的端点
    const filteredEndpoints = sortedEndpoints.filter(endpoint => {
      if (endpoint.condition) {
        return endpoint.condition();
      }
      return true;
    });
    
    return this.processEndpoints(filteredEndpoints);
  }

  /**
   * 处理预加载队列
   */
  private processPreloadQueue(): Promise<void> {
    if (this.preloadQueue.length === 0) {
      return Promise.resolve();
    }
    
    // 如果支持批量请求，且有多个GET请求，尝试使用批量请求
    if (this.config.useBatch && this.preloadQueue.filter(e => e.method === 'GET').length > 1) {
      return this.processBatchPreload();
    }
    
    // 否则，逐个处理请求
    return this.processIndividualPreload();
  }

  /**
   * 使用批量请求处理预加载
   */
  private processBatchPreload(): Promise<void> {
    // 分离GET请求和非GET请求
    const getRequests = this.preloadQueue.filter(e => e.method === 'GET');
    const nonGetRequests = this.preloadQueue.filter(e => e.method !== 'GET');
    
    // 准备批量请求
    const batchRequests = getRequests.map(endpoint => ({
      path: endpoint.url,
      method: endpoint.method,
      params: endpoint.params,
      headers: endpoint.headers,
      priority: endpoint.priority
    }));
    
    // 性能监控
    const performanceId = performanceMonitor.markStart(MarkType.API_REQUEST, 'preload:batch', {
      count: batchRequests.length
    });
    
    return batchApi.batch(batchRequests)
      .then(responses => {
        // 处理批量响应
        getRequests.forEach((endpoint, index) => {
          const response = responses[index];
          
          // 缓存响应
          apiCache.set(`preload:${endpoint.id}`, response, endpoint.cacheExpiry);
          
          // 记录结果
          this.preloadResults[endpoint.id] = { 
            success: true, 
            data: response 
          };
          
          // 执行成功回调
          if (endpoint.onSuccess) {
            endpoint.onSuccess(response);
          }
        });
        
        performanceMonitor.markEnd(performanceId, { success: true });
        
        // 从队列中移除已处理的GET请求
        this.preloadQueue = nonGetRequests;
        
        // 继续处理剩余的非GET请求
        return this.processPreloadQueue();
      })
      .catch(error => {
        performanceMonitor.markEnd(performanceId, { 
          success: false,
          error: error.message
        });
        
        this.log('批量预加载失败，回退到单个请求', error);
        
        // 回退到单个请求处理
        return this.processIndividualPreload();
      });
  }

  /**
   * 逐个处理预加载请求
   */
  private processIndividualPreload(): Promise<void> {
    if (this.preloadQueue.length === 0) {
      return Promise.resolve();
    }
    
    // 取出第一个端点
    const endpoint = this.preloadQueue.shift()!;
    
    // 检查缓存
    const cachedData = apiCache.get(`preload:${endpoint.id}`);
    if (cachedData) {
      this.log(`使用缓存数据: ${endpoint.id}`);
      
      // 记录结果
      this.preloadResults[endpoint.id] = { 
        success: true, 
        data: cachedData 
      };
      
      // 执行成功回调
      if (endpoint.onSuccess) {
        endpoint.onSuccess(cachedData);
      }
      
      // 继续处理下一个
      return this.processPreloadQueue();
    }
    
    // 性能监控
    const performanceId = performanceMonitor.markStart(MarkType.API_REQUEST, `preload:${endpoint.id}`, {
      url: endpoint.url,
      method: endpoint.method
    });
    
    // 创建AbortController
    const abortController = new AbortController();
    this.abortControllers[endpoint.id] = abortController;
    
    // 设置超时
    const timeoutId = setTimeout(() => {
      abortController.abort();
      performanceMonitor.markEnd(performanceId, { 
        success: false, 
        error: 'timeout' 
      });
      
      this.log(`请求超时: ${endpoint.id}`);
      
      // 记录结果
      this.preloadResults[endpoint.id] = { 
        success: false, 
        error: { message: 'Request timeout' } 
      };
      
      // 如果是关键数据，执行错误回调
      if (endpoint.isCritical && endpoint.onError) {
        endpoint.onError({ message: 'Request timeout' });
      }
      
      // 继续处理下一个
      this.processPreloadQueue();
    }, this.config.timeout);
    
    // 发送请求
    const fetchOptions: RequestInit = {
      method: endpoint.method,
      headers: endpoint.headers ? 
        new Headers(endpoint.headers) : 
        new Headers({ 'Content-Type': 'application/json' }),
      signal: abortController.signal,
      credentials: 'same-origin'
    };
    
    // 如果不是GET请求，添加请求体
    if (endpoint.method !== 'GET' && endpoint.params) {
      fetchOptions.body = JSON.stringify(endpoint.params);
    }
    
    // 构建URL
    const url = new URL(endpoint.url, window.location.origin);
    
    // 如果是GET请求，将参数添加到URL
    if (endpoint.method === 'GET' && endpoint.params) {
      Object.entries(endpoint.params).forEach(([key, value]) => {
        url.searchParams.append(key, String(value));
      });
    }
    
    return fetch(url.toString(), fetchOptions)
      .then(response => {
        clearTimeout(timeoutId);
        
        if (!response.ok) {
          throw new Error(`Request failed with status ${response.status}`);
        }
        
        return response.json();
      })
      .then(data => {
        performanceMonitor.markEnd(performanceId, { success: true });
        
        // 缓存响应
        apiCache.set(`preload:${endpoint.id}`, data, endpoint.cacheExpiry);
        
        // 记录结果
        this.preloadResults[endpoint.id] = { 
          success: true, 
          data 
        };
        
        // 执行成功回调
        if (endpoint.onSuccess) {
          endpoint.onSuccess(data);
        }
        
        // 继续处理下一个
        return this.processPreloadQueue();
      })
      .catch(error => {
        clearTimeout(timeoutId);
        
        // 如果请求被中止，跳过重试
        if (error.name === 'AbortError') {
          return this.processPreloadQueue();
        }
        
        performanceMonitor.markEnd(performanceId, { 
          success: false, 
          error: error.message 
        });
        
        this.log(`请求失败: ${endpoint.id}`, error);
        
        // 记录结果
        this.preloadResults[endpoint.id] = { 
          success: false, 
          error 
        };
        
        // 尝试重试
        return this.retryEndpoint(endpoint, 0)
          .then(() => this.processPreloadQueue())
          .catch(() => {
            // 如果是关键数据，执行错误回调
            if (endpoint.isCritical && endpoint.onError) {
              endpoint.onError(error);
            }
            
            // 继续处理下一个
            return this.processPreloadQueue();
          });
      });
  }

  /**
   * 重试预加载端点
   */
  private retryEndpoint(endpoint: PreloadEndpoint, retryCount: number): Promise<any> {
    if (retryCount >= this.config.retry.maxRetries) {
      return Promise.reject(`已达到最大重试次数: ${endpoint.id}`);
    }
    
    const delay = this.config.retry.delay * Math.pow(this.config.retry.backoffFactor, retryCount);
    
    this.log(`重试 ${retryCount + 1}/${this.config.retry.maxRetries}: ${endpoint.id}，延迟 ${delay}ms`);
    
    return new Promise(resolve => setTimeout(resolve, delay))
      .then(() => {
        // 重新添加到队列头部
        this.preloadQueue.unshift(endpoint);
        
        // 继续处理队列，它会处理刚添加的端点
        return this.processPreloadQueue();
      });
  }

  /**
   * 处理指定的端点集合
   */
  private processEndpoints(endpoints: PreloadEndpoint[]): Promise<void> {
    // 保存当前队列
    const originalQueue = [...this.preloadQueue];
    
    // 设置新队列
    this.preloadQueue = endpoints;
    
    // 处理新队列
    return this.processPreloadQueue()
      .then(() => {
        // 恢复原队列
        this.preloadQueue = originalQueue;
      });
  }

  /**
   * 取消所有预加载请求
   */
  public cancelAll(): void {
    Object.values(this.abortControllers).forEach(controller => {
      controller.abort();
    });
    
    this.abortControllers = {};
    this.preloadQueue = [];
    this.preloadInProgress = false;
    
    this.log('已取消所有预加载请求');
  }

  /**
   * 获取预加载结果
   */
  public getResults(): Record<string, { success: boolean; data?: any; error?: any }> {
    return this.preloadResults;
  }

  /**
   * 获取特定端点的预加载结果
   */
  public getResult(endpointId: string): { success: boolean; data?: any; error?: any } | null {
    return this.preloadResults[endpointId] || null;
  }

  /**
   * 获取预加载状态
   */
  public getStatus(): {
    enabled: boolean;
    inProgress: boolean;
    completed: boolean;
    queueLength: number;
    resultsCount: number;
  } {
    return {
      enabled: this.config.enabled,
      inProgress: this.preloadInProgress,
      completed: this.preloadCompleted,
      queueLength: this.preloadQueue.length,
      resultsCount: Object.keys(this.preloadResults).length
    };
  }

  /**
   * 日志记录
   */
  private log(message: string, data?: any): void {
    if (this.config.debug) {
      console.log(`%c[PreloadService] ${message}`, 'color: #6200ea', data || '');
    }
  }
}

// 创建并导出单例实例
export const preloadService = PreloadService.getInstance();

// 预加载常用数据的辅助函数
export const preloadCommonData = (): Promise<void> => {
  // 添加常用的预加载配置
  
  // 1. 首页产品列表
  preloadService.addEndpoint({
    id: 'products:featured',
    url: '/api/products',
    method: 'GET',
    params: { featured: true, limit: 8 },
    cacheExpiry: CacheExpiry.PRODUCT,
    priority: 1,
    isCritical: true
  });
  
  // 2. 产品类别
  preloadService.addEndpoint({
    id: 'categories:all',
    url: '/api/categories',
    method: 'GET',
    cacheExpiry: CacheExpiry.CATEGORY,
    priority: 2,
    isCritical: true
  });
  
  // 3. 用户信息（如果已登录）
  preloadService.addEndpoint({
    id: 'user:profile',
    url: '/api/user',
    method: 'GET',
    cacheExpiry: CacheExpiry.SESSION,
    priority: 3,
    isCritical: false,
    condition: () => !!localStorage.getItem('token') // 只有当有token时才加载
  });
  
  // 4. 购物车
  preloadService.addEndpoint({
    id: 'cart:current',
    url: '/api/cart',
    method: 'GET',
    cacheExpiry: CacheExpiry.CART,
    priority: 2,
    isCritical: false
  });
  
  // 开始预加载
  return preloadService.preloadAll();
};

// 导出预加载特定路由数据的函数
export const preloadRouteData = (route: string): Promise<void> => {
  switch (route) {
    case 'product-list':
      // 预加载产品列表页需要的数据
      preloadService.addEndpoint({
        id: 'products:all',
        url: '/api/products',
        method: 'GET',
        params: { limit: 20 },
        cacheExpiry: CacheExpiry.PRODUCT,
        priority: 1,
        isCritical: true
      });
      
      preloadService.addEndpoint({
        id: 'filters:all',
        url: '/api/filters',
        method: 'GET',
        cacheExpiry: CacheExpiry.PRODUCT,
        priority: 2,
        isCritical: false
      });
      
      return preloadService.preloadByType('products');
      
    case 'checkout':
      // 预加载结账页需要的数据
      preloadService.addEndpoint({
        id: 'cart:current',
        url: '/api/cart',
        method: 'GET',
        cacheExpiry: CacheExpiry.CART,
        priority: 1,
        isCritical: true
      });
      
      preloadService.addEndpoint({
        id: 'shipping:methods',
        url: '/api/shipping/methods',
        method: 'GET',
        cacheExpiry: CacheExpiry.PRODUCT,
        priority: 2,
        isCritical: true
      });
      
      preloadService.addEndpoint({
        id: 'payment:methods',
        url: '/api/payment/methods',
        method: 'GET',
        cacheExpiry: CacheExpiry.PRODUCT,
        priority: 2,
        isCritical: true
      });
      
      return preloadService.preloadByType('checkout');
      
    default:
      return Promise.resolve();
  }
};

export default preloadService; 
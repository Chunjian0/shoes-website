/**
 * ServiceIntegration.ts
 * 
 * 此文件演示如何集成所有优化服务到应用程序中，
 * 包括API缓存、批量请求、性能监控、图片优化和预加载服务。
 */

import { apiCache, CacheExpiry } from './CacheService';
import { batchApi } from './BatchRequestService';
import { performanceMonitor, MarkType } from './PerformanceMonitor';
import { imageService } from './ImageService';
import { preloadService, preloadCommonData, preloadRouteData } from './PreloadService';

// ============================================================================
// API请求包装器 - 集成所有优化服务
// ============================================================================

interface RequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
  params?: Record<string, any>;
  headers?: Record<string, string>;
  cache?: boolean;
  cacheExpiry?: number;
  priority?: number;
  batch?: boolean;
  batchId?: string;
  retry?: boolean;
  retryCount?: number;
  timeout?: number;
  abortSignal?: AbortSignal;
  monitorPerformance?: boolean;
}

const defaultRequestOptions: RequestOptions = {
  method: 'GET',
  cache: true,
  cacheExpiry: CacheExpiry.DEFAULT,
  priority: 5,
  batch: true,
  retry: true,
  retryCount: 3,
  timeout: 10000,
  monitorPerformance: true
};

/**
 * 优化的API请求函数，集成了所有优化服务
 */
export const api = {
  /**
   * 发送优化的API请求
   * @param url API端点URL
   * @param options 请求选项
   */
  request: async <T = any>(url: string, options: RequestOptions = {}): Promise<T> => {
    // 合并默认选项
    const opts = { ...defaultRequestOptions, ...options };
    const { method, params, headers, cache, cacheExpiry, priority, batch, batchId, retry, retryCount, timeout, abortSignal, monitorPerformance } = opts;
    
    // 生成缓存键
    const cacheKey = generateCacheKey(url, method!, params);
    
    // 检查缓存
    if (cache && method === 'GET') {
      const cachedData = apiCache.get<T>(cacheKey);
      if (cachedData) {
        console.log(`Using cached data for ${url}`);
        return cachedData;
      }
    }
    
    // 性能监控标记
    let perfMarkId;
    if (monitorPerformance) {
      perfMarkId = performanceMonitor.markStart(MarkType.API_REQUEST, url, {
        method,
        params: params ? JSON.stringify(params).substr(0, 100) : undefined
      });
    }
    
    try {
      let response: T;
      
      // 如果是GET请求且支持批处理，使用批处理API
      if (batch && method === 'GET') {
        response = await batchApi.sendRequest<T>({
          path: url,
          method,
          params,
          headers,
          priority,
          batchId
        });
      } else {
        // 否则使用普通请求
        const controller = new AbortController();
        const signal = abortSignal || controller.signal;
        
        // 设置超时
        let timeoutId: NodeJS.Timeout | null = null;
        if (timeout) {
          timeoutId = setTimeout(() => controller.abort(), timeout);
        }
        
        try {
          // 构建URL（对GET请求添加查询参数）
          const fullUrl = new URL(url, window.location.origin);
          if (method === 'GET' && params) {
            Object.entries(params).forEach(([key, value]) => {
              fullUrl.searchParams.append(key, String(value));
            });
          }
          
          // 构建请求选项
          const fetchOptions: RequestInit = {
            method,
            headers: headers ? new Headers(headers) : new Headers({ 'Content-Type': 'application/json' }),
            signal,
            credentials: 'same-origin'
          };
          
          // 对非GET请求添加请求体
          if (method !== 'GET' && params) {
            fetchOptions.body = JSON.stringify(params);
          }
          
          // 发送请求
          const fetchResponse = await fetch(fullUrl.toString(), fetchOptions);
          
          if (!fetchResponse.ok) {
            throw new Error(`Request failed with status ${fetchResponse.status}`);
          }
          
          response = await fetchResponse.json();
        } finally {
          // 清除超时
          if (timeoutId) {
            clearTimeout(timeoutId);
          }
        }
      }
      
      // 缓存响应（仅缓存GET请求）
      if (cache && method === 'GET') {
        apiCache.set(cacheKey, response, cacheExpiry!);
      }
      
      // 完成性能监控
      if (monitorPerformance && perfMarkId) {
        performanceMonitor.markEnd(perfMarkId, { success: true });
      }
      
      return response;
    } catch (error) {
      // 完成性能监控（失败）
      if (monitorPerformance && perfMarkId) {
        performanceMonitor.markEnd(perfMarkId, { 
          success: false, 
          error: error instanceof Error ? error.message : String(error) 
        });
      }
      
      // 如果启用了重试，且重试次数未达上限，则重试请求
      if (retry && retryCount! > 0) {
        console.log(`Retrying request to ${url}, ${retryCount} attempts left`);
        return api.request<T>(url, { ...opts, retryCount: retryCount! - 1 });
      }
      
      throw error;
    }
  },
  
  /**
   * GET请求
   */
  get: <T = any>(url: string, params?: Record<string, any>, options?: RequestOptions): Promise<T> => {
    return api.request<T>(url, { ...options, method: 'GET', params });
  },
  
  /**
   * POST请求
   */
  post: <T = any>(url: string, data?: Record<string, any>, options?: RequestOptions): Promise<T> => {
    return api.request<T>(url, { ...options, method: 'POST', params: data, cache: false });
  },
  
  /**
   * PUT请求
   */
  put: <T = any>(url: string, data?: Record<string, any>, options?: RequestOptions): Promise<T> => {
    return api.request<T>(url, { ...options, method: 'PUT', params: data, cache: false });
  },
  
  /**
   * DELETE请求
   */
  delete: <T = any>(url: string, params?: Record<string, any>, options?: RequestOptions): Promise<T> => {
    return api.request<T>(url, { ...options, method: 'DELETE', params, cache: false });
  },
  
  /**
   * PATCH请求
   */
  patch: <T = any>(url: string, data?: Record<string, any>, options?: RequestOptions): Promise<T> => {
    return api.request<T>(url, { ...options, method: 'PATCH', params: data, cache: false });
  },
  
  /**
   * 使用批量请求发送多个请求
   */
  batchRequests: async <T = any[]>(requests: Array<{
    url: string;
    method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
    params?: Record<string, any>;
    headers?: Record<string, string>;
    priority?: number;
  }>): Promise<T> => {
    const batchId = `batch-${Date.now()}`;
    const batchItems = requests.map(req => ({
      path: req.url,
      method: req.method,
      params: req.params,
      headers: req.headers,
      priority: req.priority || 5
    }));
    
    // 性能监控
    const perfMarkId = performanceMonitor.markStart(MarkType.API_REQUEST, 'batch-request', {
      count: requests.length,
      batchId
    });
    
    try {
      const response = await batchApi.batch(batchItems);
      performanceMonitor.markEnd(perfMarkId, { success: true });
      return response as unknown as T;
    } catch (error) {
      performanceMonitor.markEnd(perfMarkId, { 
        success: false, 
        error: error instanceof Error ? error.message : String(error) 
      });
      throw error;
    }
  },
  
  /**
   * 清除特定URL的缓存
   */
  clearCache: (url: string, method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH' = 'GET', params?: Record<string, any>): void => {
    const cacheKey = generateCacheKey(url, method, params);
    apiCache.remove(cacheKey);
  },
  
  /**
   * 清除所有缓存
   */
  clearAllCache: (): void => {
    apiCache.clear();
  },
  
  /**
   * 预热缓存（预加载指定URL的数据到缓存中）
   */
  preloadData: (urls: Array<{
    url: string;
    params?: Record<string, any>;
    cacheExpiry?: number;
    priority?: number;
    isCritical?: boolean;
  }>): Promise<void> => {
    urls.forEach(item => {
      preloadService.addEndpoint({
        id: `preload:${item.url}`,
        url: item.url,
        method: 'GET',
        params: item.params,
        cacheExpiry: item.cacheExpiry || CacheExpiry.DEFAULT,
        priority: item.priority || 5,
        isCritical: item.isCritical || false
      });
    });
    
    return preloadService.preloadAll();
  }
};

/**
 * 生成缓存键
 */
function generateCacheKey(url: string, method: string, params?: Record<string, any>): string {
  if (!params) {
    return `${method}:${url}`;
  }
  
  // 对参数进行排序，以确保相同参数但顺序不同的请求使用相同的缓存键
  const sortedParams = Object.keys(params)
    .sort()
    .reduce<Record<string, any>>((acc, key) => {
      acc[key] = params[key];
      return acc;
    }, {});
  
  return `${method}:${url}:${JSON.stringify(sortedParams)}`;
}

// ============================================================================
// 图片优化集成
// ============================================================================

/**
 * 优化的图片组件属性
 */
export interface OptimizedImageProps {
  src: string;
  alt: string;
  width?: number;
  height?: number;
  lazy?: boolean;
  placeholder?: 'blur' | 'color' | 'none';
  quality?: number;
  className?: string;
  style?: React.CSSProperties;
  onLoad?: () => void;
  onError?: () => void;
}

/**
 * 获取优化的图片URL
 */
export const getOptimizedImageUrl = (src: string, options: {
  width?: number;
  height?: number;
  quality?: number;
  format?: 'auto' | 'webp' | 'avif' | 'jpeg' | 'png';
} = {}): string => {
  return imageService.getImageUrl(src, options);
};

/**
 * 获取响应式图片srcSet
 */
export const getResponsiveSrcSet = (src: string, options: {
  widths?: number[];
  quality?: number;
  format?: 'auto' | 'webp' | 'avif' | 'jpeg' | 'png';
} = {}): string => {
  return imageService.getSrcSet(src, options);
};

// ============================================================================
// 应用初始化集成
// ============================================================================

/**
 * 初始化所有优化服务
 */
export const initOptimizationServices = (config?: {
  enableCache?: boolean;
  enableBatch?: boolean;
  enablePreload?: boolean;
  enablePerformanceMonitoring?: boolean;
}): void => {
  const {
    enableCache = true,
    enableBatch = true,
    enablePreload = true,
    enablePerformanceMonitoring = true
  } = config || {};
  
  // 配置缓存服务
  if (enableCache) {
    apiCache.setConfig({
      enabled: true,
      storage: 'localStorage',
      defaultExpiry: CacheExpiry.DEFAULT
    });
  }
  
  // 配置批量请求服务
  if (enableBatch) {
    batchApi.setConfig({
      enabled: true,
      maxBatchSize: 10,
      batchDelay: 50,
      endpoint: '/api/batch'
    });
  }
  
  // 配置性能监控
  if (enablePerformanceMonitoring) {
    performanceMonitor.setConfig({
      enabled: true,
      slowRequestThreshold: 1000,
      sampleRate: 1.0,
      reportToServer: true,
      reportEndpoint: '/api/performance/report'
    });
  }
  
  // 配置图片服务
  imageService.updateConfig({
    baseUrl: window.location.origin,
    cdnUrl: process.env.REACT_APP_CDN_URL,
    breakpoints: {
      sm: 640,
      md: 768,
      lg: 1024,
      xl: 1280,
      '2xl': 1536
    },
    defaultQuality: 80,
    supportWebP: true,
    supportAvif: 'auto',
    lazyLoadByDefault: true
  });
  
  // 预加载共享数据
  if (enablePreload) {
    preloadCommonData().catch(error => {
      console.error('Failed to preload common data:', error);
    });
  }
};

/**
 * 应用路由变更时调用，预加载当前路由所需数据
 */
export const handleRouteChange = (route: string): void => {
  preloadRouteData(route).catch(error => {
    console.error(`Failed to preload data for route ${route}:`, error);
  });
};

/**
 * 示例：如何在应用入口文件中初始化所有服务
 */
/*
// 在 App.tsx 或 index.tsx 中:

import { initOptimizationServices, handleRouteChange } from './services/ServiceIntegration';

// 初始化优化服务
initOptimizationServices();

// 监听路由变化
const router = createRouter();
router.events.on('routeChangeStart', (url) => {
  // 根据URL确定路由名称
  const routeName = getRouteNameFromUrl(url);
  handleRouteChange(routeName);
});

function getRouteNameFromUrl(url: string): string {
  // 根据URL路径确定路由名称
  if (url.includes('/products')) return 'product-list';
  if (url.includes('/checkout')) return 'checkout';
  return 'home';
}
*/

// ============================================================================
// 使用示例
// ============================================================================

/**
 * 示例：如何在组件中使用优化的API调用
 */
/*
import { api } from './services/ServiceIntegration';

// 在React组件中:
const fetchProducts = async () => {
  try {
    // 使用缓存的GET请求
    const products = await api.get('/api/products', { limit: 10, page: 1 });
    setProducts(products);
    
    // 或者使用批量请求
    const [products, categories, featured] = await api.batchRequests([
      { url: '/api/products', method: 'GET', params: { limit: 10 } },
      { url: '/api/categories', method: 'GET' },
      { url: '/api/products/featured', method: 'GET' }
    ]);
    
    // 更新商品（不使用缓存）
    await api.post('/api/products', { name: 'New Product', price: 99.99 });
    
    // 在修改后清除特定缓存
    api.clearCache('/api/products');
    
    // 预加载相关数据
    api.preloadData([
      { url: '/api/products/related', params: { id: 123 }, priority: 2 }
    ]);
  } catch (error) {
    console.error('Failed to fetch products:', error);
  }
};
*/

/**
 * 示例：如何在组件中使用优化的图片
 */
/*
import { getOptimizedImageUrl, getResponsiveSrcSet } from './services/ServiceIntegration';

// 在React组件中:
return (
  <div>
    <img 
      src={getOptimizedImageUrl(product.image, { width: 300, height: 300, quality: 85 })}
      srcSet={getResponsiveSrcSet(product.image, { widths: [300, 600, 900] })}
      sizes="(max-width: 768px) 100vw, 300px"
      alt={product.name}
      loading="lazy"
      width={300}
      height={300}
    />
  </div>
);
*/

export default {
  api,
  getOptimizedImageUrl,
  getResponsiveSrcSet,
  initOptimizationServices,
  handleRouteChange
}; 
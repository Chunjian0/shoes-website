import axios, { AxiosRequestConfig, AxiosResponse, AxiosError, InternalAxiosRequestConfig } from 'axios';
import { toast } from 'react-toastify';
import { recordApiPerformance, recordError, getPerformanceConfig } from '../utils/performance';
import { logger } from '../utils/logger';
import { store } from '../store';
import { logout } from '../store/slices/authSlice';

// --- Variable for API Activity Tracking ---
export let lastApiCallTimestamp = Date.now();
// --- End Variable ---

// API基础URL，优先从环境变量获取，否则回退到默认值（例如本地开发）
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'; // Default fallback for development

// 并发请求管理
const MAX_CONCURRENT_REQUESTS = 5; // 增加最大并发请求数到5个
let activeRequests = 0;
const requestQueue: Array<() => Promise<any>> = [];

// 请求缓存
const requestCache = new Map<string, { data: any, timestamp: number, ttl: number }>();
const DEFAULT_CACHE_TTL = 60 * 1000; // 默认缓存1分钟

// 最近发送的请求时间跟踪 - 取消请求频率限制
let lastRequestTime = 0;
const MIN_REQUEST_INTERVAL = 0; // 取消最小请求间隔限制

// 定义扩展的请求配置接口，包含meta字段
interface ExtendedRequestConfig extends InternalAxiosRequestConfig {
  meta?: {
    requestStartTime?: number;
  };
}

// 处理请求队列的函数
const processQueue = async () => {
  // 如果队列为空或者活跃请求数达到上限，则返回
  if (requestQueue.length === 0 || activeRequests >= MAX_CONCURRENT_REQUESTS) {
    return;
  }
  
  // 从队列中取出下一个请求
  const nextRequest = requestQueue.shift();
  if (!nextRequest) return;
  
  // 增加活跃请求计数并执行请求
  activeRequests++;
  lastRequestTime = Date.now();
  
  try {
    await nextRequest();
  } catch (error) {
    console.error('队列请求执行失败', error);
  } finally {
    activeRequests--;
    
    // 立即处理队列中的下一个请求
    processQueue();
  }
};

// Create an axios instance with default config
export const api = axios.create({
  baseURL: `${apiBaseUrl}/api`,
  timeout: 30000,   // 增加超时时间到30秒
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // 确保跨域请求可以携带cookies
});

console.log('[api.ts] Axios instance created.');

// 调试级别: 0=无调试, 1=仅错误, 2=详细
let apiDebugLevel = process.env.NODE_ENV === 'development' ? 1 : 0;

// 更详细的调试日志函数 - 减少日志输出
const logDebug = (message: string, data?: any) => {
  // 根据不同的日志级别控制输出
  if (apiDebugLevel === 0) return;
  
  // 错误日志始终输出
  if (message.includes('错误') || message.includes('失败')) {
    console.log(`[API ${new Date().toISOString()}] ${message}`, data ? data : '');
    return;
  }
  
  // 详细日志仅在级别2输出
  if (apiDebugLevel >= 2) {
  console.log(`[API ${new Date().toISOString()}] ${message}`, data ? data : '');
  }
};

// 控制日志级别的函数
export const setApiDebugLevel = (level: number) => {
  apiDebugLevel = level;
  logDebug(`API调试级别设置为: ${level}`, null);
};

// 为URL和参数生成缓存键
const generateCacheKey = (config: AxiosRequestConfig): string => {
  return `${config.method}:${config.url}:${JSON.stringify(config.params)}:${JSON.stringify(config.data)}`;
};

// 检查缓存是否有效
const isCacheValid = (cacheEntry: { timestamp: number, ttl: number }): boolean => {
  return Date.now() - cacheEntry.timestamp < cacheEntry.ttl;
};

// Request interceptor for adding auth token
api.interceptors.request.use(
  async (config: ExtendedRequestConfig) => {
    // 记录请求开始时间
    logDebug(`Starting request for: ${config.url}`);
    config.meta = { requestStartTime: performance.now() };

    // Update last API call timestamp
    lastApiCallTimestamp = Date.now();

    // 直接修改config对象而不创建副本
    // 添加认证令牌 (from localStorage)
    const token = localStorage.getItem('token');
    if (token) {
      // 在axios请求拦截器中，直接操作headers而不替换整个对象
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    return config;
  },
  (error) => {
    logDebug('Request interceptor error:', error);
    return Promise.reject(error);
  }
);

// Response interceptor for handling errors
api.interceptors.response.use(
  (response) => {
    // Update last API call timestamp on successful response
    lastApiCallTimestamp = Date.now();

    // 记录性能
    const startTime = (response.config as ExtendedRequestConfig).meta?.requestStartTime;
    const url = response.config.url || 'unknown';
    console.log(`[API Interceptor Response Success] Received response for: ${url}. Start time: ${startTime}`);
    if (startTime) {
      recordApiPerformance(url, startTime, true);
    }
    
    if (apiDebugLevel >= 2) {
    logDebug(`收到响应: ${response.config.method?.toUpperCase()} ${response.config.url}`, {
      status: response.status,
      data: response.data
    });
    }
    return response;
  },
  async (error: AxiosError) => {
    const originalRequest = error.config;
    
    // Ensure originalRequest exists before proceeding
    if (!originalRequest) {
      console.error('No original request in error:', error);
      return Promise.reject(error);
    }
    
    // 记录性能 (失败)
    const startTime = (originalRequest as ExtendedRequestConfig)?.meta?.requestStartTime;
    const url = originalRequest.url || 'unknown_error_url';
    console.log(`[API Interceptor Response Error] Failed request for: ${url}. Start time: ${startTime}`);
    if (startTime) {
      recordApiPerformance(url, startTime, false);
    }
    
    // 处理认证错误（401状态码）
    if (error.response?.status === 401) {
      console.warn('API returned 401 Unauthorized. Logging out.');
      // Dispatch logout action directly from Redux store
      // Ensure this doesn't cause infinite loops if logout itself fails with 401
      // Check if the original request was NOT the logout request itself
      if (originalRequest.url !== '/logout') { // Check against your actual logout endpoint
          // store.dispatch(logout()); // === 注释掉自动登出 ===
          toast.error('Your session has expired. Please log in again.', { toastId: 'session-expired' });
          // Reject the original promise to stop further processing in the component
          return Promise.reject(new Error('Session expired')); 
      } else {
          // If logout itself failed with 401, just reject
           return Promise.reject(error);
      }
    }
    
    // 处理授权错误（403状态码）
    if (error.response?.status === 403) {
      console.error('Authorization error:', error.response?.data || error.message);
      
      // 提取错误消息
      let errorMessage = '授权失败';
      if (error.response?.data) {
        const data = error.response.data as any;
        if (typeof data === 'string') {
          errorMessage = data;
        } else if (data.message) {
          errorMessage = data.message;
        } else if (data.error) {
          errorMessage = data.error;
        }
      }
      
      // 提供更详细的错误信息，便于调试
      console.error('详细错误信息:', {
        status: error.response?.status,
        statusText: error.response?.statusText,
        url: originalRequest.url,
        method: originalRequest.method,
        headers: originalRequest.headers,
        data: originalRequest.data,
        errorData: error.response?.data
      });
      
      // 显示友好的错误消息
      toast.error(`授权错误: ${errorMessage}`);
    }
    
    // 处理请求失败
    const errorMessage = getErrorMessage(error);
    console.error('API请求失败:', errorMessage, error);
    
    return Promise.reject(error);
  }
);

/**
 * 从错误对象中获取错误消息
 */
const getErrorMessage = (error: AxiosError): string => {
  if (error.response?.data) {
    const data = error.response.data as any;
    if (typeof data === 'string') {
      return data;
    } else if (data.message) {
      return data.message;
    } else if (data.error) {
      return data.error;
    }
  }
  
  if (error.message) {
    return error.message;
  }
  
  return '发生未知错误';
};

// 确保每个API请求都包含必要的认证信息
const enhanceRequestConfig = (config: AxiosRequestConfig): AxiosRequestConfig => {
  const enhancedConfig = { ...config };
  
  // 确保headers对象存在
  if (!enhancedConfig.headers) {
    enhancedConfig.headers = {};
  }
  
  // 添加认证标识和其他必要头信息
  enhancedConfig.headers['X-Requested-With'] = 'XMLHttpRequest';
  enhancedConfig.headers['Accept'] = 'application/json';
  
  // 确保发送cookies (对于跨域请求很重要)
  enhancedConfig.withCredentials = true;
  
  // 记录调试信息
  if (apiDebugLevel >= 2) {
    logDebug('请求配置增强完成', { 
      originalUrl: config.url,
      enhancedConfig: { 
        url: enhancedConfig.url,
        method: enhancedConfig.method,
        headers: enhancedConfig.headers,
        withCredentials: enhancedConfig.withCredentials
      }
    });
  }
  
  return enhancedConfig;
};

// 通用请求方法 - 增加重试逻辑、缓存和队列
const request = async <T>(
  config: AxiosRequestConfig, 
  retries = 2, 
  delay = 1000,
  useCache = false,
  cacheTTL = DEFAULT_CACHE_TTL
): Promise<T> => {
  // 获取性能配置
  const perfConfig = getPerformanceConfig();
  
  // 如果全局禁用了缓存，则覆盖单个请求的缓存设置
  if (!perfConfig.cacheApiResponses) {
    useCache = false;
  }
  
  // 应用全局缓存TTL
  cacheTTL = perfConfig.cacheMaxAge || cacheTTL;
  
  // 生成缓存键
  const cacheKey = useCache ? generateCacheKey(config) : '';
  
  // 如果启用缓存并且存在有效缓存，直接返回缓存数据
  if (useCache && cacheKey) {
    const cached = requestCache.get(cacheKey);
    if (cached && isCacheValid(cached)) {
      logDebug(`使用缓存数据: ${config.method} ${config.url}`, { cacheKey });
      return cached.data;
    }
  }
  
  // 创建实际的请求函数
  const executeRequest = async (): Promise<T> => {
    try {
      // 增强请求配置，确保包含认证信息
      const enhancedConfig = enhanceRequestConfig(config);
      
      // 确保params参数是一个对象
      if (enhancedConfig.params && typeof enhancedConfig.params !== 'object') {
        enhancedConfig.params = {};
        console.error('Invalid params: must be an object', enhancedConfig.params);
      }
    
      // 确保data参数是一个对象（或FormData等有效类型）
      if (enhancedConfig.data && typeof enhancedConfig.data !== 'object') {
        enhancedConfig.data = {};
        console.error('Invalid data: must be an object', enhancedConfig.data);
      }
      
      // 记录cookies信息，帮助调试
      if (apiDebugLevel >= 2) {
        logDebug(`发送请求前的cookies:`, document.cookie);
      }
    
      const response: AxiosResponse<T> = await api(enhancedConfig);
      
      // 记录响应后的cookies信息
      if (apiDebugLevel >= 2) {
        logDebug(`响应后的cookies:`, document.cookie);
      }
      
      // 如果启用缓存，保存响应数据
      if (useCache && cacheKey) {
        requestCache.set(cacheKey, {
          data: response.data,
          timestamp: Date.now(),
          ttl: cacheTTL
        });
      }
      
      return response.data;
    } catch (error: any) {
      // 记录错误信息
      logDebug('请求失败', error);
      
      // 处理429错误（请求过多）- 使用指数退避策略
      if (axios.isAxiosError(error) && error.response?.status === 429) {
        logDebug('收到429错误(请求过多)，使用指数退避策略重试', {
          url: config.url,
          method: config.method,
          retries,
          delay
        });
        
        if (retries > 0) {
          // 计算指数退避延迟时间，最少等待2秒
          const backoffDelay = Math.max(2000, delay * 1.5);
          
          // 在错误提示中添加更清晰的消息
          console.warn(`API限流: 请求过多(429)，将在${Math.round(backoffDelay/1000)}秒后重试: ${config.method} ${config.url}`);
          
          // 等待更长时间再重试
          await new Promise(resolve => setTimeout(resolve, backoffDelay));
          
          // 递增延迟时间并减少重试次数
          return request(config, retries - 1, backoffDelay, useCache, cacheTTL);
        }
      }
      
      // 如果是网络错误或超时错误，并且还有重试次数，则进行重试
      if ((axios.isAxiosError(error) && 
          (error.code === 'ECONNABORTED' || !error.response || error.message.includes('timeout'))) && 
          retries > 0) {
        
        logDebug(`请求超时或网络错误，${delay}ms后进行重试 (剩余重试次数: ${retries})`, {
          url: config.url,
          method: config.method
        });
        
        // 等待指定时间后重试
        await new Promise(resolve => setTimeout(resolve, delay));
        
        // 递增延迟时间并减少重试次数
        return request(config, retries - 1, delay * 1.5, useCache, cacheTTL);
      }
        
      // 如果是403错误，可能是权限或会话问题
      if (axios.isAxiosError(error) && error.response?.status === 403) {
        logDebug('403 Forbidden错误，可能是权限或会话问题', {
          url: config.url,
          method: config.method,
          data: error.response.data
        });
        
        // 如果有重试次数，尝试重新验证会话后再重试
        if (retries > 0) {
          try {
            // 尝试ping一下API检查会话状态
            await apiService.auth.ping();
            
            logDebug('会话有效，重试请求');
            return request(config, retries - 1, delay, useCache, cacheTTL);
          } catch (pingError) {
            logDebug('会话检查失败，无法重试', pingError);
          }
        }
      }
    
      throw error; // 向上抛出错误，让调用方处理错误
    }
  };
  
  // 如果并发请求数小于最大限制，直接执行请求
  if (activeRequests < MAX_CONCURRENT_REQUESTS) {
    activeRequests++;
    lastRequestTime = Date.now(); // 更新最近请求时间
    try {
      return await executeRequest();
    } finally {
      activeRequests--;
      // 立即处理队列中的下一个请求
      processQueue();
    }
  } else {
    // 并发请求数达到上限，将请求添加到队列
    return new Promise<T>((resolve, reject) => {
      requestQueue.push(async () => {
        try {
          const result = await executeRequest();
          resolve(result);
        } catch (error) {
          reject(error);
        }
      });
      
      // 尝试处理队列
      processQueue();
    });
  }
};

// API服务对象
export const apiService = {
  // 身份验证相关
  auth: {
    login: (data: { email: string; password: string; remember?: boolean }) => 
      request<any>({ method: 'post', url: '/customer/login', data }),
    logout: async () => {
      try {
        // 检查是否有token
        const token = localStorage.getItem('token');
        if (!token) {
          logDebug('Logout called but no token found');
          return {
            status: 'success',
            message: 'Logged out successfully (no token)'
          };
        }
        
        // 记录cookies状态
        if (apiDebugLevel >= 2) {
          logDebug('Cookies before logout:', document.cookie);
        }
        
        // 调用登出API
        const response = await request<any>({ 
          method: 'post', 
          url: '/customer/logout',
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        
        // 记录响应
        logDebug('Logout response:', response);
        
        return response;
      } catch (error: any) {
        console.error('Logout API error:', error);
        logDebug('Logout error details:', {
          status: error.response?.status,
          data: error.response?.data,
          message: error.message
        });
        
        // 即使API调用失败，也返回一个成功的响应，以确保前端清理继续进行
        return {
          status: 'success',
          message: 'Logged out successfully (local only)',
        };
      }
    },
    getUser: () => 
      request<any>({ method: 'get', url: '/customer/profile' }),
    getProfile: () => 
      request<any>({ method: 'get', url: '/customer/profile' }),
    register: (data: any) => 
      request<any>({ method: 'post', url: '/customer/register', data }),
    updateProfile: (data: any) => 
      request<any>({ method: 'put', url: '/customer/profile', data }),
    changePassword: (data: any) => 
      request<any>({ method: 'put', url: '/customer/password', data }),
    forgotPassword: (data: { email: string }) => 
      request<any>({ method: 'post', url: '/customer/forgot-password', data }),
    resetPassword: (data: any) => 
      request<any>({ method: 'post', url: '/customer/reset-password', data }),
    // 会话相关方法 - 使用缓存减少请求
    ping: () => 
      request<any>({ method: 'get', url: '/customer/ping' }, 1, 1000, false),
    checkSession: () => 
      request<{ authenticated: boolean }>({ method: 'get', url: '/customer/check-session' }, 1, 1000, false),
    // 验证码相关
    sendVerificationCode: (data: { email: string }) => 
      request<any>({ method: 'post', url: '/customer/send-verification-code', data }),
    verifyCode: (data: { email: string; verification_code: string }) => 
      request<any>({ method: 'post', url: '/customer/verify-email', data }),
  },

  // 购物车相关
  cart: {
    /**
     * 获取购物车数据
     * @param params 请求参数
     */
    get: async (params = {}) => {
      console.log('API Service: 获取购物车，参数:', params);
      
      // 确保参数是一个有效的对象
      const cleanParams = typeof params === 'object' ? params : {};
      
      // 移除所有undefined和null值
      const filteredParams: Record<string, any> = {};
      Object.entries(cleanParams).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          filteredParams[key] = value;
        }
      });
      
      console.log('API Service: 清理后的购物车请求参数:', filteredParams);
      return request<any>({ 
        method: 'get', 
        url: '/cart', 
        params: filteredParams,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        withCredentials: true 
      });
    },
    
    /**
     * 添加商品到购物车
     * @param data 请求数据
     */
    add: (data: { 
      product_id: number; 
      quantity: number; 
      specifications?: Record<string, string> | null; 
      cart_id?: number; 
      cart_type?: string; 
      cart_name?: string;
      template_id?: number;
      size?: string;
      color?: string;
    }) => {
      // 完整记录原始输入数据
      console.log('API Service (cart.add): 原始请求参数:', JSON.stringify(data));
      
      // 确保规格数据格式正确
      const cleanData = { ...data };
      
      // 强制转换product_id为数字类型
      const productId = Number(cleanData.product_id);
      
      // 验证product_id是否有效
      if (isNaN(productId) || productId <= 0) {
        console.error('API Service: 无效的product_id:', data.product_id);
        throw new Error(`无效的商品ID: ${data.product_id}`);
      }
      
      // 更新为验证后的product_id
      cleanData.product_id = productId;
      console.log('API Service (cart.add): product_id转换:', {
        原始值: data.product_id,
        转换后: productId,
        类型: typeof productId
      });
      
      // 如果specifications为空对象，设置为null
      if (cleanData.specifications && Object.keys(cleanData.specifications).length === 0) {
        cleanData.specifications = null;
      }
      
      // 确保color和size参数也被附加到请求的根级别
      // 这样后端就可以直接访问request->color和request->size
      if (cleanData.specifications) {
        // 如果规格中有color和size，将它们提升到根级别
        if (cleanData.specifications.color && !cleanData.color) {
          cleanData.color = cleanData.specifications.color;
        }
        
        if (cleanData.specifications.size && !cleanData.size) {
          cleanData.size = cleanData.specifications.size;
        }
      }
      
      // 记录请求
      console.log('API Service: 添加商品到购物车，最终参数:', {
        product_id: cleanData.product_id,
        quantity: cleanData.quantity,
        specifications: cleanData.specifications,
        template_id: cleanData.template_id,
        size: cleanData.size,
        color: cleanData.color,
      });
      
      // 记录将要发送的实际请求数据
      console.log('API Service (cart.add): 发送前最终JSON数据:', JSON.stringify(cleanData));
      
      return request<any>({ 
        method: 'post', 
        url: '/cart', 
        data: cleanData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        withCredentials: true 
      });
    },
    update: (id: number, data: { quantity: number }) => 
      request<any>({ 
        method: 'put', 
        url: `/cart/${id}`, 
        data,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        withCredentials: true 
      }).catch(error => {
        // 特殊处理购物车项不存在的情况
        if (axios.isAxiosError(error) && 
            (error.response?.status === 500 || error.response?.status === 404)) {
          // 检查是否包含"No query results"错误信息
          const errorData = error.response?.data;
          const errorMsg = typeof errorData === 'string' ? errorData : 
                          (errorData?.message || JSON.stringify(errorData?.error || ''));
          
          if (errorMsg.includes('No query results') || errorMsg.includes('not found')) {
            console.warn(`购物车项 #${id} 不存在或已被删除`, error.response?.data);
            // 抛出更有意义的错误
            throw new Error(`购物车项不存在或已被删除 (ID: ${id})`);
          }
        }
        // 重新抛出原始错误
        throw error;
      }),
    remove: (id: number) => 
      request<any>({ 
        method: 'delete', 
        url: `/cart/${id}`,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        withCredentials: true 
      }).catch(error => {
        // 特殊处理购物车项不存在的情况
        if (axios.isAxiosError(error) && 
            (error.response?.status === 500 || error.response?.status === 404)) {
          // 检查是否包含"No query results"错误信息
          const errorData = error.response?.data;
          const errorMsg = typeof errorData === 'string' ? errorData : 
                          (errorData?.message || JSON.stringify(errorData?.error || ''));
          
          if (errorMsg.includes('No query results') || errorMsg.includes('not found')) {
            console.warn(`购物车项 #${id} 不存在或已被删除 (尝试删除时)`, error.response?.data); // Added context
            // You might want to resolve peacefully here instead of throwing an error,
            // as the item is already gone.
            // For now, let's throw a specific error.
            throw new Error(`购物车项不存在或已被删除 (无法删除 ID: ${id})`); 
          }
        }
        // 重新抛出原始错误
        throw error;
      }),
    clear: () => 
      request<any>({ 
        method: 'delete', 
        url: '/cart'
      }),
    moveItems: (data: { source_cart_id: number; target_cart_id: number; item_ids: number[] }) =>
      request<any>({
        method: 'post',
        url: '/cart/move',
        data
      })
  },

  // Add categories API methods
  categories: {
    getAll: () => 
      request<any[]>({ method: 'get', url: '/product-categories' })
  },

  // Add templates API methods
  templates: {
    getAll: (params: any) =>
      request<any>({ method: 'get', url: '/product-templates', params }),
    getById: (id: number | string) =>
      request<any>({ method: 'get', url: `/product-templates/${id}` }),
  },
  
  // Add products API methods (adjust endpoints as needed)
  products: {
    getAll: (params: any) => 
      request<any>({ method: 'get', url: '/products', params }),
    getById: (id: number | string) => 
      request<any>({ method: 'get', url: `/products/${id}` }),
    search: (query: string, page: number, perPage: number) =>
      request<any>({ method: 'get', url: '/products/search', params: { query, page, per_page: perPage } }),
    getFeatured: (params: any) => 
      request<any>({ method: 'get', url: '/products/featured', params }),
    getNewArrivals: (params: any) => 
      request<any>({ method: 'get', url: '/products/new-arrivals', params }),
    getSale: (params: any) => 
      request<any>({ method: 'get', url: '/products/sale', params }),
    getPopular: (limit: number) => 
      request<any>({ method: 'get', url: '/products/popular', params: { limit } }),
    getRelated: (productId: number, limit: number) => 
      request<any>({ method: 'get', url: `/products/${productId}/related`, params: { limit } }),
    getStock: (productId: number) => 
      request<any>({ method: 'get', url: `/products/${productId}/stock` }),
    getReviews: (productId: number) => 
      request<any>({ method: 'get', url: `/products/${productId}/reviews` }),
    getList: (params: any) => // Added getList for fetchSaleProducts
      request<any>({ method: 'get', url: '/products', params }),
  },

  // Add orders API methods
  orders: {
    getAll: (params: any = {}) => 
      request<any>({ method: 'get', url: '/orders', params }),
    getById: (id: number | string) => 
      request<any>({ method: 'get', url: `/orders/${id}` }),
    create: (data: any) => 
      request<any>({ method: 'post', url: '/orders', data }),
    cancel: (id: number | string) => 
      request<any>({ method: 'post', url: `/orders/${id}/cancel` }) // Assuming POST to cancel
  }
};
import axios from 'axios';

// CSRF状态管理
let csrfToken: string | null = null;
let csrfInitialized = false;
let csrfInitializing = false;
let lastRefresh = 0;

// 元标签名称
const CSRF_META_NAME = 'csrf-token';

// 调试标志
let debugMode = false;

/**
 * 开启或关闭调试模式
 * @param enable 是否启用调试模式
 */
export const enableDebug = (enable: boolean = true) => {
  debugMode = enable;
};

/**
 * 创建调试日志
 * @param message 日志消息
 * @param data 附加数据
 */
const logDebug = (message: string, data?: any) => {
  if (!debugMode) return;
  console.log(`[CSRF] ${message}`, data ? data : '');
};

/**
 * 初始化CSRF保护
 * 获取并存储CSRF令牌，用于后续API请求
 */
export const initCsrf = async (): Promise<string | null> => {
  // 如果已经在初始化过程中，等待完成
  if (csrfInitializing) {
    logDebug('CSRF初始化已在进行中，等待完成...');
    return new Promise((resolve) => {
      const interval = setInterval(() => {
        if (!csrfInitializing) {
          clearInterval(interval);
          resolve(csrfToken);
        }
      }, 100);
    });
  }
  
  // 如果已经初始化且未过期，直接返回
  if (isCsrfValid()) {
    logDebug('CSRF令牌有效，跳过初始化');
    return csrfToken;
  }
  
  // 标记初始化过程开始
  csrfInitializing = true;
  
  try {
    logDebug('开始初始化CSRF保护...');
    
    // 先尝试从meta标签获取
    let token = getTokenFromMeta();
    
    if (token) {
      logDebug('从meta标签获取到CSRF令牌');
      csrfToken = token;
      csrfInitialized = true;
      lastRefresh = Date.now();
      csrfInitializing = false;
      return token;
    }
    
    // 如果meta标签没有令牌，尝试从API获取
    logDebug('从API获取CSRF令牌...');
    const response = await axios.get('/api/csrf-cookie', { 
      withCredentials: true,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-Debug-Info': 'CSRF-Request'
      }
    });
    
    // API调用成功，检查响应头和meta标签
    logDebug('CSRF API调用成功', response);
    
    // 尝试再次从meta标签获取
    token = getTokenFromMeta();
    
    if (token) {
      logDebug('API调用后从meta标签获取到CSRF令牌');
      csrfToken = token;
      csrfInitialized = true;
      lastRefresh = Date.now();
      return token;
    }
    
    // 如果API响应中包含CSRF令牌信息
    if (response.headers['x-csrf-token']) {
      token = response.headers['x-csrf-token'];
      logDebug('从API响应头获取到CSRF令牌');
      csrfToken = token;
      csrfInitialized = true;
      lastRefresh = Date.now();
      return token;
    }
    
    // 如果响应体中包含CSRF令牌信息
    if (response.data && response.data.csrf_token) {
      token = response.data.csrf_token;
      logDebug('从API响应数据获取到CSRF令牌');
      csrfToken = token;
      csrfInitialized = true;
      lastRefresh = Date.now();
      return token;
    }
    
    // 无法获取CSRF令牌，但不抛出错误
    logDebug('无法获取CSRF令牌，认为操作成功但令牌为空');
    csrfInitialized = true;
    lastRefresh = Date.now();
    return null;
  } catch (error) {
    logDebug('CSRF初始化失败', error);
    console.error('Failed to initialize CSRF protection:', error);
    return null;
  } finally {
    csrfInitializing = false;
  }
};

/**
 * 从meta标签获取CSRF令牌
 */
const getTokenFromMeta = (): string | null => {
  const metaTag = document.querySelector(`meta[name="${CSRF_META_NAME}"]`);
  return metaTag ? metaTag.getAttribute('content') : null;
};

/**
 * 检查CSRF令牌是否有效
 */
export const isCsrfValid = (): boolean => {
  // 如果令牌未初始化，则无效
  if (!csrfInitialized || !csrfToken) {
    return false;
  }
  
  // 检查令牌是否过期（2小时刷新一次）
  const EXPIRY_TIME = 2 * 60 * 60 * 1000; // 2小时
  if (Date.now() - lastRefresh > EXPIRY_TIME) {
    logDebug('CSRF令牌已过期');
    return false;
  }
  
  return true;
};

/**
 * 刷新CSRF令牌
 */
export const refreshCsrf = async (): Promise<string | null> => {
  // 重置初始化状态
  csrfInitialized = false;
  csrfToken = null;
  
  // 重新初始化
  return await initCsrf();
};

/**
 * 获取当前CSRF令牌
 */
export const getCsrfToken = async (): Promise<string | null> => {
  // 如果令牌无效，则初始化
  if (!isCsrfValid()) {
    return await initCsrf();
  }
  
  return csrfToken;
};

/**
 * 为请求头添加CSRF令牌
 * @param headers 当前请求头对象
 */
export const addCsrfHeader = async (headers: Record<string, string> = {}): Promise<Record<string, string>> => {
  const token = await getCsrfToken();
  
  if (token) {
    return {
      ...headers,
      'X-CSRF-TOKEN': token
    };
  }
  
  return headers;
};

/**
 * 创建包含CSRF头的Axios配置
 * @param config 原始Axios配置
 */
export const withCsrf = async (config: any = {}): Promise<any> => {
  const token = await getCsrfToken();
  
  if (!token) {
    return config;
  }
  
  return {
    ...config,
    headers: {
      ...config.headers,
      'X-CSRF-TOKEN': token
    }
  };
};

// 初始化
initCsrf().catch(err => console.error('Failed to initialize CSRF service:', err));

// 默认导出
export default {
  initCsrf,
  refreshCsrf,
  getCsrfToken,
  addCsrfHeader,
  withCsrf,
  enableDebug
}; 
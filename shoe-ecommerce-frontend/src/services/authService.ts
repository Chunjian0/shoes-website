import axios from 'axios';
import { toast } from 'react-toastify';
import { apiService } from './api';

// 存储认证令牌和用户信息的键名
const AUTH_TOKEN_KEY = 'auth_token';
const USER_INFO_KEY = 'user_info';
const TOKEN_REFRESH_TIME_KEY = 'token_refresh_time';
const SESSION_VERIFIED_KEY = 'session_verified';

// 认证状态管理
let currentAuthToken: string | null = null;
let currentUser: any = null;
let isInitialized = false;

// 事件处理
type AuthEventType = 'login' | 'logout' | 'sessionExpired' | 'tokenRefreshed';
const listeners: Record<AuthEventType, Function[]> = {
  login: [],
  logout: [],
  sessionExpired: [],
  tokenRefreshed: []
};

/**
 * 订阅认证事件
 * @param event 事件类型
 * @param callback 回调函数
 */
export const onAuthEvent = (event: AuthEventType, callback: Function) => {
  if (!listeners[event]) {
    listeners[event] = [];
  }
  listeners[event].push(callback);
  
  // 返回取消订阅函数
  return () => {
    listeners[event] = listeners[event].filter(cb => cb !== callback);
  };
};

/**
 * 触发认证事件
 * @param event 事件类型
 * @param data 事件数据
 */
const triggerAuthEvent = (event: AuthEventType, data?: any) => {
  if (listeners[event]) {
    listeners[event].forEach(callback => callback(data));
  }
};

/**
 * 初始化认证服务
 * 从本地存储加载认证信息
 */
export const initAuth = async () => {
  if (isInitialized) return;
  
  try {
    // 从localStorage获取令牌和用户信息
    const storedToken = localStorage.getItem(AUTH_TOKEN_KEY);
    const storedUser = localStorage.getItem(USER_INFO_KEY);
    
    if (storedToken) {
      currentAuthToken = storedToken;
      
      if (storedUser) {
        try {
          currentUser = JSON.parse(storedUser);
        } catch (e) {
          console.error('Failed to parse stored user info', e);
          currentUser = null;
        }
      }
      
      // 检查令牌是否需要刷新
      const refreshTime = localStorage.getItem(TOKEN_REFRESH_TIME_KEY);
      if (refreshTime && Date.now() > Number(refreshTime)) {
        await refreshToken();
      }
      
      // 验证会话是否有效
      const sessionVerifiedTime = localStorage.getItem(SESSION_VERIFIED_KEY);
      const HOUR = 60 * 60 * 1000; // 一小时的毫秒数
      
      if (!sessionVerifiedTime || Date.now() - Number(sessionVerifiedTime) > HOUR) {
        // 如果会话未验证或验证时间超过1小时，验证会话
        await verifySession();
      }
    }
  } catch (error) {
    console.error('Auth initialization failed', error);
    // 如果初始化失败，清除可能损坏的认证信息
    await logout(true);
  } finally {
    isInitialized = true;
  }
};

/**
 * 验证当前会话是否有效
 */
export const verifySession = async (): Promise<boolean> => {
  try {
    if (!currentAuthToken) return false;
    
    const response = await apiService.auth.checkSession();
    
    if (response && response.authenticated) {
      // 更新会话验证时间
      localStorage.setItem(SESSION_VERIFIED_KEY, Date.now().toString());
      return true;
    } else {
      // 会话无效，执行登出
      await logout(true);
      triggerAuthEvent('sessionExpired');
      return false;
    }
  } catch (error) {
    console.error('Session verification failed', error);
    // 验证失败，假设会话无效
    await logout(true);
    triggerAuthEvent('sessionExpired');
    return false;
  }
};

/**
 * 使用用户凭据登录
 * @param email 用户邮箱
 * @param password 用户密码
 * @param remember 是否记住登录状态
 */
export const login = async (credentials: { email: string; password: string; remember?: boolean }) => {
  try {
    // 发送登录请求
    const response = await apiService.auth.login(credentials);
    
    // 验证响应格式并提取认证信息
    let token: string | null = null;
    let user: any = null;
    
    // 处理不同的API响应格式
    if (response?.status === 'success' && response?.data?.token) {
      // 标准格式: { status: 'success', data: { token, customer } }
      token = response.data.token;
      user = response.data.customer;
    } else if (response?.success === true && response?.data?.token) {
      // 另一种格式: { success: true, data: { token, customer } }
      token = response.data.token;
      user = response.data.customer;
    } else if (response?.token && (response?.customer || response?.user)) {
      // 兼容格式: { token, customer } 或 { token, user }
      token = response.token;
      user = response.customer || response.user;
    } else {
      throw new Error('Invalid login response format');
    }
    
    // 存储认证信息
    await setAuthInfo(token, user);
    
    // 触发登录事件
    triggerAuthEvent('login', { user });
    
    // 更新令牌刷新时间
    updateTokenRefreshTime(credentials.remember);
    
    return { user, token };
  } catch (error) {
    console.error('Login failed', error);
    throw error;
  }
};

/**
 * 更新令牌刷新时间
 * @param extended 是否使用扩展时间(记住我)
 */
const updateTokenRefreshTime = (extended: boolean = false) => {
  const now = Date.now();
  // 普通会话12小时刷新一次，记住我7天刷新一次
  const refreshOffset = extended ? 7 * 24 * 60 * 60 * 1000 : 12 * 60 * 60 * 1000;
  localStorage.setItem(TOKEN_REFRESH_TIME_KEY, (now + refreshOffset).toString());
};

/**
 * 设置认证信息
 * @param token 认证令牌
 * @param user 用户信息
 */
const setAuthInfo = async (token: string | null, user: any) => {
  if (token) {
    // 存储到内存和localStorage
    currentAuthToken = token;
    currentUser = user;
    
    localStorage.setItem(AUTH_TOKEN_KEY, token);
    localStorage.setItem(USER_INFO_KEY, JSON.stringify(user));
    
    // 设置会话验证时间
    localStorage.setItem(SESSION_VERIFIED_KEY, Date.now().toString());
  } else {
    // 清除认证信息
    currentAuthToken = null;
    currentUser = null;
    
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(USER_INFO_KEY);
    localStorage.removeItem(TOKEN_REFRESH_TIME_KEY);
    localStorage.removeItem(SESSION_VERIFIED_KEY);
  }
};

/**
 * 刷新认证令牌
 */
export const refreshToken = async (): Promise<boolean> => {
  try {
    if (!currentAuthToken) return false;
    
    // 调用刷新令牌API (如果后端支持)
    // 注：需要后端支持此功能，否则可以使用会话检查替代
    const response = await apiService.auth.ping();
    
    if (response && response.success) {
      // 如果支持令牌刷新，使用新令牌
      if (response.data?.token) {
        await setAuthInfo(response.data.token, response.data.customer || currentUser);
        triggerAuthEvent('tokenRefreshed', { token: response.data.token });
      }
      
      // 更新令牌刷新时间
      updateTokenRefreshTime();
      return true;
    } else {
      // 如果服务器表示会话无效，登出用户
      await logout(true);
      triggerAuthEvent('sessionExpired');
      return false;
    }
  } catch (error) {
    console.error('Token refresh failed', error);
    return false;
  }
};

/**
 * 登出用户
 * @param silent 是否静默登出(不显示通知)
 */
export const logout = async (silent: boolean = false): Promise<boolean> => {
  try {
    // 如果存在令牌，尝试调用登出API
    if (currentAuthToken) {
      try {
        await apiService.auth.logout();
      } catch (error) {
        console.error('Logout API call failed', error);
        // 继续本地登出流程，即使API调用失败
      }
    }
    
    // 清除认证信息
    await setAuthInfo(null, null);
    
    // 触发登出事件
    triggerAuthEvent('logout');
    
    // 显示通知
    if (!silent) {
      toast.success('您已成功登出');
    }
    
    return true;
  } catch (error) {
    console.error('Logout process failed', error);
    
    // 即使出错也尝试清除本地认证信息
    await setAuthInfo(null, null);
    
    if (!silent) {
      toast.error('登出过程中发生错误');
    }
    
    return false;
  }
};

/**
 * 获取当前认证令牌
 */
export const getAuthToken = (): string | null => {
  return currentAuthToken;
};

/**
 * 获取当前用户信息
 */
export const getCurrentUser = (): any => {
  return currentUser;
};

/**
 * 检查用户是否已登录
 */
export const isLoggedIn = (): boolean => {
  return !!currentAuthToken;
};

/**
 * 获取当前用户个人资料 (修正为调用 /customer/profile)
 */
export const getUserProfile = async (): Promise<any> => {
  try {
    console.log('[authService.getUserProfile] Attempting API call /customer/profile');
    const response = await apiService.auth.getProfile(); 
    
    console.log('[authService.getUserProfile] Raw API Response:', response);

    // Check if response.data exists (it should contain the profile object directly)
    if (response.data) {
      currentUser = response.data; // Use response.data directly
      localStorage.setItem(USER_INFO_KEY, JSON.stringify(currentUser));
      console.log('[authService.getUserProfile] Updated currentUser:', currentUser);
      return currentUser; // Return the profile object
    } else {
        // Handle case where response.data is unexpectedly empty/null
        console.error('[authService.getUserProfile] Response data is missing');
        throw new Error('Failed to get user profile: Response data missing');
    }

  } catch (error: any) {
    console.error('Failed to get user profile', error);
    // Handle Axios error structure if possible
    const message = error.response?.data?.message || error.message || 'Unknown error';
    throw new Error(`Failed to fetch profile: ${message}`);
  }
};

/**
 * 注册新用户
 * @param userData 用户注册数据
 */
export const register = async (userData: any) => {
  try {
    // 发送注册请求
    const response = await apiService.auth.register(userData);
    
    // 处理不同的API响应格式
    if (response?.status === 'success') {
      // 标准成功响应
      if (response.data?.token && response.data?.customer) {
        // 如果注册同时登录
        await setAuthInfo(response.data.token, response.data.customer);
        triggerAuthEvent('login', { user: response.data.customer });
        updateTokenRefreshTime(false);
      }
      
      return response;
    } else if (response?.token && response?.customer) {
      // 兼容格式：直接返回token和customer
      await setAuthInfo(response.token, response.customer);
      triggerAuthEvent('login', { user: response.customer });
      updateTokenRefreshTime(false);
      
      return {
        status: 'success',
        message: 'Registration successful',
        data: { token: response.token, customer: response.customer }
      };
    } else {
      // 注册成功但需要单独登录
      return {
        status: 'success',
        message: 'Registration successful. Please log in.'
      };
    }
  } catch (error) {
    console.error('Registration failed', error);
    throw error;
  }
};

/**
 * 检查并处理认证错误
 * @param error 原始错误对象
 */
export const handleAuthError = async (error: any): Promise<boolean> => {
  // 检查是否为认证相关错误
  if (!axios.isAxiosError(error)) return false;
  
  const status = error.response?.status;
  
  // 处理特定的错误状态码
  if (status === 401) {
    // 未授权 - 令牌无效或过期
    console.warn('Authentication error: Token invalid or expired');
    await logout(true);
    triggerAuthEvent('sessionExpired');
    toast.error('您的会话已过期，请重新登录');
    return true;
  } else if (status === 403) {
    // 权限不足
    console.warn('Authorization error: Insufficient permissions');
    
    // 验证会话是否有效
    const isSessionValid = await verifySession();
    
    if (!isSessionValid) {
      // 会话无效，已由verifySession处理
      return true;
    }
    
    // 会话有效但权限不足
    toast.error('您没有权限执行此操作');
    return true;
  } else if (status === 419) {
    // CSRF令牌失效
    console.warn('CSRF token mismatch');
    
    // 刷新CSRF令牌
    toast.warning('会话已更新，请重试');
    return true;
  }
  
  // 不是认证相关错误
  return false;
};

// 初始化认证服务
initAuth().catch(err => console.error('Auth service initialization failed', err));

// 默认导出
export default {
  login,
  logout,
  register,
  initAuth,
  verifySession,
  refreshToken,
  getAuthToken,
  getCurrentUser,
  isLoggedIn,
  getUserProfile,
  onAuthEvent,
  handleAuthError
}; 
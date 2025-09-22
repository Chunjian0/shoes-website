import { createContext, useContext, useState, useEffect, ReactNode, useCallback } from 'react';
import { apiService } from '../services/api';
import { toast } from 'react-toastify';
import { clearPerformanceCache } from '../utils/performance';
import { AxiosError } from 'axios';

// 添加环境变量支持
const env = {
  NODE_ENV: 'development'
};

// 替代process.env对象
const processPolyfill = {
  env
};

// 定义全局process变量
if (typeof window !== 'undefined' && typeof (window as any).process === 'undefined') {
  (window as any).process = processPolyfill;
}

interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  phone?: string;
}

// 添加注册数据类型
interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  // 可以根据需要添加其他字段，例如 phone, verification_code 等
}

// 添加更新个人资料数据类型
interface UpdateProfileData {
  name?: string;
  email?: string;
  phone?: string;
  avatar?: string; // 通常 avatar 是文件上传，处理方式可能不同
  // 根据实际 API 需要添加字段
}

interface AuthContextType {
  user: User | null;
  loading: boolean;
  error: string | null;
  isAuthenticated: boolean;
  login: (email: string, password: string, remember?: boolean) => Promise<any>; // 返回值设为 any 以适应不同 API 响应
  register: (userData: RegisterData) => Promise<any>; // 使用 RegisterData 类型，返回值设为 any
  logout: () => Promise<void>;
  updateProfile: (userData: UpdateProfileData) => Promise<any>; // 使用 UpdateProfileData 类型，返回值设为 any
  changePassword: (data: { current_password: string; password: string; password_confirmation: string }) => Promise<any>; // 返回值设为 any
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // 初始化时检查用户是否已登录
  useEffect(() => {
    const checkAuthStatus = async () => {
      const token = localStorage.getItem('token');
      if (!token) {
        setLoading(false);
        return;
      }

      try {
        const userJson = localStorage.getItem('user');
        if (userJson) {
          setUser(JSON.parse(userJson));
        } else {
          // If we have a token but no user info, try to get user profile
          const response = await apiService.auth.getProfile();
          setUser(response.data?.customer);
          localStorage.setItem('user', JSON.stringify(response.data?.customer));
        }
      } catch (error) {
        console.error('Failed to get user profile:', error);
        // If getting user info fails, clear token
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      } finally {
        setLoading(false);
      }
    };

    checkAuthStatus();
  }, []);

  // 登录 - 使用useCallback包装
  const login = useCallback(async (email: string, password: string, remember: boolean = false) => {
    setLoading(true);
    setError(null);
    
    try {
      // Send login request
      const response = await apiService.auth.login({ email, password, remember });
      
      // Console log the response to debug
      console.log('Login response:', response);
      
      // 检查API响应格式
      if (response?.status === 'success' && response?.data?.token) {
        // 标准成功响应格式
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.customer));
        setUser(response.data.customer);
        toast.success(response.message || 'Login successful!');
        return response;
      } else if (response?.success === true && response?.data?.token) {
        // 新API格式
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.customer));
        setUser(response.data.customer);
        toast.success(response.message || 'Login successful!');
        return response;
      } else if (response?.token && (response?.customer || response?.user)) {
        // 兼容旧API直接返回token和customer的情况
        localStorage.setItem('token', response.token);
        localStorage.setItem('user', JSON.stringify(response.customer || response.user));
        setUser(response.customer || response.user);
        toast.success('Login successful!');
        return { 
          status: 'success', 
          message: 'Login successful',
          data: { token: response.token, customer: response.customer || response.user }
        };
      } else {
        console.error('Invalid login response format:', response);
        throw new Error('Invalid login response format');
      }
    } catch (error) {
      console.error('Login failed:', error);
      // 安全地获取错误消息
      const errorMessage = 
        error instanceof AxiosError && error.response?.data?.message
        ? error.response.data.message
        : error instanceof Error ? error.message : 'Login failed';
      setError(errorMessage);
      toast.error(errorMessage);
      throw error;
    } finally {
      setLoading(false);
    }
  }, [setLoading, setError, setUser]);

  // 注册 - 使用useCallback包装
  const register = useCallback(async (userData: RegisterData) => {
    setLoading(true);
    setError(null);
    
    try {
      // Send registration request
      const response = await apiService.auth.register(userData);
      
      // Log response for debugging
      console.log('Registration response:', response);
      
      // 检查标准API响应格式
      if (response?.status === 'success') {
        toast.success(response.message || 'Registration successful! Please log in.');
        
        // 如果注册后自动登录（API同时返回了token和customer）
        if (response.data?.token && response.data?.customer) {
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('user', JSON.stringify(response.data.customer));
          setUser(response.data.customer);
          toast.success('Registration and login successful!');
        }
        
        return response;
      } else if (response?.token && response?.customer) {
        // 兼容旧API直接返回token和customer的情况
        localStorage.setItem('token', response.token);
        localStorage.setItem('user', JSON.stringify(response.customer));
        setUser(response.customer);
        toast.success('Registration and login successful!');
        return { 
          status: 'success', 
          message: 'Registration successful',
          data: { token: response.token, customer: response.customer }
        };
      } else {
        // 注册成功但需要登录
        toast.success('Registration successful! Please log in.');
        return response;
      }
    } catch (error) {
      console.error('Registration failed:', error);
      let errorMessage = 'Registration failed. Please try again later.';
      // 安全地处理 Axios 错误
      if (error instanceof AxiosError && error.response) {
          if (error.response.status === 422) {
              const responseErrors = error.response.data?.errors;
              const responseMessage = error.response.data?.message;
              if (responseMessage?.includes('already') && responseMessage?.includes('email')) {
                  errorMessage = 'This email is already registered. Please use another email or try to log in.';
              } else if (responseMessage?.includes('verification code') || (responseErrors && responseErrors.verification_code)) {
                  errorMessage = 'The verification code is invalid or expired. Please request a new one.';
              } else {
                  errorMessage = responseMessage || 'Please check your input and try again.';
              }
          } else {
              errorMessage = error.response.data?.message || errorMessage;
          }
      } else if (error instanceof Error) {
          errorMessage = error.message;
      }
      setError(errorMessage);
      toast.error(errorMessage);
      throw error;
    } finally {
      setLoading(false);
    }
  }, [setLoading, setError, setUser]);

  // 退出登录 - 使用useCallback包装
  const logout = useCallback(async () => {
    setLoading(true);
    
    try {
      console.log('Attempting to logout...');
      
      // 检查是否有token，如果没有就不需要调用API
      const token = localStorage.getItem('token');
      if (!token) {
        console.log('No token found, skipping API call');
        // 直接清理本地存储
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setUser(null);
        toast.success('Logged out successfully');
        return;
      }
      
      // 尝试调用登出API
      const response = await apiService.auth.logout();
      console.log('Logout API response:', response);
      
      // 成功处理
      if (response?.success || response?.status === 'success') {
        toast.success(response.message || 'Logout successful');
      }
    } catch (error) {
      console.error('Logout failed:', error);
      // 安全地记录 Axios 错误信息
      if (error instanceof AxiosError && error.response) {
        console.error('Logout error response:', {
          status: error.response.status,
          data: error.response.data,
          headers: error.response.headers,
        });
      }
      
      // 即使API调用失败，也要清理本地存储
      toast.info('You have been logged out locally');
    } finally {
      // 无论如何都清理本地状态和存储
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      // 清除所有性能缓存
      clearPerformanceCache('all');
      
      setUser(null);
      setLoading(false);
    }
  }, [setLoading, setUser]);

  // 更新用户资料 - 使用useCallback包装
  const updateProfile = useCallback(async (userData: UpdateProfileData) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await apiService.auth.updateProfile(userData);
      const updatedUser = response.data?.customer;
      localStorage.setItem('user', JSON.stringify(updatedUser));
      setUser(updatedUser);
      toast.success('Profile updated successfully');
    } catch (error) {
      console.error('Update profile failed:', error);
      // 安全地获取错误消息
      const errorMessage = 
        error instanceof AxiosError && error.response?.data?.message
        ? error.response.data.message
        : error instanceof Error ? error.message : 'Failed to update profile';
      setError(errorMessage);
      toast.error(errorMessage);
      throw error;
    } finally {
      setLoading(false);
    }
  }, [setLoading, setError, setUser]);

  // 修改密码 - 使用useCallback包装
  const changePassword = useCallback(async (data: { current_password: string; password: string; password_confirmation: string }) => {
    setLoading(true);
    setError(null);
    
    try {
      await apiService.auth.changePassword(data);
      toast.success('Password changed successfully');
    } catch (error) {
      console.error('Change password failed:', error);
      // 安全地获取错误消息
      const errorMessage = 
        error instanceof AxiosError && error.response?.data?.message
        ? error.response.data.message
        : error instanceof Error ? error.message : 'Failed to change password';
      setError(errorMessage);
      toast.error(errorMessage);
      throw error;
    } finally {
      setLoading(false);
    }
  }, [setLoading, setError]);

  const isAuthenticated = !!user;

  const value = {
    user,
    loading,
    error,
    isAuthenticated,
    login,
    register,
    logout,
    updateProfile,
    changePassword
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export default AuthContext;

 

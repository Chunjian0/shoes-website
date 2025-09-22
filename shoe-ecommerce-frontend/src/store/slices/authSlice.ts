import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { apiService } from '../../services/api';
import { toast } from 'react-toastify';

// 调试日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[AuthSlice] ${message}`, data ? data : '');
};

// 用户类型定义
export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  avatar?: string;
  created_at: string;
  updated_at: string;
}

// 认证状态类型定义
interface AuthState {
  isAuthenticated: boolean;
  user: User | null;
  token: string | null;
  loading: boolean;
  error: string | null;
}

// 初始状态
const initialState: AuthState = {
  isAuthenticated: !!localStorage.getItem('token'),
  user: JSON.parse(localStorage.getItem('user') || 'null'),
  token: localStorage.getItem('token'),
  loading: false,
  error: null
};

// 异步Action: 登录
export const login = createAsyncThunk(
  'auth/login',
  async (credentials: { email: string; password: string }, { rejectWithValue }) => {
    try {
      logDebug('用户登录', { email: credentials.email });
      const response = await apiService.auth.login(credentials);
      logDebug('登录成功', response);
      
      // 根据API响应格式处理数据
      let token, user;
      
      if (response.status === 'success' && response.data) {
        // 标准格式响应: { status: 'success', data: { token, customer } }
        token = response.data.token;
        user = response.data.customer;
      } else if (response.token && response.customer) {
        // 兼容格式: 直接返回 { token, customer }
        token = response.token;
        user = response.customer;
      } else {
        return rejectWithValue('无效的API响应格式');
      }
      
      // 保存token和用户信息到localStorage
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      
      return { token, user };
    } catch (error: any) {
      logDebug('登录失败', error);
      return rejectWithValue(error.response?.data?.message || '登录失败，请检查您的凭据');
    }
  }
);

// 异步Action: 注册
export const register = createAsyncThunk(
  'auth/register',
  async (userData: any, { rejectWithValue }) => {
    try {
      logDebug('用户注册', { email: userData.email });
      const response = await apiService.auth.register(userData);
      logDebug('注册成功', response);
      
      // 检查API响应格式
      let token, user;
      
      if (response.status === 'success' && response.data) {
        // 如果注册同时也返回了token（自动登录）
        if (response.data.token && response.data.customer) {
          token = response.data.token;
          user = response.data.customer;
          
          // 保存token和用户信息到localStorage
          localStorage.setItem('token', token);
          localStorage.setItem('user', JSON.stringify(user));
        } else {
          // 注册成功但需要单独登录
          return { 
            success: true, 
            message: response.message || 'Registration successful! Please log in.'
          };
        }
      } else if (response.token && response.customer) {
        // 兼容老格式，直接返回token和customer
        token = response.token;
        user = response.customer;
        
        // 保存token和用户信息到localStorage
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
      } else {
        // 注册成功但需要单独登录
        return { 
          success: true, 
          message: 'Registration successful! Please log in.'
        };
      }
      
      return { token, user };
    } catch (error: any) {
      logDebug('注册失败', error);
      return rejectWithValue(error.response?.data?.message || '注册失败，请稍后再试');
    }
  }
);

// 异步Action: 获取用户资料
export const fetchUserProfile = createAsyncThunk(
  'auth/fetchUserProfile',
  async (_, { rejectWithValue }) => {
    try {
      logDebug('获取用户资料');
      const response = await apiService.auth.getProfile();
      logDebug('获取用户资料成功', response);
      
      // 更新localStorage中的用户信息
      localStorage.setItem('user', JSON.stringify(response));
      
      return response;
    } catch (error: any) {
      logDebug('获取用户资料失败', error);
      return rejectWithValue(error.response?.data?.message || '获取用户资料失败');
    }
  }
);

// 异步Action: 更新用户资料
export const updateUserProfile = createAsyncThunk(
  'auth/updateUserProfile',
  async (userData: any, { rejectWithValue }) => {
    try {
      logDebug('更新用户资料', userData);
      const response = await apiService.auth.updateProfile(userData);
      logDebug('更新用户资料成功', response);
      
      // 更新localStorage中的用户信息
      localStorage.setItem('user', JSON.stringify(response));
      
      return response;
    } catch (error: any) {
      logDebug('更新用户资料失败', error);
      return rejectWithValue(error.response?.data?.message || '更新用户资料失败');
    }
  }
);

// 异步Action: 修改密码
export const changePassword = createAsyncThunk(
  'auth/changePassword',
  async (passwordData: { current_password: string; password: string; password_confirmation: string }, { rejectWithValue }) => {
    try {
      logDebug('修改密码');
      const response = await apiService.auth.changePassword(passwordData);
      logDebug('修改密码成功', response);
      return response;
    } catch (error: any) {
      logDebug('修改密码失败', error);
      return rejectWithValue(error.response?.data?.message || '修改密码失败');
    }
  }
);

// 异步Action: 忘记密码
export const forgotPassword = createAsyncThunk(
  'auth/forgotPassword',
  async (data: { email: string }, { rejectWithValue }) => {
    try {
      logDebug('忘记密码', data);
      const response = await apiService.auth.forgotPassword(data);
      logDebug('忘记密码请求成功', response);
      return response;
    } catch (error: any) {
      logDebug('忘记密码请求失败', error);
      return rejectWithValue(error.response?.data?.message || '忘记密码请求失败');
    }
  }
);

// 异步Action: 重置密码
export const resetPassword = createAsyncThunk(
  'auth/resetPassword',
  async (data: { token: string; email: string; password: string; password_confirmation: string }, { rejectWithValue }) => {
    try {
      logDebug('重置密码', { email: data.email });
      const response = await apiService.auth.resetPassword(data);
      logDebug('重置密码成功', response);
      return response;
    } catch (error: any) {
      logDebug('重置密码失败', error);
      return rejectWithValue(error.response?.data?.message || '重置密码失败');
    }
  }
);

// 异步Action: 登出
export const logout = createAsyncThunk(
  'auth/logout',
  async (_, { rejectWithValue }) => {
    try {
      logDebug('用户登出');
      
      // 如果有token，调用登出API
      if (localStorage.getItem('token')) {
        try {
          await apiService.auth.logout();
          logDebug('API登出成功');
        } catch (error: any) {
          logDebug('API登出失败，但将继续本地登出', error);
          // 不抛出错误，继续进行本地登出流程
        }
      }
      
      // 清除localStorage中的token和用户信息
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      // 触发登出事件，以便其他组件可以清理状态
      try {
        window.dispatchEvent(new Event('logout'));
      } catch (e) {
        logDebug('触发登出事件失败', e);
      }
      
      logDebug('登出成功');
      return true;
    } catch (error: any) {
      logDebug('登出失败', error);
      
      // 即使出现错误，也清除本地存储
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      // 尝试触发登出事件
      try {
        window.dispatchEvent(new Event('logout'));
      } catch (e) {
        logDebug('触发登出事件失败', e);
      }
      
      return rejectWithValue(error.response?.data?.message || '登出失败');
    }
  }
);

// 认证切片
const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    // 清除认证错误
    clearAuthError: (state) => {
      logDebug('清除认证错误');
      state.error = null;
    },
    
    // 手动设置认证状态（用于初始化或测试）
    setAuth: (state, action: PayloadAction<{ isAuthenticated: boolean; user: User | null; token: string | null }>) => {
      logDebug('手动设置认证状态', action.payload);
      state.isAuthenticated = action.payload.isAuthenticated;
      state.user = action.payload.user;
      state.token = action.payload.token;
      
      // 更新localStorage
      if (action.payload.isAuthenticated && action.payload.token && action.payload.user) {
        localStorage.setItem('token', action.payload.token);
        localStorage.setItem('user', JSON.stringify(action.payload.user));
      } else {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      }
    },
    setUser: (state, action: PayloadAction<User | null>) => {
      state.user = action.payload;
      state.isAuthenticated = !!action.payload;
      if (action.payload) {
        localStorage.setItem('user', JSON.stringify(action.payload));
      } else {
        localStorage.removeItem('user');
        localStorage.removeItem('token'); // Ensure token is also cleared
        state.token = null;
        state.isAuthenticated = false;
      }
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.loading = action.payload;
    },
    setError: (state, action: PayloadAction<string | null>) => {
      state.error = action.payload;
    }
  },
  extraReducers: (builder) => {
    // 处理登录
    builder
      .addCase(login.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(login.fulfilled, (state, action) => {
        state.loading = false;
        state.isAuthenticated = true;
        state.user = action.payload.user;
        state.token = action.payload.token;
        toast.success('登录成功！');
      })
      .addCase(login.rejected, (state, action) => {
        state.loading = false;
        state.isAuthenticated = false;
        state.user = null;
        state.token = null;
        state.error = action.payload as string;
        toast.error(action.payload as string || '登录失败');
      })
    
    // 处理注册
    builder
      .addCase(register.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(register.fulfilled, (state, action) => {
        state.loading = false;
        // 如果注册后自动登录
        if (action.payload.token && action.payload.user) {
          state.isAuthenticated = true;
          state.user = action.payload.user;
          state.token = action.payload.token;
          toast.success('注册成功并已登录！');
        } else if (action.payload.success) {
          // 注册成功但需要单独登录
          toast.success(action.payload.message || '注册成功！请登录');
        }
      })
      .addCase(register.rejected, (state, action) => {
        state.loading = false;
        state.isAuthenticated = false;
        state.user = null;
        state.token = null;
        state.error = action.payload as string;
        toast.error(action.payload as string || '注册失败');
      })
    
    // 处理获取用户资料
    builder
      .addCase(fetchUserProfile.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchUserProfile.fulfilled, (state, action) => {
        state.loading = false;
        state.user = action.payload;
        state.isAuthenticated = true;
        state.error = null;
      })
      .addCase(fetchUserProfile.rejected, (state, action) => {
        state.loading = false;
        state.isAuthenticated = false;
        state.user = null;
        state.token = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      })
    
    // 处理更新用户资料
    builder
      .addCase(updateUserProfile.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(updateUserProfile.fulfilled, (state, action) => {
        state.loading = false;
        state.user = action.payload;
        toast.success('个人资料已更新');
      })
      .addCase(updateUserProfile.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
        toast.error(action.payload as string || '更新个人资料失败');
      })
    
    // 处理修改密码
    builder
      .addCase(changePassword.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(changePassword.fulfilled, (state) => {
        state.loading = false;
        toast.success('密码已成功修改');
      })
      .addCase(changePassword.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
        toast.error(action.payload as string || '修改密码失败');
      })
    
    // 处理忘记密码
    builder
      .addCase(forgotPassword.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(forgotPassword.fulfilled, (state) => {
        state.loading = false;
        toast.success('密码重置链接已发送到您的邮箱');
      })
      .addCase(forgotPassword.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
        toast.error(action.payload as string || '发送密码重置链接失败');
      })
    
    // 处理重置密码
    builder
      .addCase(resetPassword.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(resetPassword.fulfilled, (state) => {
        state.loading = false;
        toast.success('密码已成功重置，请登录');
      })
      .addCase(resetPassword.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
        toast.error(action.payload as string || '重置密码失败');
      })
    
    // 处理登出
    builder
      .addCase(logout.pending, (state) => {
        state.loading = true;
      })
      .addCase(logout.fulfilled, (state) => {
        state.loading = false;
        state.isAuthenticated = false;
        state.user = null;
        state.token = null;
        toast.success('您已成功登出');
      })
      .addCase(logout.rejected, (state) => {
        state.loading = false;
        state.isAuthenticated = false;
        state.user = null;
        state.token = null;
        toast.info('您已登出');
      });
  }
});

// 导出Action
export const { clearAuthError, setAuth, setUser, setLoading, setError } = authSlice.actions;

// 导出Reducer
export default authSlice.reducer; 
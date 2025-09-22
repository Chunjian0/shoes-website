import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { AppDispatch, RootState } from '../store';
import { fetchUserProfile, logout, login, register, changePassword, updateUserProfile } from '../store/slices/authSlice';
import { apiService } from '../services/api';

export const useAuth = () => {
  const dispatch = useDispatch<AppDispatch>();
  const { isAuthenticated, user, loading, error } = useSelector((state: RootState) => state.auth);
  const [authChecked, setAuthChecked] = useState<boolean>(false);

  // 检查认证状态
  useEffect(() => {
    const checkAuth = async () => {
      try {
        // 如果已经有token，但没有用户信息，则获取用户信息
        if (localStorage.getItem('token') && !user) {
          await dispatch(fetchUserProfile()).unwrap();
        }
        
        // 如果有token，验证token是否有效
        if (localStorage.getItem('token')) {
          const response = await apiService.auth.checkSession();
          if (!response.authenticated) {
            await dispatch(logout());
          }
        }
      } catch (error) {
        console.error('认证验证失败:', error);
        await dispatch(logout());
      } finally {
        setAuthChecked(true);
      }
    };

    checkAuth();
  }, [dispatch, user]);

  // 登录方法
  const handleLogin = async (credentials: { email: string; password: string }) => {
    try {
      await dispatch(login(credentials)).unwrap();
      return true;
    } catch (error) {
      return false;
    }
  };

  // 注册方法
  const handleRegister = async (userData: any) => {
    try {
      await dispatch(register(userData)).unwrap();
      return true;
    } catch (error) {
      return false;
    }
  };

  // 登出方法
  const handleLogout = async () => {
    try {
      await dispatch(logout()).unwrap();
      return true;
    } catch (error) {
      return false;
    }
  };

  // 更新用户资料
  const handleUpdateProfile = async (userData: any) => {
    try {
      await dispatch(updateUserProfile(userData)).unwrap();
      return true;
    } catch (error) {
      return false;
    }
  };

  // 修改密码
  const handleChangePassword = async (passwordData: { current_password: string; password: string; password_confirmation: string }) => {
    try {
      await dispatch(changePassword(passwordData)).unwrap();
      return true;
    } catch (error) {
      return false;
    }
  };

  return {
    isAuthenticated,
    user,
    loading,
    error,
    authChecked,
    login: handleLogin,
    register: handleRegister,
    logout: handleLogout,
    updateProfile: handleUpdateProfile,
    changePassword: handleChangePassword
  };
}; 
import { ReactNode, useEffect } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAppSelector, useAppDispatch } from '../store';
import { fetchUserProfile } from '../store/slices/authSlice';
import LoadingSpinner from './LoadingSpinner';

// 调试日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[ProtectedRoute] ${message}`, data ? data : '');
};

interface ProtectedRouteProps {
  children: ReactNode;
  redirectTo?: string;
}

const ProtectedRoute = ({ 
  children, 
  redirectTo = '/login' 
}: ProtectedRouteProps) => {
  const location = useLocation();
  const dispatch = useAppDispatch();
  
  // 从Redux获取认证状态
  const { isAuthenticated, loading, user } = useAppSelector(state => state.auth);
  
  // 组件加载时获取用户资料（如果已认证但没有用户资料）
  useEffect(() => {
    if (isAuthenticated && !user && !loading) {
      logDebug('已认证但没有用户资料，获取用户资料');
      dispatch(fetchUserProfile());
    }
  }, [isAuthenticated, user, loading, dispatch]);
  
  // 如果正在加载，显示加载状态
  if (loading) {
    logDebug('正在加载用户资料');
    return (
      <div className="flex justify-center items-center h-screen">
        <LoadingSpinner size="large" />
      </div>
    );
  }
  
  // 如果未认证，重定向到登录页面
  if (!isAuthenticated) {
    logDebug('未认证，重定向到登录页面', { from: location.pathname });
    return <Navigate to={redirectTo} state={{ from: location }} replace />;
  }
  
  // 已认证，渲染子组件
  logDebug('已认证，渲染受保护内容');
  return <>{children}</>;
};

export default ProtectedRoute; 
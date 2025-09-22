import React from 'react';
import { useSelector } from 'react-redux';
import { RootState } from '../../store';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';

interface CartAuthCheckProps {
  children: React.ReactNode;
  onAuthFail?: () => void;
  productId?: number | string;
}

/**
 * 检查用户在操作购物车时的登录状态
 * 如果未登录，将重定向到登录页面
 */
const CartAuthCheck: React.FC<CartAuthCheckProps> = ({ 
  children, 
  onAuthFail,
  productId 
}) => {
  const { isAuthenticated, loading } = useSelector((state: RootState) => state.auth);
  const navigate = useNavigate();

  // 处理未授权情况
  const handleUnauthenticated = () => {
    toast.warning('请先登录后再操作');
    
    // 如果提供了失败回调函数，则调用
    if (onAuthFail) {
      onAuthFail();
    }
    
    // 保存当前URL，登录后可以返回
    const returnUrl = productId 
      ? `/products/${productId}` 
      : window.location.pathname;
      
    // 重定向到登录页面，并传递返回URL
    navigate(`/login?returnUrl=${encodeURIComponent(returnUrl)}`);
    
    return null;
  };

  // 如果正在加载，返回子组件
  if (loading) {
    return <>{children}</>;
  }

  // 如果未登录，处理未授权情况
  if (!isAuthenticated) {
    return handleUnauthenticated();
  }

  // 如果已登录，直接渲染子组件
  return <>{children}</>;
};

export default CartAuthCheck; 
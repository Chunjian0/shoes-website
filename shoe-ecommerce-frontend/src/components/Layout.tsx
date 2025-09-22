import { useEffect } from 'react';
import { Outlet } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store';
import { fetchUserProfile } from '../store/slices/authSlice';
import { fetchCart } from '../store/slices/cartSlice';
import Header from './layout/Header';
import Footer from './layout/Footer';

// 调试日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[Layout] ${message}`, data ? data : '');
};

const Layout = () => {
  const dispatch = useAppDispatch();
  
  // 从Redux获取认证状态
  const { isAuthenticated } = useAppSelector(state => state.auth);
  
  // 组件加载时获取用户资料和购物车
  useEffect(() => {
    logDebug('布局组件挂载');
    
    // 如果已认证，获取用户资料
    if (isAuthenticated) {
      logDebug('已认证，获取用户资料');
      dispatch(fetchUserProfile())
        .unwrap()
        .then(() => {
          logDebug('获取用户资料成功');
        })
        .catch(error => {
          logDebug('获取用户资料失败', error);
        });
      
      // 获取购物车
      logDebug('获取购物车');
      dispatch(fetchCart())
        .unwrap()
        .then(() => {
          logDebug('获取购物车成功');
        })
        .catch(error => {
          logDebug('获取购物车失败', error);
        });
    }
  }, [isAuthenticated, dispatch]);
  
  return (
    <div className="flex flex-col min-h-screen">
      <Header />
      <main className="flex-grow">
        <Outlet />
      </main>
      <Footer />
    </div>
  );
};

export default Layout; 
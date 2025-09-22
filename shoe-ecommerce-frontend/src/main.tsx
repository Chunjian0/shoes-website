import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { Provider } from 'react-redux';
import { store } from './store';
import { AuthProvider } from './contexts/AuthContext';
import { HelmetProvider } from 'react-helmet-async';
import App from './App';
import './index.css';
import homepageService from './services/homepageService';
import { toast } from 'react-toastify';

// 设置全局错误处理
window.addEventListener('error', (event) => {
  console.error('Global error caught:', event.error);
  // 可以在这里添加错误上报逻辑
});

// 全局未捕获的Promise错误处理
window.addEventListener('unhandledrejection', (event) => {
  console.error('Unhandled Promise rejection:', event.reason);
  // 可以在这里添加错误上报逻辑
});

// 在应用启动时预加载首页设置到Redis缓存 - REMOVED as getHomePageData handles this
/*
homepageService.fetchHomepageSettingsToRedis()
  .then(() => console.log('Successfully preloaded homepage settings to Redis cache'))
  .catch(error => {
    console.error('Failed to preload homepage settings', error);
    // 使用模拟数据，确保应用可以启动
    toast.warning('使用本地缓存数据，部分功能可能受限');
  });
*/

// 启动React应用
ReactDOM.createRoot(document.getElementById('root') as HTMLElement).render(
  <React.StrictMode>
    <BrowserRouter>
      <Provider store={store}>
        <AuthProvider>
          <HelmetProvider>
            <App />
          </HelmetProvider>
        </AuthProvider>
      </Provider>
    </BrowserRouter>
  </React.StrictMode>
); 
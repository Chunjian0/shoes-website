import React, { useEffect, useState } from 'react';
import { Routes, Route, useLocation, Navigate, useParams } from 'react-router-dom';
import { Slide, ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { Provider } from 'react-redux';
import store from './store';
import { useAppSelector } from './store'; // Added Redux hook

// Layout Components
import Header from './components/layout/Header';
import Footer from './components/layout/Footer';
import SessionManager from './components/SessionManager';

// Pages
import HomePage from './pages/HomePage';
import DevTools from './pages/DevTools';
import ProductsPage from './pages/ProductsPage';
import ProductDetailPage from './pages/ProductDetailPage';
import TemplateDetailPage from './pages/TemplateDetailPage';
import CartPage from './pages/CartPage';
import CheckoutPage from './pages/CheckoutPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import AccountPage from './pages/AccountPage';
import OrdersPage from './pages/OrdersPage';
import OrderDetailPage from './pages/OrderDetailPage';
import NotFoundPage from './pages/NotFoundPage';
import AboutPage from './pages/AboutPage';
import ContactPage from './pages/ContactPage';
import SearchPage from './pages/SearchPage';
import ProductFilterDemo from './pages/ProductFilterDemo';
import ForgotPasswordPage from './pages/ForgotPasswordPage';
import ResetPasswordPage from './pages/ResetPasswordPage';
import NikePegasusPromoPage from './pages/promo/NikePegasusPromoPage';
import AdidasAdiosProPromoPage from './pages/promo/AdidasAdiosProPromoPage';
import NikeAF1PromoPage from './pages/promo/NikeAF1PromoPage';
import DrMartensPromoPage from './pages/promo/DrMartensPromoPage';
import ConverseChuckTaylorPromoPage from './pages/promo/ConverseChuckTaylorPromoPage';
import AdidasSuperstarPromoPage from './pages/promo/AdidasSuperstarPromoPage';
import HavaianasPromoPage from './pages/promo/HavaianasPromoPage';
import AllenEdmondsPromoPage from './pages/promo/AllenEdmondsPromoPage';
import CrocsClassicClogPromoPage from './pages/promo/CrocsClassicClogPromoPage';
import BlundstonePromoPage from './pages/promo/BlundstonePromoPage';
import GHBassLarsonPromoPage from './pages/promo/GHBassLarsonPromoPage';
import AsicsGelKayanoPromoPage from './pages/promo/AsicsGelKayanoPromoPage';
import OrderConfirmationPage from './pages/OrderConfirmationPage';
import OrderHistoryPage from './pages/account/OrderHistoryPage';
import FAQPage from './pages/FAQPage';
import ShippingPolicyPage from './pages/ShippingPolicyPage';
import ReturnsPolicyPage from './pages/ReturnsPolicyPage';
import PrivacyPolicyPage from './pages/PrivacyPolicyPage';
import TermsOfServicePage from './pages/TermsOfServicePage';

// Global styles
import './styles/global.css';

// Context Providers
import { HomepageSettingsProvider } from './contexts/HomepageSettingsContext';
import { CartProvider } from './contexts/CartContext';
import HomepageSettingsStatus from './components/HomepageSettingsStatus';
import DebugControls from './components/DebugControls';
import DebugPanel from './components/DebugPanel';
import ProtectedRoute from './components/ProtectedRoute';

// Performance utilities
import { initPerformance } from './utils/performance';

// Redirect component for template to product conversion
const TemplateToProductRedirect = () => {
  const { id } = useParams();
  return <Navigate to={`/products/${id}`} replace />;
};

// Show Header and Footer based on route
const AppContent = () => {
  const location = useLocation();
  const isHomePage = location.pathname === '/';
  const { isAuthenticated, loading } = useAppSelector(state => state.auth); // Get state from Redux
  const [authChecked, setAuthChecked] = useState(false);

  // 确保认证状态已检查完成
  useEffect(() => {
    if (!loading) {
      setAuthChecked(true);
      console.log('认证状态检查完成:', { isAuthenticated, pathname: location.pathname });
    }
  }, [loading, isAuthenticated, location.pathname]);

  // 如果认证状态检查中，显示加载指示器
  if (!authChecked) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  return (
    <div className="flex flex-col min-h-screen bg-gray-50">
      <ToastContainer
        position="top-right"
        autoClose={5000}
        hideProgressBar={false}
        newestOnTop
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        transition={Slide}
      />
      {/* Session Manager to keep user logged in */}
      <SessionManager />
      
      {/* Only show Header on non-homepage */}
      {!isHomePage && <Header />}
      
      <main className={`flex-grow ${!isHomePage ? 'pt-16' : ''}`}>
        <Routes>
          {/* 公共路由 - 不需要认证 */}
          <Route path="/" element={<HomePage />} />
          {/* 健康检查路由 - 返回 HTTP 200 即可 */}
          <Route path="/health" element={<div>OK</div>} />
          <Route path="/dev-tools" element={<DevTools />} />
          <Route path="/products" element={<ProductsPage />} />
          <Route path="/products/:id" element={<TemplateDetailPage />} />
          <Route path="/templates/:id" element={<TemplateToProductRedirect />} />
          <Route path="/about" element={<AboutPage />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/search" element={<SearchPage />} />
          <Route path="/login" element={
            isAuthenticated ? <Navigate to="/" replace /> : <LoginPage />
          } />
          <Route path="/register" element={
            isAuthenticated ? <Navigate to="/" replace /> : <RegisterPage />
          } />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/reset-password/:token" element={<ResetPasswordPage />} />
          <Route path="/filter-demo" element={<ProductFilterDemo />} />
          
          {/* Promo Pages Routes */}
          <Route path="/promo/dr-martens-1460" element={<DrMartensPromoPage />} />
          <Route path="/promo/nike-pegasus-41" element={<NikePegasusPromoPage />} />
          <Route path="/promo/adidas-adios-pro-3" element={<AdidasAdiosProPromoPage />} />
          <Route path="/promo/nike-af1-07" element={<NikeAF1PromoPage />} />
          <Route path="/promo/converse-chuck-taylor" element={<ConverseChuckTaylorPromoPage />} />
          <Route path="/promo/adidas-superstar" element={<AdidasSuperstarPromoPage />} />
          <Route path="/promo/havaianas-top" element={<HavaianasPromoPage />} />
          <Route path="/promo/allen-edmonds-strand" element={<AllenEdmondsPromoPage />} />
          <Route path="/promo/crocs-classic-clog" element={<CrocsClassicClogPromoPage />} />
          <Route path="/promo/blundstone-550" element={<BlundstonePromoPage />} />
          <Route path="/promo/gh-bass-larson" element={<GHBassLarsonPromoPage />} />
          <Route path="/promo/asics-gel-kayano-30" element={<AsicsGelKayanoPromoPage />} />
          
          
          {/* 受保护路由 - 需要认证 */}
          <Route path="/cart" element={
            <ProtectedRoute>
              <CartPage />
            </ProtectedRoute>
          } />
          <Route path="/cart-management" element={
            <ProtectedRoute>
              <Navigate to="/cart" replace />
            </ProtectedRoute>
          } />
          <Route path="/checkout" element={
            <ProtectedRoute>
              <CheckoutPage />
            </ProtectedRoute>
          } />
          <Route path="/account" element={
            <ProtectedRoute>
              <AccountPage />
            </ProtectedRoute>
          } />
          <Route path="/orders" element={
            <ProtectedRoute>
              <OrdersPage />
            </ProtectedRoute>
          } />
          <Route path="/orders/:id" element={
            <ProtectedRoute>
              <OrderDetailPage />
            </ProtectedRoute>
          } />
          <Route path="/order-confirmation/:orderId" element={<OrderConfirmationPage />} />
          <Route path="/account/orders" element={<OrderHistoryPage />} />
          <Route path="/faq" element={<FAQPage />} />
          <Route path="/shipping" element={<ShippingPolicyPage />} />
          <Route path="/returns" element={<ReturnsPolicyPage />} />
          <Route path="/privacy" element={<PrivacyPolicyPage />} />
          <Route path="/terms" element={<TermsOfServicePage />} />
          
          {/* 404路由 */}
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>
      
      {/* Only show Footer on non-homepage */}
      {!isHomePage && <Footer />}
      
      {/* Show settings status in development */}
      {/* <HomepageSettingsStatus /> */}

      {/* Debug Tools (Render conditionally or based on env) */}
      {/* process.env.NODE_ENV === 'development' && ( */}
      {/* <DebugControls /> */}
      {/* <DebugPanel /> */}
      {/* ) */}
    </div>
  );
};

function App() {
  // Initialize app when it loads
  useEffect(() => {
    const initializeApp = async () => {
      console.log('[App] Initializing application...');
      
      // 初始化性能工具
      initPerformance({
        // 开发环境下启用详细日志
        debugLevel: process.env.NODE_ENV === 'development' ? 2 : 1,
        // 根据环境设置会话检查频率
        sessionCheckFrequencyMs: process.env.NODE_ENV === 'development' 
          ? 10 * 60 * 1000  // 开发环境10分钟
          : 5 * 60 * 1000,  // 生产环境5分钟
        // 启用性能监控
        enableMonitoring: true,
        // 配置缓存
        cacheApiResponses: true,
        cacheMaxAge: 5 * 60 * 1000, // 5分钟
      });
    };

    initializeApp();
  }, []);

  return (
    <Provider store={store}>
      <HomepageSettingsProvider>
        <CartProvider>
          <AppContent />
        </CartProvider>
      </HomepageSettingsProvider>
    </Provider>
  );
}

export default App; 
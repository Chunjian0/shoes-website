import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useSelector } from 'react-redux';
import { RootState } from '../../store';
import { toast } from 'react-toastify';
import { motion } from 'framer-motion';

interface CartLinkProps {
  children?: React.ReactNode;
  className?: string;
  showCount?: boolean;
  isMobile?: boolean;
}

/**
 * 购物车链接组件 - 处理购物车导航和认证检查
 */
const CartLink: React.FC<CartLinkProps> = ({ 
  children, 
  className = "",
  showCount = true,
  isMobile = false
}) => {
  const navigate = useNavigate();
  const { isAuthenticated } = useSelector((state: RootState) => state.auth);
  const { cartCount } = useSelector((state: RootState) => state.cart);
  
  // 处理购物车点击
  const handleCartClick = (e: React.MouseEvent) => {
    e.preventDefault();
    console.log('购物车点击 - 认证状态:', isAuthenticated);
    
    if (isAuthenticated) {
      // 已登录，导航到购物车页面
      navigate('/cart');
    } else {
      // 未登录，提示并导航到登录页面
      toast.warning('请先登录后再查看购物车');
      navigate(`/login?returnUrl=${encodeURIComponent('/cart')}`);
    }
  };
  
  return (
    <a 
      href="/cart"
      onClick={handleCartClick}
      className={`relative ${className}`}
      aria-label="Shopping Cart"
    >
      {children ? (
        children
      ) : (
        <motion.div
          whileHover={{ scale: 1.1 }}
          whileTap={{ scale: 0.95 }}
          className="text-gray-700 hover:text-blue-600 transition-colors duration-300"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          
          {showCount && cartCount > 0 && (
            <motion.span 
              initial={{ scale: 0 }}
              animate={{ scale: 1 }}
              className={`absolute ${isMobile ? '-top-1 -right-1' : '-top-2 -right-2'} bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium`}
            >
              {cartCount > 99 ? '99+' : cartCount}
            </motion.span>
          )}
        </motion.div>
      )}
    </a>
  );
};

export default CartLink; 
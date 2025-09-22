// 导入必要的组件和hooks
import { useSelector } from 'react-redux';
import { RootState } from '../../store';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';

// 在组件函数内实现登录状态检查
const CartButton = () => {
  const { isAuthenticated } = useSelector((state: RootState) => state.auth);
  const navigate = useNavigate();
  
  // 处理点击事件
  const handleCartClick = () => {
    if (!isAuthenticated) {
      toast.warning('请先登录后再查看购物车');
      navigate('/login?returnUrl=' + encodeURIComponent('/cart'));
      return;
    }
    
    // 如果已登录，导航到购物车页面
    navigate('/cart');
  };
  
  return (
    <button 
      onClick={handleCartClick}
      className="relative p-2 text-gray-700 hover:text-blue-600 transition-colors duration-300"
      aria-label="Shopping Cart"
    >
      {/* 购物车按钮的内容 */}
    </button>
  );
};

export default CartButton; 
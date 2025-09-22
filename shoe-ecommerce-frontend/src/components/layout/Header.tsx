import { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAppSelector, useAppDispatch } from '../../store';
import { logout } from '../../store/slices/authSlice';
import { clearCart } from '../../store/slices/cartSlice';
import { toggleMobileMenu, closeMobileMenu, toggleSearch } from '../../store/slices/uiSlice';
import { motion, AnimatePresence } from 'framer-motion';
import { apiService } from '../../services/api';
import { ProductCategory } from '../../types/apiTypes';
import templateService from '../../services/templateService';
import { toast } from 'react-toastify';
import CartLink from '../cart/CartLink';

// 调试日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[Header] ${message}`, data ? data : '');
};

// 动画变量
const navVariants = {
  hidden: { opacity: 0, y: -20 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: {
      duration: 0.5,
      staggerChildren: 0.1
    }
  }
};

const linkVariants = {
  hidden: { opacity: 0, y: -10 },
  visible: { opacity: 1, y: 0 }
};

const dropdownVariants = {
  hidden: { opacity: 0, y: -5, height: 0 },
  visible: { 
    opacity: 1, 
    y: 0, 
    height: 'auto',
    transition: { duration: 0.3 }
  }
};

const Header = () => {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const headerRef = useRef<HTMLDivElement>(null);
  const [isScrolled, setIsScrolled] = useState(false);
  const [hoverItem, setHoverItem] = useState<string | null>(null);
  const [categories, setCategories] = useState<ProductCategory[]>([]);
  
  // 从Redux获取状态
  const { isAuthenticated, user } = useAppSelector(state => state.auth);
  const { carts, cartCount } = useAppSelector(state => state.cart);
  const { isMobileMenuOpen, isSearchOpen } = useAppSelector(state => state.ui);
  
  // 搜索状态
  const [searchQuery, setSearchQuery] = useState('');
  
  // 监听滚动事件并更新header样式
  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 30) {
        setIsScrolled(true);
      } else {
        setIsScrolled(false);
      }
    };
    
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  
  // 获取产品类别数据
  useEffect(() => {
    const fetchCategories = async () => {
      try {
        // 首先尝试正常的API调用获取分类数据
        console.log('尝试从/product-categories API获取分类数据');
        const response: any = await apiService.categories.getAll(); // 使用 any 类型以处理不确定的结构
        console.log('获取到API响应:', response);
        
        let categoriesData: ProductCategory[] = [];

        // 检查各种可能的响应结构
        if (response?.data?.categories && Array.isArray(response.data.categories)) {
          categoriesData = response.data.categories;
        } else if (response?.data && Array.isArray(response.data)) {
          categoriesData = response.data;
        } else if (response?.categories && Array.isArray(response.categories)) {
          categoriesData = response.categories;
        } else if (Array.isArray(response)) {
          // 直接返回数组的情况
          categoriesData = response;
        }
        
          if (categoriesData.length > 0) {
          console.log('成功从API响应获取类别:', categoriesData);
            setCategories(categoriesData);
            return;
        } else {
          console.log('API响应中未找到有效的分类数据，尝试从模板获取');
          await fetchCategoriesFromTemplates();
        }

      } catch (error) {
        console.error('获取分类数据时出错:', error);
        // 回退到模板方法
        await fetchCategoriesFromTemplates();
      }
    };
    
    // 从模板中获取类别的备用方法
    const fetchCategoriesFromTemplates = async () => {
      try {
        // 尝试从模板详情中获取类别信息
        console.log('Attempting to get details of template #1 to extract category information');
        const templateData = await templateService.getTemplateDetails(1);
        console.log('Retrieved template data:', templateData);
        
        // 使用从模板中获取的类别信息
        if (templateData && templateData.category) {
          console.log('Extracted category information from template:', templateData.category);
          
          // 从模板服务获取类别信息后，尝试获取更多相关模板
          console.log('Attempting to get related templates to extract more category information');
          const relatedTemplates = await templateService.getRelatedTemplates(templateData.id, templateData.category.name);
          console.log('Retrieved related templates:', relatedTemplates);
          
          // 整合所有类别
          const allCategories = new Map<number, ProductCategory>();
          
          // 添加主模板类别
          if (templateData.category.id && templateData.category.name) {
            allCategories.set(templateData.category.id, templateData.category);
          }
          
          // 添加相关模板类别
          relatedTemplates.forEach(template => {
            if (template.category && template.category.id && template.category.name) {
              allCategories.set(template.category.id, template.category);
            }
          });
          
          // 将Map转为数组
          const uniqueCategories = Array.from(allCategories.values());
          console.log('整合所有类别:', uniqueCategories);
          
          if (uniqueCategories.length > 0) {
            setCategories(uniqueCategories);
            return;
          }
        }
        
        console.log('Failed to get enough category information from templates, using default categories');
        // 设置默认类别
        setCategories([
          { id: 1, name: 'Lens' },
          { id: 2, name: 'Optical' },
          { id: 3, name: 'Accessories' }
        ]);
      } catch (error) {
        console.error('Failed to get category information:', error);
        // 设置默认类别
        setCategories([
          { id: 1, name: 'Lens' },
          { id: 2, name: 'Optical' },
          { id: 3, name: 'Accessories' }
        ]);
      }
    };

    // 只在组件首次加载时获取类别
    fetchCategories();
  }, []);
  // 处理搜索提交
  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      logDebug('Submit search', { query: searchQuery });
      dispatch(closeMobileMenu());
      navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
      setSearchQuery('');
      dispatch(toggleSearch());
    }
  };
  
  // 处理登出
  const handleLogout = async () => {
    try {
      // 登出用户
      await dispatch(logout()).unwrap();
      
      // 清除购物车状态
      dispatch(clearCart());
      
      // 导航到首页
      navigate('/');
      
      // 显示成功消息
      toast.success('Successfully logged out');
    } catch (error) {
      console.error('Logout failed:', error);
      toast.error('Logout error occurred');
    }
  };
  
  // 测试认证状态
  const testAuthStatus = () => {
    console.log('Testing authentication status:', { 
      isAuthenticated, 
      user,
      token: localStorage.getItem('token'),
      userInStorage: JSON.parse(localStorage.getItem('user') || 'null')
    });
    
    if (isAuthenticated) {
      toast.success('You are logged in, user: ' + (user?.name || 'Unknown'));
    } else {
      toast.error('You are not logged in');
    }
  };
  
  // 监听窗口大小变化，在大屏幕上自动关闭移动菜单
  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth >= 1024 && isMobileMenuOpen) {
        dispatch(closeMobileMenu());
      }
    };
    
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, [isMobileMenuOpen, dispatch]);

  // 循环动画
  const logoAnimation = {
    hidden: { scale: 1 },
    animate: {
      scale: [1, 1.05, 1],
      transition: { 
        duration: 2,
        repeat: Infinity,
        repeatType: "mirror" as const,
        ease: "easeInOut"
      }
    }
  };
  
  return (
    <motion.header
      ref={headerRef}
      className={`sticky top-0 z-50 transition-all duration-300 ease-out ${ 
        isScrolled 
          ? 'bg-white/90 backdrop-blur-md shadow-md' 
          : 'bg-white'
      }`}
      initial={{ y: -100 }}
      animate={{ y: 0 }}
      transition={{ type: 'spring', stiffness: 100, damping: 20 }}
    >
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center h-16 md:h-20">
          {/* Logo */} 
          <motion.div className="flex-shrink-0" variants={linkVariants}>
            <Link to="/" className="flex items-baseline">
              <motion.span 
                className="text-2xl md:text-3xl font-serif font-bold tracking-wider text-gray-900 mr-1"
                whileHover={{ scale: 1.05, color: '#4f46e5' }} // Indigo hover
              >
                YCE
              </motion.span>
              <motion.span 
                className="text-2xl md:text-3xl font-light italic text-indigo-600"
                whileHover={{ scale: 1.05 }}
              >
                Shoes
              </motion.span>
            </Link>
          </motion.div>
          
          {/* Desktop Navigation */} 
          <motion.nav 
            className="hidden lg:flex items-center space-x-8"
            variants={navVariants}
            initial="hidden"
            animate="visible"
          >
            <motion.div variants={linkVariants}>
              <NavLinkItem to="/">HOME</NavLinkItem>
            </motion.div>
            <motion.div variants={linkVariants}>
              <NavLinkItem to="/products">PRODUCTS</NavLinkItem>
            </motion.div>
             {/* Category Dropdown */}
            <motion.div 
               className="relative" 
               onHoverStart={() => setHoverItem('categories')} 
               onHoverEnd={() => setHoverItem(null)}
              variants={linkVariants}
             >
               <NavLinkItem to="#">CATEGORIES</NavLinkItem> 
              <AnimatePresence>
                 {hoverItem === 'categories' && categories.length > 0 && (
                  <motion.div
                     className="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden"
                    variants={dropdownVariants}
                    initial="hidden"
                    animate="visible"
                    exit="hidden"
                  >
                     <div className="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                       {categories.map(category => (
                      <Link 
                        key={category.id}
                           to={`/products?category=${category.id}`}
                           className="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-150"
                           role="menuitem"
                           onClick={() => setHoverItem(null)} // Close on click
                      >
                        {category.name}
                      </Link>
                    ))}
                     </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </motion.div>
            <motion.div variants={linkVariants}>
              <NavLinkItem to="/about">ABOUT US</NavLinkItem>
            </motion.div>
            <motion.div variants={linkVariants}>
              <NavLinkItem to="/contact">CONTACT US</NavLinkItem>
            </motion.div>
          </motion.nav>

          {/* Desktop Icons & Auth */} 
          <div className="hidden lg:flex items-center space-x-5">
            {/* Search Icon/Bar */} 
            <motion.div className="relative" variants={linkVariants}>
              <AnimatePresence>
              {isSearchOpen ? (
                <motion.form 
                   onSubmit={handleSearchSubmit}
                   initial={{ width: 0, opacity: 0 }}
                   animate={{ width: 200, opacity: 1 }}
                   exit={{ width: 0, opacity: 0 }}
                   transition={{ duration: 0.3 }}
              className="relative"
            >
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Search..."
                    className="pl-8 pr-4 py-1.5 border border-gray-300 rounded-full focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    autoFocus
                  />
                   <span className="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400">
                     <i className="fas fa-search"></i>
                   </span>
                </motion.form>
              ) : (
                <motion.button
                  onClick={() => dispatch(toggleSearch())}
                  className="text-gray-600 hover:text-indigo-600 transition-colors"
                  whileHover={{ scale: 1.1 }}
                  whileTap={{ scale: 0.9 }}
                >
                  <i className="fas fa-search text-lg"></i>
                </motion.button>
              )}
              </AnimatePresence>
            </motion.div>

            {/* Cart Icon */}
            <motion.div variants={linkVariants} whileHover={{ scale: 1.1 }} whileTap={{ scale: 0.9 }}>
               <CartLink />
            </motion.div>

            {/* Auth Links */}
            {isAuthenticated ? (
              <motion.div 
                className="relative"
                 onHoverStart={() => setHoverItem('user')} 
                 onHoverEnd={() => setHoverItem(null)}
                 variants={linkVariants}
               >
                <button className="flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">
                  <span className="mr-1">{user?.name || 'Account'}</span>
                  <i className="fas fa-chevron-down text-xs"></i>
                </button>
                <AnimatePresence>
                  {hoverItem === 'user' && (
                    <motion.div 
                      className="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden"
                      variants={dropdownVariants}
                      initial="hidden"
                      animate="visible"
                      exit="hidden"
                    >
                      <div className="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        <Link
                          to="/account"
                          className="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-150"
                          role="menuitem"
                          onClick={() => setHoverItem(null)}
                        >
                          Profile
                  </Link>
                  <button
                    onClick={handleLogout}
                          className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors duration-150"
                          role="menuitem"
                  >
                        Logout
                  </button>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </motion.div>
            ) : (
              <motion.div variants={linkVariants}>
                <Link to="/login" className="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">Login</Link>
              </motion.div>
            )}
          </div>
          
          {/* Mobile Menu Button */} 
          <div className="lg:hidden flex items-center">
             {/* Mobile Search Icon */}
            <motion.button
               onClick={() => dispatch(toggleSearch())}
               className="text-gray-600 hover:text-indigo-600 transition-colors mr-4"
               whileHover={{ scale: 1.1 }}
              whileTap={{ scale: 0.9 }}
            >
               <i className="fas fa-search text-xl"></i>
            </motion.button>
            
             {/* Mobile Cart Icon */}
             <div className="mr-4">
               <CartLink isMobile={true} />
             </div>

            <motion.button
              onClick={() => dispatch(toggleMobileMenu())}
              className="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              aria-expanded={isMobileMenuOpen}
              whileHover={{ scale: 1.1 }}
              whileTap={{ scale: 0.9 }}
            >
              <span className="sr-only">Open main menu</span>
              {isMobileMenuOpen ? (
                <i className="fas fa-times text-xl"></i> 
              ) : (
                <i className="fas fa-bars text-xl"></i> 
              )}
            </motion.button>
        </div>
      </div>
      
        {/* Mobile Search Bar (Appears below header when active) */} 
      <AnimatePresence>
      {isSearchOpen && (
          <motion.div 
               className="lg:hidden pb-3"
               initial={{ height: 0, opacity: 0 }}
               animate={{ height: 'auto', opacity: 1 }}
               exit={{ height: 0, opacity: 0 }}
               transition={{ duration: 0.2 }}
             >
               <form onSubmit={handleSearchSubmit} className="relative">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Search products..."
                   className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                  autoFocus
                />
                 <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                   <i className="fas fa-search"></i>
                 </span>
          </form>
          </motion.div>
      )}
      </AnimatePresence>
      </div>
      
      {/* Mobile Menu */} 
      <AnimatePresence>
      {isMobileMenuOpen && (
          <motion.div 
            className="lg:hidden absolute top-full left-0 w-full bg-white shadow-lg border-t border-gray-200"
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
          >
            <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3">
              <MobileNavLinkItem to="/">HOME</MobileNavLinkItem>
              <MobileNavLinkItem to="/products">PRODUCTS</MobileNavLinkItem>
              {/* Mobile Categories */} 
              {categories.length > 0 && (
                <div className="pt-2">
                  <h3 className="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Categories</h3>
                  {categories.map(category => (
                    <MobileNavLinkItem key={category.id} to={`/products?category=${category.id}`}>{category.name}</MobileNavLinkItem>
                  ))}
                </div>
              )}
              <MobileNavLinkItem to="/about">ABOUT US</MobileNavLinkItem>
              <MobileNavLinkItem to="/contact">CONTACT US</MobileNavLinkItem>
            </div>
            {/* Mobile Auth */} 
            <div className="pt-4 pb-3 border-t border-gray-200">
              {isAuthenticated ? (
                <div className="px-5">
                  <div className="text-base font-medium text-gray-800">{user?.name}</div>
                  <div className="text-sm font-medium text-gray-500 mb-2">{user?.email}</div>
                  <Link
                    to="/account"
                    className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-700 hover:bg-indigo-50 transition-colors mb-1"
                    onClick={() => dispatch(closeMobileMenu())}
                  >
                    Profile
                  </Link>
                  <button
                    onClick={() => { handleLogout(); dispatch(closeMobileMenu()); }}
                    className="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-700 hover:bg-indigo-50 transition-colors"
                  >
                    Logout
                  </button>
              </div>
            ) : (
                <div className="px-5">
                <Link
                  to="/login"
                    className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-700 hover:bg-indigo-50 transition-colors"
                  onClick={() => dispatch(closeMobileMenu())}
                >
                    Login
                </Link>
                </div>
            )}
          </div>
          </motion.div>
      )}
      </AnimatePresence>
    </motion.header>
  );
};

// Helper components for Nav Links (Desktop & Mobile)
const NavLinkItem = ({ to, children }: { to: string, children: React.ReactNode }) => (
  <Link 
    to={to} 
    className="relative text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors duration-200 group"
  >
    {children}
    <motion.span 
      className="absolute bottom-0 left-0 h-0.5 bg-indigo-600 w-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 ease-out origin-left"
    />
  </Link>
);

const MobileNavLinkItem = ({ to, children }: { to: string, children: React.ReactNode }) => {
  const dispatch = useAppDispatch();
  return (
    <Link
      to={to}
      className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-700 hover:bg-indigo-50 transition-colors"
      onClick={() => dispatch(closeMobileMenu())}
    >
      {children}
    </Link>
  );
};

export default Header; 
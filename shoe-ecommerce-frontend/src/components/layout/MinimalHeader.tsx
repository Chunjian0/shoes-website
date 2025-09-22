import { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAppSelector, useAppDispatch } from '../../store';
import { logout } from '../../store/slices/authSlice';
import { clearCart } from '../../store/slices/cartSlice';
import { toggleMobileMenu, closeMobileMenu, toggleSearch } from '../../store/slices/uiSlice';
import { motion, AnimatePresence } from 'framer-motion';
import { ProductCategory } from '../../types/apiTypes';
import CartLink from '../cart/CartLink';
import { FiMenu, FiX, FiSearch, FiShoppingBag, FiUser, FiChevronDown, FiLogOut, FiGrid } from 'react-icons/fi';

// Define the type for categories expected by the header
interface ProductCategoryMinimal {
  id: number | string;
  name: string;
  // Add other properties if needed, e.g., slug
  slug?: string;
}

// Define props for MinimalHeader
interface MinimalHeaderProps {
  categories?: ProductCategoryMinimal[];
}

// 动画变量
const navVariants = {
  hidden: { opacity: 0, y: -10 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.3,
      staggerChildren: 0.05
    }
  }
};

const linkVariants = {
  hidden: { opacity: 0, y: -5 },
  visible: { 
    opacity: 1, 
    y: 0,
    transition: { duration: 0.2 }
  }
};

const dropdownVariants = {
  hidden: { opacity: 0, height: 0 },
  visible: {
    opacity: 1,
    height: 'auto',
    transition: { duration: 0.2 }
  }
};

const mobileMenuVariants = {
  hidden: { 
    x: '-100%',
    opacity: 0 
  },
  visible: { 
    x: 0,
    opacity: 1,
    transition: { 
      type: 'tween',
      duration: 0.3
    }
  }
};

// Apply the props type to the component
const MinimalHeader: React.FC<MinimalHeaderProps> = ({ categories = [] }) => {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const headerRef = useRef<HTMLDivElement>(null);
  const [isScrolled, setIsScrolled] = useState(false);
  const [hoverItem, setHoverItem] = useState<string | null>(null);
  
  // 从Redux获取状态
  const { isAuthenticated, user } = useAppSelector(state => state.auth);
  const { cartCount } = useAppSelector(state => state.cart);
  const { isMobileMenuOpen, isSearchOpen } = useAppSelector(state => state.ui);
  
  // 搜索状态
  const [searchQuery, setSearchQuery] = useState('');
  
  // 监听滚动事件并更新header样式
  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 20) {
        setIsScrolled(true);
      } else {
        setIsScrolled(false);
      }
    };
    
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  
  // 处理搜索提交
  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      dispatch(closeMobileMenu());
      navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
      setSearchQuery('');
      dispatch(toggleSearch());
    }
  };
  
  // 处理登出
  const handleLogout = async () => {
    try {
      dispatch(logout());
      dispatch(clearCart());
      dispatch(closeMobileMenu());
      navigate('/');
    } catch (error) {
      console.error('登出失败:', error);
    }
  };

  // 点击外部关闭搜索栏
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      const searchForm = document.getElementById('search-form');
      if (isSearchOpen && searchForm && !searchForm.contains(event.target as Node)) {
        dispatch(toggleSearch());
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [isSearchOpen, dispatch]);

  return (
    <header
      ref={headerRef}
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        isScrolled 
          ? 'bg-white shadow-sm py-2' 
          : 'bg-white py-4'
      }`}
    >
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between">
          {/* Logo */}
          <Link to="/" className="flex items-center">
            <h1 className="text-xl font-light tracking-wide">
              <span className="font-medium">YCE</span>
              <span className="opacity-60 ml-1">Shoes</span>
            </h1>
          </Link>

          {/* Mobile Menu Toggle */}
          <div className="flex lg:hidden items-center space-x-4">
            <motion.button
              whileHover={{ scale: 1.1, filter: 'brightness(1.1)' }}
              whileTap={{ scale: 0.95 }}
              onClick={() => dispatch(toggleSearch())}
              className="text-gray-700 p-1 rounded-full"
              aria-label="Search"
            >
              <FiSearch size={20} />
            </motion.button>
            
            <CartLink className="relative">
              <motion.div
                whileHover={{ scale: 1.1, filter: 'brightness(1.1)' }}
                whileTap={{ scale: 0.95 }}
                className="text-gray-700 p-1 rounded-full"
              >
                <FiShoppingBag size={20} />
                {cartCount > 0 && (
                  <motion.span 
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    className="absolute -top-1 -right-1 bg-black text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"
                  >
                    {cartCount > 99 ? '99+' : cartCount}
                  </motion.span>
                )}
              </motion.div>
            </CartLink>
            
            <motion.button
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
              onClick={() => dispatch(toggleMobileMenu())}
              className="text-gray-700 p-1 rounded-full"
              aria-label="Toggle menu"
            >
              {isMobileMenuOpen ? <FiX size={24} /> : <FiMenu size={20} />}
            </motion.button>
          </div>

          {/* Desktop Navigation */}
          <motion.nav 
            variants={navVariants}
            initial="hidden"
            animate="visible"
            className="hidden lg:flex items-center space-x-8"
          >
            <motion.div variants={linkVariants}>
              <Link 
                to="/" 
                className="text-gray-700 text-sm hover:text-black py-2 inline-block relative group"
                onMouseEnter={() => setHoverItem('home')}
                onMouseLeave={() => setHoverItem(null)}
              >
                HOME
                <span className={`absolute bottom-0 left-0 w-full h-0.5 bg-black transition-transform duration-300 ease-out origin-left transform scale-x-0 group-hover:scale-x-100`}></span>
              </Link>
            </motion.div>

            <motion.div 
              variants={linkVariants}
              className="relative group"
              onMouseEnter={() => setHoverItem('products')}
              onMouseLeave={() => setHoverItem(null)}
            >
              <div className="flex items-center cursor-pointer text-gray-700 text-sm hover:text-black py-2">
                <span>PRODUCTS</span>
                <FiChevronDown size={16} className="ml-1 transition-transform duration-200 group-hover:rotate-180" />
                <span className={`absolute bottom-0 left-0 w-full h-0.5 bg-black transition-transform duration-300 ease-out origin-left transform scale-x-0 group-hover:scale-x-100`}></span>
              </div>
              
              <AnimatePresence>
                {hoverItem === 'products' && (
                  <motion.div
                    variants={dropdownVariants}
                    initial="hidden"
                    animate="visible"
                    exit="hidden"
                    className="absolute left-0 mt-2 w-48 bg-white shadow-lg border border-gray-100 py-2 z-10"
                  >
                    {categories.map((category) => (
                      <Link 
                        key={category.id}
                        to={`/products?category=${category.id}`} 
                        className="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 hover:text-black transition-colors duration-200"
                      >
                        {category.name}
                      </Link>
                    ))}
                    <div className="border-t border-gray-100 my-1"></div>
                    <Link to="/products?new=true" className="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 hover:text-black transition-colors duration-200">
                      New Arrivals
                    </Link>
                    <Link to="/products?sale=true" className="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 hover:text-black transition-colors duration-200">
                      Sale Items
                    </Link>
                  </motion.div>
                )}
              </AnimatePresence>
            </motion.div>

            <motion.div variants={linkVariants}>
              <Link 
                to="/categories" 
                className="text-gray-700 text-sm hover:text-black py-2 inline-block relative group"
                onMouseEnter={() => setHoverItem('collections')}
                onMouseLeave={() => setHoverItem(null)}
              >
                COLLECTIONS
                <span className={`absolute bottom-0 left-0 w-full h-0.5 bg-black transition-transform duration-300 ease-out origin-left transform scale-x-0 group-hover:scale-x-100`}></span>
              </Link>
            </motion.div>

            <motion.div variants={linkVariants}>
              <Link 
                to="/filter-demo" 
                className="text-gray-700 text-sm hover:text-black py-2 inline-block relative group"
                onMouseEnter={() => setHoverItem('filter-demo')}
                onMouseLeave={() => setHoverItem(null)}
              >
                FILTER DEMO
                <span className={`absolute bottom-0 left-0 w-full h-0.5 bg-black transition-transform duration-300 ease-out origin-left transform scale-x-0 group-hover:scale-x-100`}></span>
              </Link>
            </motion.div>

            <motion.div variants={linkVariants}>
              <Link 
                to="/about" 
                className="text-gray-700 text-sm hover:text-black py-2 inline-block relative group"
                onMouseEnter={() => setHoverItem('about')}
                onMouseLeave={() => setHoverItem(null)}
              >
                ABOUT
                <span className={`absolute bottom-0 left-0 w-full h-0.5 bg-black transition-transform duration-300 ease-out origin-left transform scale-x-0 group-hover:scale-x-100`}></span>
              </Link>
            </motion.div>

            <motion.div variants={linkVariants}>
              <Link 
                to="/contact" 
                className="text-gray-700 text-sm hover:text-black py-2 inline-block relative group"
                onMouseEnter={() => setHoverItem('contact')}
                onMouseLeave={() => setHoverItem(null)}
              >
                CONTACT
                <span className={`absolute bottom-0 left-0 w-full h-0.5 bg-black transition-transform duration-300 ease-out origin-left transform scale-x-0 group-hover:scale-x-100`}></span>
              </Link>
            </motion.div>
          </motion.nav>

          {/* Desktop Action Buttons */}
          <div className="hidden lg:flex items-center space-x-5">
            <motion.button
              whileHover={{ scale: 1.1, filter: 'brightness(1.1)' }}
              whileTap={{ scale: 0.95 }}
              onClick={() => dispatch(toggleSearch())}
              className="text-gray-700 hover:text-black transition-colors p-1"
              aria-label="Search"
            >
              <FiSearch size={18} />
            </motion.button>
            
            <CartLink className="relative">
              <motion.div
                whileHover={{ scale: 1.1, filter: 'brightness(1.1)' }}
                whileTap={{ scale: 0.95 }}
                className="text-gray-700 hover:text-black transition-colors p-1"
              >
                <FiShoppingBag size={18} />
                {cartCount > 0 && (
                  <motion.span 
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    className="absolute -top-1 -right-1 bg-black text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"
                  >
                    {cartCount > 99 ? '99+' : cartCount}
                  </motion.span>
                )}
              </motion.div>
            </CartLink>
            
            {/* User account */}
            {isAuthenticated ? (
              <div 
                className="relative"
                onMouseEnter={() => setHoverItem('user')}
                onMouseLeave={() => setHoverItem(null)}
              >
                <motion.button 
                  whileHover={{ scale: 1.1, filter: 'brightness(1.1)' }}
                  className="flex items-center text-gray-700 hover:text-black p-1"
                >
                  <FiUser size={18} />
                </motion.button>
                <AnimatePresence>
                  {hoverItem === 'user' && (
                    <motion.div 
                      variants={dropdownVariants}
                      initial="hidden"
                      animate="visible"
                      exit="hidden"
                      className="absolute right-0 mt-2 w-48 bg-white shadow-lg border border-gray-100 py-2 z-10"
                    >
                      <div className="px-4 py-2 text-xs font-medium text-gray-900 border-b border-gray-100 mb-1">
                        {user?.name || 'User'}
                      </div>
                      <Link to="/account" className="flex items-center px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 hover:text-black transition-colors duration-200">
                        <FiUser size={14} className="mr-2" />
                        My Account
                      </Link>
                      <Link to="/orders" className="flex items-center px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 hover:text-black transition-colors duration-200">
                        <FiGrid size={14} className="mr-2" />
                        My Orders
                      </Link>
                      <button
                        onClick={handleLogout}
                        className="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 hover:text-black transition-colors duration-200"
                      >
                        <FiLogOut size={14} className="mr-2" />
                        Logout
                      </button>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            ) : (
              <Link to="/login">
                <motion.button
                  whileHover={{ scale: 1.05 }}
                  whileTap={{ scale: 0.95 }}
                  className="text-sm text-gray-700 hover:text-black py-2"
                >
                  LOGIN
                </motion.button>
              </Link>
            )}
          </div>
        </div>
      </div>

      {/* Search Bar */}
      <AnimatePresence>
        {isSearchOpen && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.2 }}
            className="bg-white shadow-sm border-t border-gray-100"
          >
            <div className="container mx-auto px-4 py-4">
              <form id="search-form" onSubmit={handleSearchSubmit} className="relative">
                <input
                  type="text"
                  placeholder="Search for products..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full px-4 py-3 bg-gray-50 border-0 rounded-sm focus:ring-0 focus:outline-none text-sm"
                  autoFocus
                />
                <button
                  type="submit"
                  className="absolute right-0 top-0 bottom-0 px-4 text-gray-500 hover:text-black"
                >
                  <FiSearch size={18} />
                </button>
              </form>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Mobile Menu */}
      <AnimatePresence>
        {isMobileMenuOpen && (
          <motion.div
            variants={mobileMenuVariants}
            initial="hidden"
            animate="visible"
            exit="hidden"
            className="fixed inset-0 bg-white z-40 pt-20 overflow-y-auto lg:hidden"
          >
            <div className="container mx-auto px-4 py-6">
              <ul className="space-y-4">
                <li>
                  <Link 
                    to="/" 
                    className="text-lg hover:text-black block py-2 border-b border-gray-100"
                    onClick={() => dispatch(closeMobileMenu())}
                  >
                    Home
                  </Link>
                </li>
                <li className="py-2 border-b border-gray-100">
                  <div className="flex justify-between items-center text-lg mb-2">
                    Products
                  </div>
                  <ul className="pl-4 space-y-2 mt-2">
                    {categories.map((category) => (
                      <li key={category.id}>
                        <Link 
                          to={`/products?category=${category.id}`}
                          className="text-gray-600 hover:text-black text-base"
                          onClick={() => dispatch(closeMobileMenu())}
                        >
                          {category.name}
                        </Link>
                      </li>
                    ))}
                    <li>
                      <Link 
                        to="/products?new=true"
                        className="text-gray-600 hover:text-black text-base"
                        onClick={() => dispatch(closeMobileMenu())}
                      >
                        New Arrivals
                      </Link>
                    </li>
                    <li>
                      <Link 
                        to="/products?sale=true"
                        className="text-gray-600 hover:text-black text-base"
                        onClick={() => dispatch(closeMobileMenu())}
                      >
                        Sale Items
                      </Link>
                    </li>
                  </ul>
                </li>
                <li>
                  <Link 
                    to="/categories" 
                    className="text-lg hover:text-black block py-2 border-b border-gray-100"
                    onClick={() => dispatch(closeMobileMenu())}
                  >
                    Collections
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/filter-demo" 
                    className="text-lg hover:text-black block py-2 border-b border-gray-100"
                    onClick={() => dispatch(closeMobileMenu())}
                  >
                    Filter Demo
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/about" 
                    className="text-lg hover:text-black block py-2 border-b border-gray-100"
                    onClick={() => dispatch(closeMobileMenu())}
                  >
                    About
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/contact" 
                    className="text-lg hover:text-black block py-2 border-b border-gray-100"
                    onClick={() => dispatch(closeMobileMenu())}
                  >
                    Contact
                  </Link>
                </li>
                
                {isAuthenticated ? (
                  <>
                    <li>
                      <Link 
                        to="/account" 
                        className="text-lg hover:text-black block py-2 border-b border-gray-100"
                        onClick={() => dispatch(closeMobileMenu())}
                      >
                        My Account
                      </Link>
                    </li>
                    <li>
                      <Link 
                        to="/orders" 
                        className="text-lg hover:text-black block py-2 border-b border-gray-100"
                        onClick={() => dispatch(closeMobileMenu())}
                      >
                        My Orders
                      </Link>
                    </li>
                    <li>
                      <button 
                        onClick={handleLogout}
                        className="text-lg hover:text-black block py-2 border-b border-gray-100 w-full text-left"
                      >
                        Logout
                      </button>
                    </li>
                  </>
                ) : (
                  <li>
                    <Link 
                      to="/login" 
                      className="text-lg hover:text-black block py-2 border-b border-gray-100"
                      onClick={() => dispatch(closeMobileMenu())}
                    >
                      Login / Register
                    </Link>
                  </li>
                )}
              </ul>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  );
};

export default MinimalHeader; 
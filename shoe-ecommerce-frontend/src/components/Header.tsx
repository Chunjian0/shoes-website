import { useState, useEffect, Fragment } from 'react';
import { Link, NavLink, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { useAppSelector, useAppDispatch } from '../store';
import { fetchCategories } from '../store/slices/productSlice';
import { Menu, Transition } from '@headlessui/react';
import { logout } from '../store/slices/authSlice';

const Header = () => {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const { isAuthenticated, user, loading: authLoading } = useAppSelector(state => state.auth);
  
  // Get cart items count from Redux using cartCount
  const cartItemsCount = useAppSelector(state => state.cart.cartCount);
  
  // Get categories from Redux
  const { categories } = useAppSelector(state => state.products);

  // Fetch categories when component mounts
  useEffect(() => {
    if (categories.length === 0) {
      dispatch(fetchCategories());
    }
  }, [dispatch, categories.length]);

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };

  const toggleSearch = () => {
    setIsSearchOpen(!isSearchOpen);
    if (!isSearchOpen) {
      setTimeout(() => {
        document.getElementById('search-input')?.focus();
      }, 100);
    }
  };

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/products?search=${encodeURIComponent(searchQuery.trim())}`);
      setIsSearchOpen(false);
      setSearchQuery('');
    }
  };

  const handleLogout = async () => {
    dispatch(logout());
    navigate('/');
  };

  return (
    <header className="bg-white shadow-sm">
      {/* Top bar */}
      <div className="bg-blue-900 text-white py-2">
        <div className="container mx-auto px-4 flex justify-between items-center text-sm">
          <div className="flex items-center space-x-4">
            <a href="tel:+1234567890" className="hover:text-blue-200 transition-colors">
              <span className="mr-1">&#9742;</span>
              <span className="hidden sm:inline">Call us:</span> +1 (234) 567-890
            </a>
            <a href="mailto:support@shoeshop.com" className="hover:text-blue-200 transition-colors">
              <span className="mr-1">&#9993;</span>
              <span className="hidden sm:inline">Email:</span> support@shoeshop.com
            </a>
          </div>
          <div className="flex items-center space-x-4">
            <a href="#" className="hover:text-blue-200 transition-colors">
              <span className="mr-1">&#9432;</span>
              <span className="hidden sm:inline">Help</span>
            </a>
            <a href="#" className="hover:text-blue-200 transition-colors">
              <span className="mr-1">&#9993;</span>
              <span className="hidden sm:inline">Newsletter</span>
            </a>
          </div>
        </div>
      </div>
      
      {/* Main header */}
      <div className="container mx-auto px-4 py-4">
        <div className="flex items-center justify-between">
          {/* Logo */}
          <div className="flex-shrink-0">
            <Link to="/" className="flex items-center">
              <div className="h-10 w-10 bg-blue-900 text-white rounded-full flex items-center justify-center font-bold">
                S
              </div>
              <span className="ml-2 text-xl font-bold text-gray-900">ShoeShop</span>
            </Link>
          </div>
          
          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            <NavLink 
              to="/" 
              className={({ isActive }) => 
                isActive ? "text-blue-600 font-medium" : "text-gray-700 hover:text-blue-600 transition-colors"
              }
              end
            >
              Home
            </NavLink>
            <NavLink 
              to="/products" 
              className={({ isActive }) => 
                isActive ? "text-blue-600 font-medium" : "text-gray-700 hover:text-blue-600 transition-colors"
              }
            >
              Shop
            </NavLink>
            <NavLink 
              to="/sale" 
              className={({ isActive }) => 
                isActive ? "text-blue-600 font-medium" : "text-gray-700 hover:text-blue-600 transition-colors"
              }
            >
              Sale
            </NavLink>
            <NavLink 
              to="/about" 
              className={({ isActive }) => 
                isActive ? "text-blue-600 font-medium" : "text-gray-700 hover:text-blue-600 transition-colors"
              }
            >
              About
            </NavLink>
            <NavLink 
              to="/contact" 
              className={({ isActive }) => 
                isActive ? "text-blue-600 font-medium" : "text-gray-700 hover:text-blue-600 transition-colors"
              }
            >
              Contact
            </NavLink>
            {/* 开发工具链接 - 仅在开发环境显示 */}
            {process.env.NODE_ENV === 'development' && (
              <a href="/dev-tools" className="text-blue-600 hover:text-blue-800 flex items-center">
                <span>Dev Tools</span>
                <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </a>
            )}
          </nav>
          
          {/* Header Actions */}
          <div className="flex items-center space-x-4">
            {/* Search Button */}
            <button 
              onClick={toggleSearch}
              className="text-gray-700 hover:text-blue-600 transition-colors p-2"
              aria-label="Search"
            >
              <span className="text-xl">&#128269;</span>
            </button>
            
            {/* User Menu */}
            <div className="relative">
              <Menu as="div" className="relative inline-block text-left">
                <Menu.Button className="text-gray-700 hover:text-blue-600 transition-colors p-2">
                  <span className="text-xl">&#128100;</span>
                </Menu.Button>
                <Transition
                  as={Fragment}
                  enter="transition ease-out duration-100"
                  enterFrom="transform opacity-0 scale-95"
                  enterTo="transform opacity-100 scale-100"
                  leave="transition ease-in duration-75"
                  leaveFrom="transform opacity-100 scale-100"
                  leaveTo="transform opacity-0 scale-95"
                >
                  <Menu.Items className="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                    <div className="px-1 py-1">
                      {isAuthenticated ? (
                        <>
                          <Menu.Item>
                            {({ active }) => (
                              <Link
                                to="/account"
                                className={`${
                                  active ? 'bg-blue-50 text-blue-600' : 'text-gray-700'
                                } group flex w-full items-center rounded-md px-2 py-2 text-sm`}
                              >
                                <span className="mr-2">&#128100;</span>
                                My Account
                              </Link>
                            )}
                          </Menu.Item>
                          <Menu.Item>
                            {({ active }) => (
                              <Link
                                to="/orders"
                                className={`${
                                  active ? 'bg-blue-50 text-blue-600' : 'text-gray-700'
                                } group flex w-full items-center rounded-md px-2 py-2 text-sm`}
                              >
                                <span className="mr-2">&#128230;</span>
                                My Orders
                              </Link>
                            )}
                          </Menu.Item>
                          <Menu.Item>
                            {({ active }) => (
                              <button
                                onClick={handleLogout}
                                disabled={authLoading}
                                className={`${
                                  active ? 'bg-blue-50 text-blue-600' : 'text-gray-700'
                                } group flex w-full items-center rounded-md px-2 py-2 text-sm disabled:opacity-50`}
                              >
                                <span className="mr-2">&#128682;</span>
                                {authLoading ? 'Logging out...' : 'Logout'}
                              </button>
                            )}
                          </Menu.Item>
                        </>
                      ) : (
                        <>
                          <Menu.Item>
                            {({ active }) => (
                              <Link
                                to="/login"
                                className={`${
                                  active ? 'bg-blue-50 text-blue-600' : 'text-gray-700'
                                } group flex w-full items-center rounded-md px-2 py-2 text-sm`}
                              >
                                <span className="mr-2">&#128275;</span>
                                Login
                              </Link>
                            )}
                          </Menu.Item>
                          <Menu.Item>
                            {({ active }) => (
                              <Link
                                to="/register"
                                className={`${
                                  active ? 'bg-blue-50 text-blue-600' : 'text-gray-700'
                                } group flex w-full items-center rounded-md px-2 py-2 text-sm`}
                              >
                                <span className="mr-2">&#9998;</span>
                                Register
                              </Link>
                            )}
                          </Menu.Item>
                        </>
                      )}
                    </div>
                  </Menu.Items>
                </Transition>
              </Menu>
            </div>
            
            {/* Cart */}
            <Link 
              to="/cart" 
              className="text-gray-700 hover:text-blue-600 transition-colors p-2 relative"
              aria-label="Cart"
            >
              <span className="text-xl">&#128722;</span>
              {cartItemsCount > 0 && (
                <span className="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                  {cartItemsCount}
                </span>
              )}
            </Link>
            
            {/* Mobile menu button */}
            <button 
              onClick={toggleMenu}
              className="md:hidden text-gray-700 hover:text-blue-600 transition-colors p-2"
              aria-label="Menu"
            >
              <span className="text-xl">{isMenuOpen ? '✕' : '☰'}</span>
            </button>
          </div>
        </div>
        
        {/* Mobile Navigation */}
        {isMenuOpen && (
          <nav className="md:hidden mt-4 py-4 border-t border-gray-200">
            <ul className="space-y-4">
              <li>
                <NavLink 
                  to="/" 
                  className={({ isActive }) => 
                    isActive 
                      ? "block text-blue-600 font-medium" 
                      : "block text-gray-700 hover:text-blue-600 transition-colors"
                  }
                  onClick={() => setIsMenuOpen(false)}
                >
                  <i className="fas fa-home mr-2"></i> Home
                </NavLink>
              </li>
              <li>
                <button
                  className="flex items-center justify-between w-full text-left text-gray-700 hover:text-blue-600 transition-colors"
                  onClick={() => {
                    navigate('/products');
                    setIsMenuOpen(false);
                  }}
                >
                  <span><i className="fas fa-th-large mr-2"></i> Categories</span>
                  <i className="fas fa-chevron-right"></i>
                </button>
              </li>
              <li>
                <NavLink 
                  to="/products" 
                  className={({ isActive }) => 
                    isActive 
                      ? "block text-blue-600 font-medium" 
                      : "block text-gray-700 hover:text-blue-600 transition-colors"
                  }
                  onClick={() => setIsMenuOpen(false)}
                >
                  <i className="fas fa-shopping-bag mr-2"></i> All Products
                </NavLink>
              </li>
              <li>
                <NavLink 
                  to="/sale" 
                  className={({ isActive }) => 
                    isActive 
                      ? "block text-blue-600 font-medium" 
                      : "block text-gray-700 hover:text-blue-600 transition-colors"
                  }
                  onClick={() => setIsMenuOpen(false)}
                >
                  <i className="fas fa-tag mr-2"></i> Sale
                </NavLink>
              </li>
              <li>
                <NavLink 
                  to="/contact" 
                  className={({ isActive }) => 
                    isActive 
                      ? "block text-blue-600 font-medium" 
                      : "block text-gray-700 hover:text-blue-600 transition-colors"
                  }
                  onClick={() => setIsMenuOpen(false)}
                >
                  <i className="fas fa-envelope mr-2"></i> Contact
                </NavLink>
              </li>
            </ul>
          </nav>
        )}
      </div>
      
      {/* Search Overlay */}
      {isSearchOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-start justify-center pt-20">
          <div className="bg-white w-full max-w-2xl mx-4 rounded-lg shadow-xl overflow-hidden">
            <form onSubmit={handleSearchSubmit} className="relative">
              <input
                id="search-input"
                type="text"
                placeholder="Search for products..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full px-4 py-4 pr-12 text-lg focus:outline-none"
              />
              <button
                type="submit"
                className="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600 transition-colors"
              >
                <i className="fas fa-search text-lg"></i>
              </button>
            </form>
            <div className="flex justify-between p-4 bg-gray-50">
              <p className="text-sm text-gray-500">Press ESC to close</p>
              <button
                onClick={toggleSearch}
                className="text-sm text-blue-600 hover:text-blue-800 transition-colors"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </header>
  );
};

export default Header; 
import React, { useState, useEffect, useRef, ReactNode, Fragment } from 'react';
import { TemplateProps } from './TemplateProps';
import AnimatedElement from '../animations/AnimatedElement';
import type { AnimationType } from '../animations/AnimatedElement';
import ProductsSection from '../products/ProductsSection';
import TemplateDescription from '../templates/TemplateDescription';
import { ProductTemplate } from '../../types/apiTypes';
import { Link, useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import LoadingIndicator from '../LoadingIndicator';
import ErrorDisplay from '../ErrorDisplay';
import TemplateCard from './TemplateCard';
import EnhancedTemplateCard from '../enhanced/EnhancedTemplateCard';
import { Menu, Transition } from '@headlessui/react';
import { useAppSelector, useAppDispatch } from '../../store';
import { logout } from '../../store/slices/authSlice';

/**
 * Find the minimum price from product template
 * @param template Product template
 * @returns Formatted minimum price, or null if no price available
 */
const findMinPrice = (template: ProductTemplate): string | null => {
  if (!template.related_products || template.related_products.length === 0) {
    return null;
  }

  const validPrices = template.related_products
    .filter(product => product.price && typeof product.price === 'number' && product.price > 0)
    .map(product => product.price as number);

  if (validPrices.length === 0) {
    return null;
  }

  return Math.min(...validPrices).toFixed(2);
};

/**
 * Minimal Style Template
 * Features: Whitespace, minimalist design, elegant typography
 */
const MinimalTemplate: React.FC<TemplateProps> = ({
  settings,
  featuredProducts = [],
  newProducts = [],
  saleProducts = [],
  categories = [],
  isLoading = {},
  errors = {},
  templates
}) => {
  const navigate = useNavigate();
  // State management
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobile, setIsMobile] = useState(false);
  const [autoplayInterval, setAutoplayInterval] = useState<NodeJS.Timeout | null>(null);
  const [scrollY, setScrollY] = useState(0);
  const [hoverItem, setHoverItem] = useState<string | null>(null);
  const carouselRef = useRef<HTMLDivElement>(null);

  // Watch for device size
  useEffect(() => {
    const checkDeviceSize = () => {
      setIsMobile(window.innerWidth < 768);
    };

    checkDeviceSize();
    window.addEventListener('resize', checkDeviceSize);

    return () => window.removeEventListener('resize', checkDeviceSize);
  }, []);

  // Watch for scroll events
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
      setScrollY(window.scrollY);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Get carousel settings
  const banners = settings.banners || [];
  const carouselSettings = settings.carousel || {};
  const autoplay = carouselSettings.autoplay !== false;
  const delay = carouselSettings.delay || 5000;
  const showNavigation = carouselSettings.showNavigation !== false;
  const showIndicators = carouselSettings.showIndicators !== false;

  // Set up autoplay for carousel
  useEffect(() => {
    if (autoplay && banners.length > 1) {
      const interval = setInterval(() => {
        setCurrentSlide((prev) => (prev + 1) % banners.length);
      }, delay);

      setAutoplayInterval(interval);
      return () => {
        if (autoplayInterval) clearInterval(autoplayInterval);
      };
    }
  }, [autoplay, banners.length, delay]);

  // Navigation dropdown variants
  const dropdownVariants = {
    hidden: { opacity: 0, y: -5, height: 0 },
    visible: {
      opacity: 1,
      y: 0,
      height: 'auto',
      transition: { duration: 0.3 }
    }
  };

  // Get device-appropriate animation type
  const getDeviceAnimation = (type: string): AnimationType => {
    if (isMobile) {
      return 'fade-in'; // Simpler animations for mobile devices
    }

    switch (type) {
      case 'slideUp': return 'slide-up';
      case 'slideDown': return 'slide-down';
      case 'fadeIn': return 'fade-in';
      case 'zoomIn': return 'zoom-in';
      default: return 'fade-in';
    }
  };

  // Get parallax effect style
  const getParallaxStyle = (depth: number = 0.1) => {
    return {
      transform: `translateY(${scrollY * depth}px)`,
      transition: 'transform 0.1s ease-out',
    };
  };

  // Render carousel section
  const renderCarousel = () => {
    // Use high-quality online images (not local images)
    const demoCarouselItems = [
      {
        id: 1,
        title: "Minimalist Design",
        subtitle: "Experience the perfect balance of style and comfort from our curated collection",
        buttonText: "Explore Collection",
        buttonLink: "/collections",
        imageUrl: "https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80"
      },
      {
        id: 2,
        title: "Summer Selection",
        subtitle: "Lightweight, breathable designs for the hot season",
        buttonText: "Shop Now",
        buttonLink: "/collections/summer",
        imageUrl: "https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80"
      },
      {
        id: 3,
        title: "Limited Edition",
        subtitle: "Uniquely crafted designs for those seeking distinct style",
        buttonText: "View Collection",
        buttonLink: "/collections/limited",
        imageUrl: "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80"
      }
    ];

    // Use banner settings or demo items if not available
    const carouselItems = banners.length > 0 ? banners : demoCarouselItems;

    // Go to specific slide
    const goToSlide = (index: number) => {
      setCurrentSlide(index);
    };

    // Touch swipe functionality
    const touchStartX = useRef<number>(0);
    const touchEndX = useRef<number>(0);

    const handleTouchStart = (e: React.TouchEvent) => {
      touchStartX.current = e.touches[0].clientX;

      const handleTouchMove = (e: TouchEvent) => {
        touchEndX.current = e.touches[0].clientX;
      };

      const handleTouchEnd = () => {
        const diff = touchStartX.current - touchEndX.current;

        if (diff > 50) {
          goToSlide((currentSlide + 1) % carouselItems.length);
        } else if (diff < -50) {
          goToSlide((currentSlide - 1 + carouselItems.length) % carouselItems.length);
        }

        document.removeEventListener('touchmove', handleTouchMove);
        document.removeEventListener('touchend', handleTouchEnd);
      };

      document.addEventListener('touchmove', handleTouchMove);
      document.addEventListener('touchend', handleTouchEnd);
    };

    // Mouse drag functionality
    const mouseStartX = useRef<number>(0);
    const mouseEndX = useRef<number>(0);
    const isDragging = useRef<boolean>(false);

    const handleMouseDown = (e: React.MouseEvent) => {
      isDragging.current = true;
      mouseStartX.current = e.clientX;

      const handleMouseMove = (e: MouseEvent) => {
        if (!isDragging.current) return;
        mouseEndX.current = e.clientX;
      };

      const handleMouseUp = (e: MouseEvent) => {
        if (!isDragging.current) return;

        isDragging.current = false;
        const diff = mouseStartX.current - mouseEndX.current;

        if (diff > 50) {
          goToSlide((currentSlide + 1) % carouselItems.length);
        } else if (diff < -50) {
          goToSlide((currentSlide - 1 + carouselItems.length) % carouselItems.length);
        }

        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
      };

      document.addEventListener('mousemove', handleMouseMove);
      document.addEventListener('mouseup', handleMouseUp);
    };

    return (
      <div
        ref={carouselRef}
        className="relative h-screen overflow-hidden"
        onTouchStart={handleTouchStart}
        onMouseDown={handleMouseDown}
      >
        {/* Carousel items */}
        {carouselItems.map((item, idx) => (
          <div
            key={item.id || idx}
            className={`absolute inset-0 bg-cover bg-center transition-opacity duration-1000 ${idx === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0'}`}
            style={{ backgroundImage: `url(${item.imageUrl})` }}
          >
            {/* Overlay */}
            <div className="absolute inset-0 bg-black opacity-30"></div>

            {/* Content */}
            <div className="container h-full mx-auto px-6 flex flex-col justify-center relative z-10">
              <div className="max-w-xl px-4">
                <h2 className="text-white text-sm md:text-base font-light tracking-[0.3em] mb-4">YCE SHOES</h2>
                <h1 className="text-white text-4xl md:text-6xl font-light mb-6">{item.title}</h1>
                <p className="text-white/80 text-lg mb-8 max-w-md font-light">{item.subtitle}</p>
                <a
                  href={item.buttonLink}
                  className="inline-block px-8 py-3 border border-white text-white font-light tracking-wider hover:bg-white hover:text-black transition-all duration-300 text-sm"
                >
                  {item.buttonText}
                </a>
              </div>

              {/* Bottom indicators and controls */}
              <div className="absolute bottom-10 left-0 right-0 flex justify-between items-center px-6 md:px-10">
                <div>
                  <span className="text-white/80 text-sm font-light">
                    {currentSlide + 1} / {carouselItems.length}
                  </span>
                </div>

                <div className="flex items-center space-x-4">
                  {showIndicators && (
                    <div className="flex space-x-2">
                      {carouselItems.map((_, idx) => (
                        <button
                          key={idx}
                          onClick={() => goToSlide(idx)}
                          className={`w-2 h-2 rounded-full transition-all duration-300 ${idx === currentSlide ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/60'}`}
                          aria-label={`Go to slide ${idx + 1}`}
                        ></button>
                      ))}
                    </div>
                  )}
                </div>

                <div className="hidden md:flex space-x-3">
                  <button
                    onClick={() => goToSlide((currentSlide - 1 + carouselItems.length) % carouselItems.length)}
                    className="w-10 h-10 rounded-full flex items-center justify-center border border-white/30 text-white/80 hover:bg-white/10 hover:border-white transition-all duration-300"
                    aria-label="Previous slide"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 19l-7-7 7-7" />
                    </svg>
                  </button>
                  <button
                    onClick={() => goToSlide((currentSlide + 1) % carouselItems.length)}
                    className="w-10 h-10 rounded-full flex items-center justify-center border border-white/30 text-white/80 hover:bg-white/10 hover:border-white transition-all duration-300"
                    aria-label="Next slide"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        ))}

        {/* Navigation buttons - only shown on larger screens and on hover */}
        {showNavigation && carouselItems.length > 1 && (
          <>
            <button
              onClick={() => goToSlide((currentSlide - 1 + carouselItems.length) % carouselItems.length)}
              className="md:hidden absolute left-4 top-1/2 transform -translate-y-1/2 z-20 text-white opacity-0 hover:opacity-100 focus:opacity-100 transition-all duration-300 hover:scale-110 w-12 h-12 rounded-full backdrop-blur-sm bg-black/10 flex items-center justify-center"
              aria-label="Previous slide"
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              onClick={() => goToSlide((currentSlide + 1) % carouselItems.length)}
              className="md:hidden absolute right-4 top-1/2 transform -translate-y-1/2 z-20 text-white opacity-0 hover:opacity-100 focus:opacity-100 transition-all duration-300 hover:scale-110 w-12 h-12 rounded-full backdrop-blur-sm bg-black/10 flex items-center justify-center"
              aria-label="Next slide"
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </>
        )}

        {/* Scroll indicator - more elegant animation design */}
        <div className="absolute bottom-28 left-1/2 transform -translate-x-1/2 z-20 text-white flex flex-col items-center">
          <div className="w-6 h-10 border border-white/50 rounded-full flex items-center justify-center p-1">
            <div className="w-1 h-1 bg-white rounded-full animate-scrollDown"></div>
          </div>
          <span className="text-xs tracking-wider mt-2 opacity-70 font-light">SCROLL</span>
        </div>
      </div>
    );
  };

  // Render header section
  const renderHeader = () => {
    // Get auth state inside renderHeader or pass from component props if needed
    const { isAuthenticated, user, loading: authLoading } = useAppSelector(state => state.auth);
    const dispatch = useAppDispatch(); // Get dispatch

    const handleLogout = () => {
      dispatch(logout());
      navigate('/'); // Navigate to home after logout
    };

    // Get cart count
    const cartItemsCount = useAppSelector(state => state.cart.cartCount);

    return (
      <header className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${isScrolled ? 'bg-white shadow-md py-2' : 'bg-transparent py-4'}`}>
        <div className="container mx-auto px-6">
          <div className="flex justify-between items-center">
            <Link to="/" className="text-2xl font-light tracking-widest">
              <span className={isScrolled ? 'text-blue-900' : 'text-white'}>YCE</span>
              <span className={isScrolled ? 'text-gray-900' : 'text-white'}> SHOES</span>
            </Link>

            {/* Desktop Navigation */}
            <nav className="hidden md:flex items-center space-x-8">
              {/* Products Dropdown */}
              <div
                className="relative group"
                onMouseEnter={() => setHoverItem('products')}
                onMouseLeave={() => setHoverItem(null)}
              >
                <Link
                  to="/products"
                  className={`${isScrolled ? 'text-gray-800 hover:text-blue-800' : 'text-white hover:text-gray-200'} 
                    font-light tracking-wider transition-colors duration-200 flex items-center`}
                >
                  PRODUCTS
                  <svg
                    className={`ml-1 h-4 w-4 transition-transform ${hoverItem === 'products' ? 'rotate-180' : ''}`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </Link>

                <AnimatePresence>
                  {hoverItem === 'products' && (
                    <motion.div
                      initial="hidden"
                      animate="visible"
                      exit="hidden"
                      variants={dropdownVariants}
                      className="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                    >
                      {categories.map((category, idx) => {
                        const categoryName = typeof category === 'object' && category.name ? category.name : String(category);
                        const categoryId = typeof category === 'object' && category.id ? category.id : null;
                        // Use category ID if available for more robust linking
                        const categoryQuery = categoryId ? `category=${categoryId}` : `category_name=${encodeURIComponent(categoryName)}`;
                        return (
                          <Link
                            key={idx}
                            to={`/products?${categoryQuery}`}
                            className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-800"
                          >
                            {categoryName} Shoes
                          </Link>
                        );
                      })}
                      <div className="border-t border-gray-100 my-1"></div>
                      <Link
                        to="/products"
                        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-800 font-medium"
                      >
                        View All Products
                      </Link>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>

              {/* About Us */}
              <Link
                to="/about"
                className={`${isScrolled ? 'text-gray-800 hover:text-blue-800' : 'text-white hover:text-gray-200'} 
                  font-light tracking-wider transition-colors duration-200`}
              >
                ABOUT US
              </Link>

              {/* Contact */}
              <Link
                to="/contact"
                className={`${isScrolled ? 'text-gray-800 hover:text-blue-800' : 'text-white hover:text-gray-200'} 
                  font-light tracking-wider transition-colors duration-200`}
              >
                CONTACT US
              </Link>
            </nav>

            <div className="flex items-center space-x-4">
              {/* Cart Button */}
              <Link
                to="/cart"
                className={`${isScrolled ? 'text-gray-800' : 'text-white'} hover:opacity-80 transition-opacity duration-200 relative p-2`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                {/* Use cartItemsCount from Redux */}
                {cartItemsCount > 0 && (
                  <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">
                    {cartItemsCount}
                  </span>
                )}
              </Link>

              {/* NEW User Menu */}
              <Menu as="div" className="relative inline-block text-left">
                <Menu.Button className={`${isScrolled ? 'text-gray-800' : 'text-white'} hover:opacity-80 transition-opacity duration-200 p-2`}>
                  {/* Original User Icon SVG */}
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
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
                  <Menu.Items className="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                    <div className="px-1 py-1 ">
                      {isAuthenticated ? (
                        <>
                          <div className="px-3 py-2">
                            <p className="text-sm font-medium text-gray-900 truncate">
                              {user?.name || 'User'}
                            </p>
                            <p className="text-sm text-gray-500 truncate">
                              {user?.email || ''}
                            </p>
                          </div>
                          <div className="border-t border-gray-100"></div>
                          <Menu.Item>
                            {({ active }) => (
                              <Link
                                to="/account"
                                className={`${active ? 'bg-gray-100 text-gray-900' : 'text-gray-700'} group flex rounded-md items-center w-full px-2 py-2 text-sm`}
                              >
                                My Account
                              </Link>
                            )}
                          </Menu.Item>
                          <Menu.Item>
                            {({ active }) => (
                              <Link
                                to="/orders"
                                className={`${active ? 'bg-gray-100 text-gray-900' : 'text-gray-700'} group flex rounded-md items-center w-full px-2 py-2 text-sm`}
                              >
                                My Orders
                              </Link>
                            )}
                          </Menu.Item>
                        </>
                      ) : (
                        <Menu.Item>
                          {({ active }) => (
                            <Link
                              to="/login"
                              className={`${active ? 'bg-gray-100 text-gray-900' : 'text-gray-700'} group flex rounded-md items-center w-full px-2 py-2 text-sm`}
                            >
                              Login
                            </Link>
                          )}
                        </Menu.Item>
                      )}
                    </div>
                    {isAuthenticated && (
                      <div className="px-1 py-1">
                        <Menu.Item>
                          {({ active }) => (
                            <button
                              onClick={handleLogout}
                              disabled={authLoading}
                              className={`${active ? 'bg-gray-100 text-gray-900' : 'text-gray-700'} group flex rounded-md items-center w-full px-2 py-2 text-sm disabled:opacity-50`}
                            >
                              {authLoading ? 'Logging out...' : 'Logout'}
                            </button>
                          )}
                        </Menu.Item>
                      </div>
                    )}
                    {!isAuthenticated && (
                      <div className="px-1 py-1 border-t border-gray-100">
                        <Menu.Item>
                          {({ active }) => (
                            <Link
                              to="/register"
                              className={`${active ? 'bg-gray-100 text-gray-900' : 'text-gray-700'} group flex rounded-md items-center w-full px-2 py-2 text-sm`}
                            >
                              Register
                            </Link>
                          )}
                        </Menu.Item>
                      </div>
                    )}
                  </Menu.Items>
                </Transition>
              </Menu>

              {/* Mobile Menu Button */}
              <button className="md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" className={`h-5 w-5 ${isScrolled ? 'text-gray-800' : 'text-white'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </header>
    );
  };

  // Render hero section (for cases without carousel)
  const renderHeroSection = () => {
    // Don't display this section if carousel exists
    if (banners && banners.length > 0) {
      return null;
    }

    // Default banner settings
    const defaultBannerSettings = {
      title: 'Minimal Elegance For Modern Style',
      subtitle: 'Discover our collection of premium footwear designed for the modern aesthetic.',
      buttonText: 'Shop Collection',
      buttonLink: '/collections',
      imageUrl: 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80'
    };

    // Use first banner or default settings
    const bannerSettings = banners && banners.length > 0 ? banners[0] : defaultBannerSettings;

    return (
      <section className="relative h-screen flex items-center bg-gray-900">
        {/* Background image */}
        <div
          className="absolute inset-0 bg-cover bg-center"
          style={{
            backgroundImage: `url(${bannerSettings.imageUrl})`,
            filter: 'grayscale(15%) brightness(0.7)'
          }}
        ></div>

        {/* Content */}
        <div className="container mx-auto px-6 relative z-10">
          <div className="max-w-xl">
            <AnimatedElement type="fade-in" options={{ delay: 0.2 }}>
              <h2 className="text-sm md:text-base tracking-[0.3em] text-gray-300 uppercase mb-4 font-light">
                {settings.site_name || 'YCE Shoes'}
              </h2>
            </AnimatedElement>

            <AnimatedElement type="slide-up" options={{ delay: 0.4 }}>
              <h1 className="text-4xl md:text-6xl text-white font-light leading-tight mb-6">
                {bannerSettings.title}
              </h1>
            </AnimatedElement>

            <AnimatedElement type="fade-in" options={{ delay: 0.6 }}>
              <p className="text-gray-300 text-lg md:text-xl font-light mb-8 max-w-md">
                {bannerSettings.subtitle}
              </p>
            </AnimatedElement>

            <AnimatedElement type="slide-up" options={{ delay: 0.8 }}>
              <a
                href={bannerSettings.buttonLink}
                className="inline-block px-8 py-3 border border-white text-white font-light tracking-wider hover:bg-white hover:text-black transition-all duration-300 text-sm"
              >
                {bannerSettings.buttonText}
              </a>
            </AnimatedElement>
          </div>
        </div>
      </section>
    );
  };

  // 渲染品牌理念区域
  const renderBrandEthos = () => {
    return (
      <section className="py-24 bg-white">
        <div className="container mx-auto px-6">
          <div className="max-w-3xl mx-auto text-center">
            <AnimatedElement type="slide-up">
              <h2 className="text-3xl font-light text-gray-900 mb-8">Minimalist Design. Maximum Comfort.</h2>
            </AnimatedElement>

            <AnimatedElement type="fade-in" options={{ delay: 0.2 }}>
              <p className="text-gray-600 leading-relaxed mb-12">
                At YCE Shoes, we believe that true style comes from perfect balance. Our footwear embodies minimalist elegance without compromising on comfort and durability. Each pair is thoughtfully designed and meticulously crafted for those who appreciate subtle sophistication.
              </p>
            </AnimatedElement>

            <div className="h-px w-16 bg-gray-300 mx-auto"></div>
          </div>
        </div>
      </section>
    );
  };

  // 渲染分类区域
  const renderCategories = () => {
    if (!categories || categories.length === 0) {
      return null;
    }

    return (
      <section className="py-24 bg-gray-50">
        <div className="container mx-auto px-6">
          <AnimatedElement type="slide-up">
            <div className="text-center mb-16">
              <h2 className="text-3xl font-light text-gray-900">Curated Collections</h2>
              <div className="h-px w-12 bg-gray-500 mx-auto mt-6"></div>
            </div>
          </AnimatedElement>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {categories.slice(0, 6).map((category, index) => {
              // Safely handle category whether it's a string or object
              const categoryId = typeof category === 'object' && category.id ? category.id : index;
              const categoryName = typeof category === 'object' && category.name ? category.name : String(category);
              const categorySlug = typeof category === 'object' && category.slug ? category.slug : 'all';
              const categoryImage = typeof category === 'object' && category.image ? category.image : null;
              const productCount = typeof category === 'object' && category.product_count !== undefined ? category.product_count : null;

              return (
                <AnimatedElement
                  key={categoryId}
                  type="fade-in"
                  options={{ delay: 0.1 + (index * 0.05) }}
                >
                  <a
                    href={`/categories/${categorySlug}`}
                    className="block group relative aspect-w-3 aspect-h-4 overflow-hidden"
                  >
                    {/* Background image */}
                    <div className="absolute inset-0 bg-gray-200">
                      {categoryImage ? (
                        <img
                          src={categoryImage}
                          alt={categoryName}
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                        />
                      ) : (
                        <div className="w-full h-full bg-gradient-to-br from-gray-100 to-gray-300"></div>
                      )}
                    </div>

                    {/* Hover overlay */}
                    <div className="absolute inset-0 bg-black opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>

                    {/* Content */}
                    <div className="absolute inset-0 flex flex-col justify-end p-8">
                      <div className="transform translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300 bg-white/90 backdrop-blur-sm p-4">
                        <h3 className="text-lg font-medium text-gray-900">
                          {categoryName}
                        </h3>
                        {productCount !== null && (
                          <p className="text-gray-600 text-sm mt-1">{productCount} products</p>
                        )}
                      </div>
                    </div>
                  </a>
                </AnimatedElement>
              );
            })}
          </div>

          {categories.length > 6 && (
            <div className="text-center mt-12">
              <a href="/categories" className="inline-block border-b-2 border-gray-900 text-gray-900 font-light tracking-wider hover:text-gray-600 hover:border-gray-600 transition-colors duration-300">
                View All Categories
              </a>
            </div>
          )}
        </div>
      </section>
    );
  };

  // Render products section
  const renderProducts = (): ReactNode => {
    // 渲染特色产品部分
    const renderFeaturedSection = (): ReactNode => {
      // Check if this section should be shown based on settings
      if (!settings?.featuredProducts?.title) return null; // Only render if title exists

      const sectionSettings = settings.featuredProducts;
      const sectionTitle = sectionSettings.title || 'Featured Products';
      const sectionSubtitle = sectionSettings.subtitle || 'Our handpicked selection';
      const buttonText = sectionSettings.buttonText;
      const buttonLink = sectionSettings.buttonLink;

      return (
        <AnimatedElement type={getDeviceAnimation('slideUp')} className="py-20 bg-gradient-to-br from-white to-indigo-50 relative overflow-hidden">
          {/* Decorative element */}
          <div className="absolute top-0 left-0 w-32 h-32 bg-indigo-100/50 rounded-full -translate-x-16 -translate-y-16 blur-2xl pointer-events-none"></div>
          <div className="container mx-auto px-4 relative">
            <div className="text-center mb-14">
              <motion.div
                initial={{ opacity: 0, scale: 0.9 }}
                whileInView={{ opacity: 1, scale: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5 }}
              >
                <span className="inline-block px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full mb-3">
                  FEATURED
                </span>
              </motion.div>
              <motion.h2
                className="text-3xl md:text-4xl font-bold text-gray-900 mb-4"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6 }}
              >
                {sectionTitle}
              </motion.h2>
              <motion.p
                className="text-gray-600 max-w-2xl mx-auto"
                initial={{ opacity: 0 }}
                whileInView={{ opacity: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6, delay: 0.2 }}
              >
                {sectionSubtitle}
              </motion.p>
            </div>

            {isLoading.featuredProducts ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="animate-pulse">
                    <div className="bg-gray-200 h-64 rounded-lg mb-4"></div>
                    <div className="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
                    <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                  </div>
                ))}
              </div>
            ) : (
              <>
                {templates?.featured && templates.featured.length > 0 ? (
                  <motion.div
                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, margin: "-100px" }}
                    transition={{ staggerChildren: 0.1 }}
                  >
                    {(templates?.featured || []).map((template, index) => (
                      <EnhancedTemplateCard
                        key={template.id}
                        template={template}
                        index={index}
                        highlightClass="border-blue-100 hover:border-blue-200"
                        mobileView={isMobile}
                      />
                    ))}
                  </motion.div>
                ) : featuredProducts && featuredProducts.length > 0 ? (
                  <motion.div
                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, margin: "-100px" }}
                    transition={{ staggerChildren: 0.1 }}
                  >
                    {featuredProducts.map((product, index) => (
                      <motion.div
                        key={product.id}
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{
                          delay: index * 0.1,
                          duration: 0.5,
                          type: "spring",
                          stiffness: 100
                        }}
                      >
                        <Link to={`/products/${product.id}`} className="block group">
                          <div className="relative aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 mb-4">
                            {product.imageUrl ? (
                              <img
                                src={product.imageUrl}
                                alt={product.name}
                                className="w-full h-full object-center object-cover group-hover:scale-105 transition-transform duration-300"
                              />
                            ) : (
                              <div className="flex items-center justify-center h-full bg-gray-200">
                                <span className="text-gray-400">No image</span>
                              </div>
                            )}
                            {product.discount && product.discount > 0 && (
                              <div className="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                -{product.discount}%
                              </div>
                            )}
                          </div>
                          <h3 className="text-gray-900 text-lg font-medium">{product.name}</h3>
                          <p className="text-gray-700 mt-1">${product.price.toFixed(2)}</p>
                        </Link>
                      </motion.div>
                    ))}
                  </motion.div>
                ) : (
                  <div className="text-center text-gray-500 py-10">
                    <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p>No featured products available.</p>
                  </div>
                )}

                {/* Use button text and link from settings */}
                {buttonText && buttonLink && (
                  <motion.div
                    className="text-center mt-12"
                    initial={{ opacity: 0, y: 10 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: 0.5 }}
                  >
                    <Link
                      to={buttonLink} // Use link from settings
                      className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:text-white hover:text-white"
                    >
                      {buttonText} {/* Use text from settings */}
                      <svg className="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </Link>
                  </motion.div>
                )}
              </>
            )}
          </div>
        </AnimatedElement>
      );
    };

    // 渲染新品上市部分
    const renderNewArrivalsSection = (): ReactNode => {
      // Check if this section should be shown based on settings
      if (!settings?.newProducts?.title) return null; // Only render if title exists

      const sectionSettings = settings.newProducts;
      const sectionTitle = sectionSettings.title || 'New Arrivals';
      const sectionSubtitle = sectionSettings.subtitle || 'Discover our latest additions';
      const buttonText = sectionSettings.buttonText;
      const buttonLink = sectionSettings.buttonLink;

      return (
        <AnimatedElement type={getDeviceAnimation('slideUp')} className="py-20 bg-white relative overflow-hidden">
          {/* Decorative element */}
          <div className="absolute bottom-0 right-0 w-40 h-40 bg-green-100/50 rounded-tl-full translate-x-16 translate-y-16 blur-2xl pointer-events-none"></div>
          <div className="container mx-auto px-4 relative">
            <div className="text-center mb-14">
              {/* Section Tag */}
              <motion.div
                initial={{ opacity: 0, scale: 0.9 }}
                whileInView={{ opacity: 1, scale: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5 }}
              >
                <span className="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full mb-3">
                  JUST ARRIVED
                </span>
              </motion.div>
              <motion.h2
                className="text-3xl md:text-4xl font-bold text-gray-900 mb-4"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6 }}
              >
                {sectionTitle}
              </motion.h2>
              <motion.p
                className="text-gray-600 max-w-2xl mx-auto"
                initial={{ opacity: 0 }}
                whileInView={{ opacity: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6, delay: 0.2 }}
              >
                {sectionSubtitle}
              </motion.p>
            </div>

            {isLoading.newProducts ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="animate-pulse">
                    <div className="bg-gray-200 h-64 rounded-lg mb-4"></div>
                    <div className="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
                    <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                  </div>
                ))}
              </div>
            ) : (
              <>
                {templates?.newArrival && templates.newArrival.length > 0 ? (
                  <motion.div
                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, margin: "-100px" }}
                    transition={{ staggerChildren: 0.1 }}
                  >
                    {(templates?.newArrival || []).map((template, index) => (
                      <EnhancedTemplateCard
                        key={template.id}
                        template={template}
                        index={index}
                        highlightClass="border-green-100 hover:border-green-200"
                        mobileView={isMobile}
                      />
                    ))}
                  </motion.div>
                ) : newProducts && newProducts.length > 0 ? (
                  <motion.div
                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, margin: "-100px" }}
                    transition={{ staggerChildren: 0.1 }}
                  >
                    {newProducts.map((product, index) => (
                      <motion.div
                        key={product.id}
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{
                          delay: index * 0.1,
                          duration: 0.5,
                          type: "spring",
                          stiffness: 100
                        }}
                      >
                        <Link to={`/products/${product.id}`} className="block group">
                          <div className="relative aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 mb-4">
                            {product.imageUrl ? (
                              <img
                                src={product.imageUrl}
                                alt={product.name}
                                className="w-full h-full object-center object-cover group-hover:scale-105 transition-transform duration-300"
                              />
                            ) : (
                              <div className="flex items-center justify-center h-full bg-gray-200">
                                <span className="text-gray-400">No image</span>
                              </div>
                            )}
                            {product.discount && product.discount > 0 && (
                              <div className="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                -{product.discount}%
                              </div>
                            )}
                          </div>
                          <h3 className="text-gray-900 text-lg font-medium">{product.name}</h3>
                          <p className="text-gray-700 mt-1">${product.price.toFixed(2)}</p>
                        </Link>
                      </motion.div>
                    ))}
                  </motion.div>
                ) : (
                  <div className="text-center text-gray-500 py-10">
                    <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p>No new arrivals available.</p>
                  </div>
                )}

                {/* Use button text and link from settings */}
                {buttonText && buttonLink && (
                  <motion.div
                    className="text-center mt-12"
                    initial={{ opacity: 0, y: 10 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: 0.5 }}
                  >
                    <Link
                      to={buttonLink} // Use link from settings
                      className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 focus:text-white hover:text-white"
                    >
                      {buttonText} {/* Use text from settings */}
                      <svg className="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </Link>
                  </motion.div>
                )}
              </>
            )}
          </div>
        </AnimatedElement>
      );
    };

    // 渲染特价商品部分
    const renderSaleSection = (): ReactNode => {
      // Check if this section should be shown based on settings
      if (!settings?.saleProducts?.title) return null; // Only render if title exists

      const sectionSettings = settings.saleProducts;
      const sectionTitle = sectionSettings.title || 'On Sale';
      const sectionSubtitle = sectionSettings.subtitle || 'Special discounts and offers';
      const buttonText = sectionSettings.buttonText;
      const buttonLink = sectionSettings.buttonLink;

      return (
        <AnimatedElement type={getDeviceAnimation('slideUp')} className="py-20 bg-gradient-to-bl from-white to-red-50 relative overflow-hidden">
          {/* Decorative element */}
          <div className="absolute top-1/2 left-0 w-48 h-48 bg-red-100/50 rounded-full -translate-x-24 -translate-y-1/2 blur-2xl pointer-events-none"></div>
          <div className="container mx-auto px-4 relative">
            <div className="text-center mb-14">
              {/* Section Tag */}
              <motion.div
                initial={{ opacity: 0, scale: 0.9 }}
                whileInView={{ opacity: 1, scale: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5 }}
              >
                <span className="inline-block px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full mb-3">
                  SPECIAL OFFERS
                </span>
              </motion.div>
              <motion.h2
                className="text-3xl md:text-4xl font-bold text-gray-900 mb-4"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6 }}
              >
                {sectionTitle}
              </motion.h2>
              <motion.p
                className="text-gray-600 max-w-2xl mx-auto"
                initial={{ opacity: 0 }}
                whileInView={{ opacity: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6, delay: 0.2 }}
              >
                {sectionSubtitle}
              </motion.p>
            </div>

            {isLoading.saleProducts ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                {[...Array(4)].map((_, i) => (
                  <div key={i} className="animate-pulse">
                    <div className="bg-gray-200 h-64 rounded-lg mb-4"></div>
                    <div className="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
                    <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                  </div>
                ))}
              </div>
            ) : (
              <>
                {templates?.sale && templates.sale.length > 0 ? (
                  <motion.div
                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, margin: "-100px" }}
                    transition={{ staggerChildren: 0.1 }}
                  >
                    {(templates?.sale || []).map((template, index) => (
                      <EnhancedTemplateCard
                        key={template.id}
                        template={template}
                        index={index}
                        highlightClass="border-red-100 hover:border-red-200"
                        mobileView={isMobile}
                      />
                    ))}
                  </motion.div>
                ) : saleProducts && saleProducts.length > 0 ? (
                  <motion.div
                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                    viewport={{ once: true, margin: "-100px" }}
                  >
                    {saleProducts.map((product, index) => (
                      <motion.div
                        key={product.id}
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{
                          delay: index * 0.1,
                          duration: 0.5,
                          type: "spring",
                          stiffness: 100
                        }}
                      >
                        <Link to={`/products/${product.id}`} className="block group">
                          <div className="relative aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 mb-4">
                            {product.imageUrl ? (
                              <img
                                src={product.imageUrl}
                                alt={product.name}
                                className="w-full h-full object-center object-cover group-hover:scale-105 transition-transform duration-300"
                              />
                            ) : (
                              <div className="flex items-center justify-center h-full bg-gray-200">
                                <span className="text-gray-400">No image</span>
                              </div>
                            )}
                            {product.discount && product.discount > 0 && (
                              <div className="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                -{product.discount}%
                              </div>
                            )}
                          </div>
                          <h3 className="text-gray-900 text-lg font-medium">{product.name}</h3>
                          <p className="text-gray-700 mt-1">${product.price.toFixed(2)}</p>
                        </Link>
                      </motion.div>
                    ))}
                  </motion.div>
                ) : (
                  <div className="text-center text-gray-500 py-10">
                    <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p>No sale products available.</p>
                  </div>
                )}

                {/* Use button text and link from settings */}
                {buttonText && buttonLink && (
                  <motion.div
                    className="text-center mt-12"
                    initial={{ opacity: 0, y: 10 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ delay: 0.5 }}
                  >
                    <Link
                      to={buttonLink} // Use link from settings
                      className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 focus:text-white hover:text-white"
                    >
                      {buttonText} {/* Use text from settings */}
                      <svg className="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                      </svg>
                    </Link>
                  </motion.div>
                )}
              </>
            )}
          </div>
        </AnimatedElement>
      );
    };

    return (
      <>
        {renderFeaturedSection()}
        {renderNewArrivalsSection()}
        {renderSaleSection()}
      </>
    );
  };

  // Render brand philosophy section
  const renderPhilosophy = () => {
    return (
      <section className="py-24 bg-gray-50">
        <div className="container mx-auto px-6">
          <div className="grid md:grid-cols-2 gap-16 items-center">
            <AnimatedElement type="slide-right">
              <div className="aspect-w-1 aspect-h-1 relative">
                <img
                  src="https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                  alt="Minimalist shoe design"
                  className="w-full h-full object-cover"
                />
                <div className="absolute -bottom-5 -right-5 w-24 h-24 border border-gray-900 -z-10"></div>
              </div>
            </AnimatedElement>

            <AnimatedElement type="slide-left" options={{ delay: 0.2 }}>
              <div>
                <h2 className="text-3xl font-light text-gray-900 mb-6">Our Philosophy</h2>
                <p className="text-gray-600 leading-relaxed mb-8">
                  We believe that footwear should be beautiful in its simplicity, functional in its design, and responsible in its production. Every pair of YCE shoes represents our commitment to sustainable craftsmanship and timeless style.
                </p>
                <p className="text-gray-600 leading-relaxed mb-8">
                  Our minimalist approach extends beyond aesthetics—it's about creating products that last longer, reducing waste and environmental impact.
                </p>
                <a href="/about" className="inline-block border-b-2 border-gray-900 text-gray-900 font-light hover:text-gray-600 hover:border-gray-600 transition-colors duration-300">
                  Learn More About Our Process
                </a>
              </div>
            </AnimatedElement>
          </div>
        </div>
      </section>
    );
  };

  // Render template description
  const renderTemplateDescription = (template: ProductTemplate) => {
    if (!template) {
      return null;
    }

    const minPrice = findMinPrice(template);

    return (
      <div className="mb-12">
        <div className="text-center mb-6">
          <h2 className="text-2xl font-light text-gray-900 mb-2">{template.title || 'Product'}</h2>
          {minPrice !== null && (
            <div className="text-xl font-semibold text-red-600 mt-2">
              From ${minPrice}
            </div>
          )}
          <div className="h-px w-12 bg-gray-300 mx-auto my-4"></div>
        </div>

        {template.description && (
          <TemplateDescription
            htmlContent={template.description}
            options={{
              maxHeight: 160,
              responsive: true,
              className: 'prose max-w-none text-center mx-auto'
            }}
          />
        )}
      </div>
    );
  };

  // Render footer
  const renderFooter = () => {
    return (
      <footer className="bg-gray-900 text-white py-24">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <div>
              <h3 className="text-lg font-light mb-6 tracking-wider">YCE SHOES</h3>
              <p className="text-gray-400 text-sm leading-relaxed">
                Minimalist footwear for the modern individual. Designed with purpose, crafted with care.
              </p>
              <div className="mt-6 flex space-x-4">
                <a href="#" className="text-gray-400 hover:text-white transition-colors duration-300">
                  <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                </a>
                <a href="#" className="text-gray-400 hover:text-white transition-colors duration-300">
                  <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723 10.058 10.058 0 01-3.13 1.198 4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.665 2.473c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.061a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.937 4.937 0 004.604 3.417 9.868 9.868 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63a9.936 9.936 0 002.46-2.548l-.047-.02z" /></svg>
                </a>
                <a href="#" className="text-gray-400 hover:text-white transition-colors duration-300">
                  <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.074-4.947c-.061-1.277-.256-1.805-.421-.569-.224-.96-.479-1.38-.899-.42-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" /></svg>
                </a>
              </div>

              <div>
                <h3 className="text-lg font-light mb-6 tracking-wider">Collections</h3>
                <ul className="text-gray-400 space-y-3">
                  <li><Link to="/products/featured" className="hover:text-white transition-colors duration-300">Featured</Link></li>
                  <li><Link to="/products/new" className="hover:text-white transition-colors duration-300">New Arrivals</Link></li>
                  <li><Link to="/products/sale" className="hover:text-white transition-colors duration-300">Special Offers</Link></li>
                  <li><Link to="/categories" className="hover:text-white transition-colors duration-300">Categories</Link></li>
                </ul>
              </div>

              <div>
                <h3 className="text-lg font-light mb-6 tracking-wider">Information</h3>
                <ul className="text-gray-400 space-y-3">
                  <li><Link to="/about" className="hover:text-white transition-colors duration-300">About Us</Link></li>
                  <li><Link to="/contact" className="hover:text-white transition-colors duration-300">Contact</Link></li>
                  <li><Link to="/sustainability" className="hover:text-white transition-colors duration-300">Sustainability</Link></li>
                  <li><Link to="/faq" className="hover:text-white transition-colors duration-300">FAQ</Link></li>
                </ul>
              </div>

              <div>
                <h3 className="text-lg font-light mb-6 tracking-wider">Newsletter</h3>
                <p className="text-gray-400 text-sm mb-4">Subscribe for exclusive updates and offers.</p>
                <div className="flex">
                  <input
                    type="email"
                    placeholder="Your email"
                    className="bg-gray-800 text-white text-sm px-4 py-2 w-full focus:outline-none border-l border-t border-b border-gray-700"
                  />
                  <button className="bg-white text-gray-900 px-4 py-2 text-sm font-medium hover:bg-gray-200 transition-colors duration-300">
                    Subscribe
                  </button>
                </div>
              </div>
            </div>

            <div className="pt-8 border-t border-gray-800 text-center text-gray-500 text-sm">
              <p>&copy; {new Date().getFullYear()} YCE Shoes. All rights reserved.</p>
              <div className="flex justify-center space-x-6 mt-4">
                <Link to="/privacy" className="hover:text-white transition-colors duration-300">Privacy Policy</Link>
                <Link to="/terms" className="hover:text-white transition-colors duration-300">Terms of Service</Link>
              </div>
            </div>
          </div>
        </div>
      </footer>
    );
  };

  // Main content rendering
  return (
    <div className="minimal-template">
      {/* Header Navigation */}
      {renderHeader()}

      {/* Carousel */}
      {renderCarousel()}

      {/* Alternative Hero Section */}
      {renderHeroSection()}

      {/* Brand Ethos */}
      {renderBrandEthos()}

      {/* Categories Section */}
      {renderCategories()}

      {/* Products Section */}
      {renderProducts()}

      {/* Brand Philosophy */}
      {renderPhilosophy()}

      {/* Footer */}
      {renderFooter()}
    </div>
  );
};

export default MinimalTemplate; 
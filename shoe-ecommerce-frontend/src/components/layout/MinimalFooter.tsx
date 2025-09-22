import { Link } from 'react-router-dom';
import { motion, useAnimation } from 'framer-motion';
import { useEffect, useState } from 'react';
import { FiMail, FiMapPin, FiPhone, FiArrowUp } from 'react-icons/fi';

const MinimalFooter = () => {
  const currentYear = new Date().getFullYear();
  const [isVisible, setIsVisible] = useState(false);
  const [showScrollTop, setShowScrollTop] = useState(false);
  const controls = useAnimation();
  
  // 监听滚动以触发动画和显示返回顶部按钮
  useEffect(() => {
    const handleScroll = () => {
      const footer = document.getElementById('minimal-footer');
      if (footer) {
        const position = footer.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        if (position.top < windowHeight * 0.8) {
          setIsVisible(true);
          controls.start('visible');
        }
      }
      
      // 显示/隐藏回到顶部按钮
      setShowScrollTop(window.scrollY > 300);
    };
    
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // 初始检查
    
    return () => window.removeEventListener('scroll', handleScroll);
  }, [controls]);
  
  // 滚动到顶部
  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
  
  // 动画变体
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1
      }
    }
  };
  
  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: { duration: 0.5 }
    }
  };
  
  return (
    <footer id="minimal-footer" className="bg-white border-t border-gray-200 pt-16 pb-8 overflow-hidden">
      <div className="container mx-auto px-4">
        <motion.div 
          className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12"
          variants={containerVariants}
          initial="hidden"
          animate={controls}
        >
          {/* 品牌信息 */}
          <motion.div variants={itemVariants} className="pr-4">
            <h2 className="text-2xl font-light mb-5">
              <span className="font-medium">YCE</span>
              <span className="opacity-60 ml-1">Shoes</span>
            </h2>
            <p className="text-sm text-gray-600 mb-6 leading-relaxed">
              Discover the perfect blend of elegance and comfort with our premium footwear collection, 
              designed for those who appreciate luxury in every step.
            </p>
            <div className="flex space-x-4">
              <motion.a 
                href="#" 
                className="w-8 h-8 rounded-sm border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-black hover:text-white hover:border-black transition-colors duration-300"
                whileHover={{ y: -2 }}
                whileTap={{ scale: 0.95 }}
              >
                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                </svg>
              </motion.a>
              <motion.a 
                href="#" 
                className="w-8 h-8 rounded-sm border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-black hover:text-white hover:border-black transition-colors duration-300"
                whileHover={{ y: -2 }}
                whileTap={{ scale: 0.95 }}
              >
                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                </svg>
              </motion.a>
              <motion.a 
                href="#" 
                className="w-8 h-8 rounded-sm border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-black hover:text-white hover:border-black transition-colors duration-300"
                whileHover={{ y: -2 }}
                whileTap={{ scale: 0.95 }}
              >
                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                </svg>
              </motion.a>
            </div>
          </motion.div>
          
          {/* 导航链接 */}
          <motion.div variants={itemVariants} className="lg:ml-8">
            <h3 className="text-base font-medium text-black mb-5">
              Shop
            </h3>
            <ul className="space-y-2">
              <li>
                <Link to="/products" className="text-sm text-gray-600 hover:text-black transition-colors duration-200 inline-block">
                  All Products
                </Link>
              </li>
              <li>
                <Link to="/products?new=true" className="text-sm text-gray-600 hover:text-black transition-colors duration-200 inline-block">
                  New Arrivals
                </Link>
              </li>
              <li>
                <Link to="/products?sale=true" className="text-sm text-gray-600 hover:text-black transition-colors duration-200 inline-block">
                  Sale
                </Link>
              </li>
              <li>
                <Link to="/categories" className="text-sm text-gray-600 hover:text-black transition-colors duration-200 inline-block">
                  Collections
                </Link>
              </li>
            </ul>
          </motion.div>
          
          {/* 支持链接 */}
          <motion.div variants={itemVariants}>
            <h3 className="text-base font-medium text-black mb-5">
              Support
            </h3>
            <ul className="space-y-3">
              <li>
                <Link to="/faq" className="text-sm text-gray-600 hover:text-indigo-600 transition-colors duration-200 inline-block">
                  FAQ
                </Link>
              </li>
              <li>
                <Link to="/shipping" className="text-sm text-gray-600 hover:text-indigo-600 transition-colors duration-200 inline-block">
                  Shipping
                </Link>
              </li>
              <li>
                <Link to="/returns" className="text-sm text-gray-600 hover:text-indigo-600 transition-colors duration-200 inline-block">
                  Returns
                </Link>
              </li>
              <li>
                <Link to="/privacy" className="text-sm text-gray-600 hover:text-indigo-600 transition-colors duration-200 inline-block">
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link to="/terms" className="text-sm text-gray-600 hover:text-indigo-600 transition-colors duration-200 inline-block">
                  Terms of Service
                </Link>
              </li>
            </ul>
          </motion.div>
          
          {/* 联系信息 */}
          <motion.div variants={itemVariants}>
            <h3 className="text-base font-medium text-black mb-5">
              Contact
            </h3>
            <ul className="space-y-4">
              <li className="flex items-start text-sm">
                <FiMapPin className="mt-1 mr-3 text-gray-400" />
                <span className="text-gray-600">123 Fashion Street, Design District, Los Angeles, CA 90001</span>
              </li>
              <li className="flex items-center text-sm">
                <FiPhone className="mr-3 text-gray-400" />
                <a href="tel:+1234567890" className="text-gray-600 hover:text-black transition-colors duration-200">
                  +1 (234) 567-890
                </a>
              </li>
              <li className="flex items-center text-sm">
                <FiMail className="mr-3 text-gray-400 flex-shrink-0" />
                <a href="mailto:support@yceshoes.com" className="text-gray-600 hover:text-indigo-600 transition-colors duration-200 truncate">
                  support@yceshoes.com
                </a>
              </li>
            </ul>
          </motion.div>
        </motion.div>
        
        <motion.div 
          variants={itemVariants}
          initial="hidden"
          animate={controls}
          className="text-center pt-6 border-t border-gray-200"
        >
          <p className="text-sm text-gray-500">
            © {currentYear} YCE Shoes. All rights reserved.
          </p>
        </motion.div>
      </div>
      
      {/* 回到顶部按钮 */}
      <motion.button
        onClick={scrollToTop}
        className={`fixed right-6 bottom-6 w-10 h-10 bg-black text-white rounded-sm shadow-lg flex items-center justify-center hover:bg-gray-800 transition-colors z-50 ${
          showScrollTop ? 'opacity-100' : 'opacity-0 pointer-events-none'
        }`}
        animate={{ y: showScrollTop ? 0 : 20 }}
        transition={{ duration: 0.2 }}
        aria-label="Back to top"
      >
        <FiArrowUp size={18} />
      </motion.button>
    </footer>
  );
};

export default MinimalFooter; 
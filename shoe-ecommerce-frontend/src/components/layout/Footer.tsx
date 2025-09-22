import { Link } from 'react-router-dom';
import { motion, useInView } from 'framer-motion';
import { useEffect, useState, useRef } from 'react';

const Footer = () => {
  const currentYear = new Date().getFullYear();
  const footerRef = useRef<HTMLElement>(null);
  const isInView = useInView(footerRef, { once: true, amount: 0.3 });
  
  // 标签动画变体
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        delayChildren: 0.3,
        staggerChildren: 0.1
      }
    }
  };
  
  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: { type: 'spring', stiffness: 300, damping: 24 }
    }
  };
  
  // 循环动画 - 标志效果
  const logoVariants = {
    animate: {
      y: [0, -8, 0],
      transition: {
        duration: 4,
        ease: "easeInOut",
        repeat: Infinity,
        repeatType: "mirror" as const
      }
    }
  };
  
  return (
    <footer id="yce-footer" ref={footerRef} className="bg-gray-900 text-gray-300 pt-20 pb-8 overflow-hidden">
      <div className="container mx-auto px-4">
        {/* Animated Grid Container */}
        <motion.div 
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16"
          variants={containerVariants}
          initial="hidden"
          animate={isInView ? "visible" : "hidden"}
        >
          {/* Brand Info */}
          <motion.div variants={itemVariants}>
            <motion.div 
               variants={logoVariants} 
               animate="animate" 
               className="mb-6 inline-block" // Make inline-block for logo animation
             >
              <Link to="/" className="flex items-baseline group">
                <span className="text-3xl font-serif font-bold tracking-wider text-white mr-1 group-hover:text-indigo-300 transition-colors duration-300">YCE</span>
                <span className="text-3xl font-light italic text-indigo-400 group-hover:text-indigo-300 transition-colors duration-300">Shoes</span>
              </Link>
            </motion.div>
            <p className="text-gray-400 mb-6 leading-relaxed text-sm">
              Discover the perfect blend of elegance and comfort with our premium footwear collection, 
              designed for those who appreciate luxury in every step.
            </p>
            {/* Social Icons */}
            <div className="flex space-x-5">
              <SocialIcon href="#" iconClass="fab fa-facebook-f" />
              <SocialIcon href="#" iconClass="fab fa-instagram" />
              <SocialIcon href="#" iconClass="fab fa-twitter" />
              <SocialIcon href="#" iconClass="fab fa-pinterest-p" />
            </div>
          </motion.div>
          
          {/* Quick Links & Services (Combine maybe?) */}
          <motion.div variants={itemVariants}>
            <FooterLinkSection title="EXPLORE">
              <FooterLink to="/">Home</FooterLink>
              <FooterLink to="/products">Products</FooterLink>
              <FooterLink to="/about">About Us</FooterLink>
              <FooterLink to="/contact">Contact Us</FooterLink>
            </FooterLinkSection>
          </motion.div>
          
          <motion.div variants={itemVariants}>
            <FooterLinkSection title="SUPPORT">
              <FooterLink to="/faq">FAQ</FooterLink>
              <FooterLink to="/shipping">Shipping & Delivery</FooterLink>
              <FooterLink to="/returns">Returns Policy</FooterLink>
              <FooterLink to="/privacy">Privacy Policy</FooterLink>
              <FooterLink to="/terms">Terms of Service</FooterLink>
            </FooterLinkSection>
          </motion.div>
          
          {/* Newsletter/Contact */}
          <motion.div variants={itemVariants}>
            <h3 className="text-lg font-medium text-white mb-6 relative inline-block">
              STAY CONNECTED
              <span className="absolute -bottom-2 left-0 w-10 h-0.5 bg-indigo-500"></span>
            </h3>
            <p className="text-gray-400 mb-4 text-sm">Subscribe for exclusive updates and offers.</p>
            <form className="flex mb-6">
                <input 
                  type="email"
                placeholder="Your email address"
                className="px-4 py-2.5 bg-gray-800 text-white border border-gray-700 rounded-l-md focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-full text-sm placeholder-gray-500"
                />
                <motion.button
                  type="submit"
                className="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-r-md font-medium text-sm transition-colors duration-300"
                  whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.98 }}
                >
                Sign Up
                </motion.button>
              </form>
             <p className="text-gray-400 text-sm">
                Questions? Call us: <a href="tel:+18001234567" className="text-indigo-400 hover:text-indigo-300 transition-colors">+1 800-123-4567</a>
            </p>
          </motion.div>
        </motion.div>

        {/* Bottom Bar */}
        <div className="border-t border-gray-800 pt-8 text-center">
          <p className="text-sm text-gray-500">
            &copy; {currentYear} YCE Shoes. All Rights Reserved.
          </p>
          {/* Optional: Add secondary links like sitemap etc. */}
        </div>
      </div>
    </footer>
  );
};

// Helper component for Footer Links Section
const FooterLinkSection = ({ title, children }: { title: string, children: React.ReactNode }) => (
  <div>
    <h3 className="text-lg font-medium text-white mb-6 relative inline-block">
      {title}
      <span className="absolute -bottom-2 left-0 w-10 h-0.5 bg-indigo-500"></span>
    </h3>
    <ul className="space-y-3">
      {children}
    </ul>
  </div>
);

// Helper component for Footer Links
const FooterLink = ({ to, children }: { to: string, children: React.ReactNode }) => (
  <motion.li whileHover={{ x: 5 }} transition={{ type: "spring", stiffness: 400 }}>
    <Link to={to} className="text-gray-400 hover:text-white transition-colors duration-300 inline-block text-sm">
      {children}
    </Link>
  </motion.li>
);

// Helper component for Social Icons
const SocialIcon = ({ href, iconClass }: { href: string, iconClass: string }) => (
   <motion.a
      href={href}
      className="text-gray-400 hover:text-white transition-colors duration-300 block p-1.5 border border-transparent hover:border-gray-700 rounded-full"
      whileHover={{ scale: 1.15, rotate: 5, backgroundColor: 'rgba(255, 255, 255, 0.1)'}}
      whileTap={{ scale: 0.9 }}
      target="_blank"
      rel="noopener noreferrer"
    >
      <i className={`${iconClass} text-xl w-5 h-5 text-center`}></i>
    </motion.a>
);

export default Footer; 
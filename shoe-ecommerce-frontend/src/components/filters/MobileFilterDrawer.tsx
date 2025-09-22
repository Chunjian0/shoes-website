import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { FiX } from 'react-icons/fi';
import ProductFilters from './ProductFilters';

interface MobileFilterDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  categories: Array<{id: number; name: string}>;
  filters: {
    category: string;
    minPrice: string;
    maxPrice: string;
    sort: string;
  };
  onFilterChange: (name: string, value: string) => void;
  onReset: () => void;
  onRefresh: () => void;
  isRefreshing?: boolean;
}

const MobileFilterDrawer: React.FC<MobileFilterDrawerProps> = ({
  isOpen,
  onClose,
  categories,
  filters,
  onFilterChange,
  onReset,
  onRefresh,
  isRefreshing = false
}) => {
  // 阻止点击抽屉内部时关闭
  const handleDrawerClick = (e: React.MouseEvent) => {
    e.stopPropagation();
  };
  
  // 抽屉动画
  const drawerVariants = {
    hidden: { 
      x: '-100%',
      transition: { 
        type: 'tween', 
        duration: 0.4,
        ease: [0.32, 0.72, 0, 1]
      }
    },
    visible: { 
      x: 0,
      transition: { 
        type: 'tween', 
        duration: 0.4,
        ease: [0.32, 0.72, 0, 1]
      }
    }
  };
  
  // 背景遮罩动画
  const overlayVariants = {
    hidden: { opacity: 0 },
    visible: { 
      opacity: 1,
      transition: {
        duration: 0.3
      }
    }
  };
  
  // 按钮动画
  const buttonVariants = {
    hover: { 
      scale: 1.05,
      transition: { duration: 0.2 }
    },
    tap: { 
      scale: 0.95,
      transition: { duration: 0.1 }
    }
  };
  
  return (
    <AnimatePresence>
      {isOpen && (
        <div className="fixed inset-0 z-50 overflow-hidden">
          {/* 背景遮罩 */}
          <motion.div
            className="absolute inset-0"
            style={{ backgroundColor: 'rgba(10, 10, 10, 0.5)' }}
            variants={overlayVariants}
            initial="hidden"
            animate="visible"
            exit="hidden"
            onClick={onClose}
          />
          
          {/* 筛选抽屉 */}
          <motion.div
            className="absolute inset-y-0 left-0 max-w-[85%] w-[320px] overflow-y-auto"
            style={{ 
              background: 'linear-gradient(160deg, #ffffff, #fefdf8)',
              boxShadow: '0 10px 40px rgba(0, 0, 0, 0.3)',
              borderRight: '1px solid rgba(212, 175, 55, 0.2)'
            }}
            variants={drawerVariants}
            initial="hidden"
            animate="visible"
            exit="hidden"
            onClick={handleDrawerClick}
          >
            {/* 抽屉标题栏 */}
            <div className="sticky top-0 flex items-center justify-between p-5 z-10" 
                style={{ 
                  borderBottom: '1px solid rgba(212, 175, 55, 0.2)',
                  background: 'rgba(255, 255, 255, 0.95)',
                  backdropFilter: 'blur(10px)'
                }}>
              <h2 style={{ 
                fontFamily: 'Playfair Display, serif',
                fontSize: '18px',
                lineHeight: '1.3',
                letterSpacing: '-0.02em',
                color: '#0A0A0A'
              }}>Refine Selection</h2>
              <motion.button 
                onClick={onClose} 
                className="focus:outline-none"
                aria-label="Close filter drawer"
                variants={buttonVariants}
                whileHover="hover"
                whileTap="tap"
                style={{ color: '#D4AF37' }}
              >
                <FiX size={24} />
              </motion.button>
            </div>
            
            {/* 筛选组件 */}
            <div className="p-5">
              <ProductFilters
                categories={categories}
                initialFilters={filters}
                onFilterChange={onFilterChange}
                onReset={onReset}
                onRefresh={onRefresh}
                isRefreshing={isRefreshing}
                isMobile={false} // 在抽屉中始终展开显示
              />
              
              {/* 应用并关闭按钮 */}
              <div className="mt-8 flex space-x-3">
                <motion.button
                  onClick={onClose}
                  className="flex-1 py-3"
                  variants={buttonVariants}
                  whileHover="hover"
                  whileTap="tap"
                  style={{
                    background: '#0A0A0A',
                    color: '#D4AF37',
                    fontFamily: 'Montserrat, sans-serif',
                    fontSize: '14px',
                    fontWeight: 500,
                    letterSpacing: '0.05em',
                    border: 'none',
                    boxShadow: '0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1)',
                    textTransform: 'uppercase'
                  }}
                >
                  Apply & Close
                </motion.button>
                
                <motion.button
                  onClick={onReset}
                  className="flex-1 py-3"
                  variants={buttonVariants}
                  whileHover="hover"
                  whileTap="tap"
                  style={{
                    background: 'transparent',
                    color: '#0A0A0A',
                    fontFamily: 'Montserrat, sans-serif',
                    fontSize: '14px',
                    fontWeight: 500,
                    letterSpacing: '0.05em',
                    border: '1px solid rgba(212, 175, 55, 0.3)',
                    textTransform: 'uppercase'
                  }}
                >
                  Reset All
                </motion.button>
              </div>
            </div>
          </motion.div>
        </div>
      )}
    </AnimatePresence>
  );
};

export default MobileFilterDrawer; 
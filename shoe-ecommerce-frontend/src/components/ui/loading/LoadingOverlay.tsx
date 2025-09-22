import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import Spinner from './Spinner';

interface LoadingOverlayProps {
  isLoading: boolean;
  text?: string;
  variant?: 'full' | 'container' | 'minimal';
  spinnerSize?: 'sm' | 'md' | 'lg' | 'xl';
  spinnerVariant?: 'primary' | 'gold' | 'light';
  backdropClassName?: string;
  zIndex?: number;
  children?: React.ReactNode;
  animated?: boolean;
}

/**
 * 奢华风格的加载覆盖层组件
 * 支持全屏、容器内和最小化三种变体
 */
const LoadingOverlay: React.FC<LoadingOverlayProps> = ({
  isLoading,
  text,
  variant = 'container',
  spinnerSize = 'lg',
  spinnerVariant = 'gold',
  backdropClassName = '',
  zIndex = 50,
  children,
  animated = true,
}) => {
  // 背景样式映射
  const backdropStyles = {
    full: 'fixed inset-0 flex items-center justify-center',
    container: 'absolute inset-0 flex items-center justify-center',
    minimal: 'absolute inset-0 flex items-center justify-center',
  };

  // 背景颜色映射
  const backdropBackgroundStyles = {
    full: 'bg-black bg-opacity-80 backdrop-blur-sm',
    container: 'bg-black bg-opacity-70 backdrop-blur-sm',
    minimal: 'bg-transparent',
  };

  // 覆盖层动画变体
  const overlayVariants = {
    hidden: { opacity: 0 },
    visible: { 
      opacity: 1,
      transition: { 
        duration: 0.3,
        ease: [0, 0, 0.2, 1]
      }
    },
    exit: { 
      opacity: 0,
      transition: { 
        duration: 0.2,
        ease: [0.4, 0, 1, 1]
      }
    }
  };

  // 文字淡入动画
  const textVariants = {
    hidden: { opacity: 0, y: 10 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { 
        delay: 0.2,
        duration: 0.3,
        ease: "easeOut"
      }
    },
    exit: { 
      opacity: 0,
      transition: { 
        duration: 0.15
      }
    }
  };

  // 加载指示器动画
  const spinnerVariants = {
    hidden: { opacity: 0, scale: 0.9 },
    visible: { 
      opacity: 1, 
      scale: 1,
      transition: { 
        duration: 0.3,
        ease: "easeOut"
      }
    },
    exit: { 
      opacity: 0,
      scale: 0.9,
      transition: { 
        duration: 0.2
      }
    }
  };

  // 奢华装饰线条动画
  const decorationVariants = {
    hidden: { opacity: 0, width: '0%' },
    visible: { 
      opacity: 1, 
      width: '60px',
      transition: { 
        delay: 0.3,
        duration: 0.5,
        ease: "easeOut"
      }
    },
    exit: { 
      opacity: 0,
      width: '0%',
      transition: { 
        duration: 0.15
      }
    }
  };

  return (
    <div className="relative" style={{ minHeight: variant === 'minimal' ? '100px' : 'auto' }}>
      {children}
      
      <AnimatePresence>
        {isLoading && (
          <motion.div
            className={`${backdropStyles[variant]} ${backdropBackgroundStyles[variant]} ${backdropClassName}`}
            style={{ zIndex }}
            {...(animated ? { 
              initial: "hidden", 
              animate: "visible", 
              exit: "exit", 
              variants: overlayVariants 
            } : {})}
          >
            <div className="flex flex-col items-center justify-center p-6 max-w-md mx-auto text-center">
              <motion.div
                {...(animated ? { 
                  variants: spinnerVariants, 
                  initial: "hidden", 
                  animate: "visible", 
                  exit: "exit" 
                } : {})}
              >
                <Spinner 
                  size={spinnerSize}
                />
              </motion.div>
              
              {text && (
                <>
                  <motion.div
                    className="my-3 h-px bg-gradient-to-r from-transparent via-amber-500 to-transparent"
                    {...(animated ? { 
                      variants: decorationVariants, 
                      initial: "hidden", 
                      animate: "visible", 
                      exit: "exit" 
                    } : {})}
                  />
                  
                  <motion.p
                    className="mt-2 text-base font-playfair font-medium text-white dark:text-gray-200"
                    {...(animated ? { 
                      variants: textVariants, 
                      initial: "hidden", 
                      animate: "visible", 
                      exit: "exit" 
                    } : {})}
                  >
                    {text}
                  </motion.p>
                </>
              )}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default LoadingOverlay; 
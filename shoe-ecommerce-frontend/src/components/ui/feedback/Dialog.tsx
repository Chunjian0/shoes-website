import React, { useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

type DialogSize = 'sm' | 'md' | 'lg' | 'xl' | 'full';
type DialogVariant = 'default' | 'destructive' | 'success' | 'luxury' | 'minimal';

interface DialogProps {
  isOpen: boolean;
  onClose: () => void;
  title?: React.ReactNode;
  description?: React.ReactNode;
  children?: React.ReactNode;
  footer?: React.ReactNode;
  size?: DialogSize;
  variant?: DialogVariant;
  closeOnEscape?: boolean;
  closeOnOutsideClick?: boolean;
  showCloseButton?: boolean;
  preventScroll?: boolean;
  className?: string;
  contentClassName?: string;
  headerClassName?: string;
  footerClassName?: string;
  disableAnimation?: boolean;
  bodyClassName?: string;
}

/**
 * 奢华风格的对话框组件
 * 提供多种尺寸和样式变体，带有流畅的动画效果
 */
const Dialog: React.FC<DialogProps> = ({
  isOpen,
  onClose,
  title,
  description,
  children,
  footer,
  size = 'md',
  variant = 'default',
  closeOnEscape = true,
  closeOnOutsideClick = true,
  showCloseButton = true,
  preventScroll = true,
  className = '',
  contentClassName = '',
  headerClassName = '',
  footerClassName = '',
  disableAnimation = false,
  bodyClassName = '',
}) => {
  const dialogRef = useRef<HTMLDivElement>(null);
  
  // 尺寸配置
  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    full: 'max-w-full mx-4 md:mx-8',
  };
  
  // 变体样式配置
  const variantStyles = {
    default: {
      container: 'bg-white dark:bg-gray-900 shadow-xl',
      header: 'border-b border-gray-200 dark:border-gray-800',
      title: 'text-gray-900 dark:text-white',
      description: 'text-gray-600 dark:text-gray-300',
      closeButton: 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800',
      footer: 'border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900',
    },
    destructive: {
      container: 'bg-white dark:bg-gray-900 shadow-xl border-t-4 border-red-500',
      header: 'border-b border-gray-200 dark:border-gray-800',
      title: 'text-red-600 dark:text-red-400',
      description: 'text-gray-600 dark:text-gray-300',
      closeButton: 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800',
      footer: 'border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900',
    },
    success: {
      container: 'bg-white dark:bg-gray-900 shadow-xl border-t-4 border-green-500',
      header: 'border-b border-gray-200 dark:border-gray-800',
      title: 'text-green-600 dark:text-green-400',
      description: 'text-gray-600 dark:text-gray-300',
      closeButton: 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800',
      footer: 'border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900',
    },
    luxury: {
      container: 'bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 shadow-2xl border border-gray-200 dark:border-gray-800',
      header: 'border-b border-gray-200 dark:border-gray-800 bg-gradient-to-r from-amber-50 to-white dark:from-gray-900/50 dark:to-gray-900',
      title: 'text-gray-900 dark:text-white font-playfair',
      description: 'text-gray-600 dark:text-gray-300 font-montserrat',
      closeButton: 'text-gray-500 dark:text-gray-400 hover:bg-gray-100/50 dark:hover:bg-gray-800/50',
      footer: 'border-t border-gray-200 dark:border-gray-800 bg-gradient-to-r from-white to-gray-50 dark:from-gray-950 dark:to-gray-900',
    },
    minimal: {
      container: 'bg-white dark:bg-gray-900 shadow-lg',
      header: 'border-b border-gray-100 dark:border-gray-800/50',
      title: 'text-gray-800 dark:text-white',
      description: 'text-gray-500 dark:text-gray-400',
      closeButton: 'text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400',
      footer: 'bg-transparent',
    },
  };
  
  // 阻止滚动
  useEffect(() => {
    if (preventScroll && isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    
    return () => {
      document.body.style.overflow = '';
    };
  }, [isOpen, preventScroll]);
  
  // 键盘事件处理 (Esc 键关闭)
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && isOpen && closeOnEscape) {
        onClose();
      }
    };
    
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onClose, closeOnEscape]);
  
  // 点击外部区域关闭
  const handleBackdropClick = (e: React.MouseEvent<HTMLDivElement>) => {
    if (closeOnOutsideClick && e.target === e.currentTarget) {
      onClose();
    }
  };
  
  // 动画变体
  const backdropVariants = {
    hidden: { opacity: 0 },
    visible: { 
      opacity: 1,
      transition: { duration: 0.3 }
    },
    exit: { 
      opacity: 0,
      transition: { duration: 0.2, delay: 0.1 }
    }
  };
  
  const dialogVariants = {
    hidden: { opacity: 0, scale: 0.95, y: 10 },
    visible: { 
      opacity: 1, 
      scale: 1, 
      y: 0,
      transition: {
        type: 'spring',
        damping: 25,
        stiffness: 400,
        duration: 0.3
      }
    },
    exit: { 
      opacity: 0, 
      scale: 0.95, 
      y: 10, 
      transition: { duration: 0.2 }
    }
  };
  
  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
          initial={disableAnimation ? undefined : "hidden"}
          animate={disableAnimation ? undefined : "visible"}
          exit={disableAnimation ? undefined : "exit"}
          variants={disableAnimation ? undefined : backdropVariants}
          onClick={handleBackdropClick}
        >
          <motion.div
            ref={dialogRef}
            className={`relative w-full ${sizeClasses[size]} rounded-lg overflow-hidden ${variantStyles[variant].container} ${className}`}
            initial={disableAnimation ? undefined : "hidden"}
            animate={disableAnimation ? undefined : "visible"}
            exit={disableAnimation ? undefined : "exit"}
            variants={disableAnimation ? undefined : dialogVariants}
            role="dialog"
            aria-modal="true"
            tabIndex={-1}
          >
            {/* 头部 */}
            {(title || showCloseButton) && (
              <div className={`px-6 py-4 ${variantStyles[variant].header} ${headerClassName}`}>
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    {title && <h3 className={`text-lg font-semibold ${variantStyles[variant].title}`}>{title}</h3>}
                    {description && <p className={`mt-1 text-sm ${variantStyles[variant].description}`}>{description}</p>}
                  </div>
                  
                  {/* 关闭按钮 */}
                  {showCloseButton && (
                    <button
                      type="button"
                      onClick={onClose}
                      className={`p-2 rounded-full ${variantStyles[variant].closeButton} focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-600`}
                      aria-label="Close dialog"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  )}
                </div>
              </div>
            )}
            
            {/* 主体内容 */}
            <div className={`p-6 overflow-auto ${bodyClassName}`}>
              <div className={contentClassName}>
                {children}
              </div>
            </div>
            
            {/* 底部 */}
            {footer && (
              <div className={`px-6 py-4 ${variantStyles[variant].footer} ${footerClassName}`}>
                {footer}
              </div>
            )}
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};

export default Dialog; 
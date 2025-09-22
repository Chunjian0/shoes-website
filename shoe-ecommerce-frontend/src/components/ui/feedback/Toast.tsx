import React, { useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

export type ToastType = 'success' | 'error' | 'info' | 'warning';

export interface ToastProps {
  id: string;
  message: string;
  type: ToastType;
  title?: string;
  onClose: (id: string) => void;
  duration?: number;
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
  hasAction?: boolean;
  actionLabel?: string;
  onAction?: () => void;
}

/**
 * 奢华风格的Toast通知组件
 * 提供各种通知类型和位置选项，带有优雅的动画效果
 */
const Toast: React.FC<ToastProps> = ({
  id,
  message,
  type = 'info',
  title,
  onClose,
  duration = 5000,
  position = 'top-right',
  hasAction = false,
  actionLabel = 'Action',
  onAction,
}) => {
  // 不同类型的配置
  const typeConfig = {
    success: {
      bgClass: 'bg-gradient-to-r from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700',
      iconColor: 'text-white',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
        </svg>
      ),
      defaultTitle: 'Success',
    },
    error: {
      bgClass: 'bg-gradient-to-r from-red-500 to-red-600 dark:from-red-600 dark:to-red-700',
      iconColor: 'text-white',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      ),
      defaultTitle: 'Error',
    },
    info: {
      bgClass: 'bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700',
      iconColor: 'text-white',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      ),
      defaultTitle: 'Information',
    },
    warning: {
      bgClass: 'bg-gradient-to-r from-amber-500 to-amber-600 dark:from-amber-600 dark:to-amber-700',
      iconColor: 'text-white',
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      ),
      defaultTitle: 'Warning',
    },
  };

  // 位置样式
  const positionClasses = {
    'top-right': 'top-4 right-4',
    'top-left': 'top-4 left-4',
    'bottom-right': 'bottom-4 right-4',
    'bottom-left': 'bottom-4 left-4',
    'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
    'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2',
  };

  // 关闭处理 - 使用 useCallback 包装
  const handleClose = useCallback(() => {
    onClose(id);
  }, [id, onClose]);

  // 自动关闭计时器
  useEffect(() => {
    let timer: NodeJS.Timeout | null = null;
    if (duration && duration > 0) { // 确保 duration 大于 0
      timer = setTimeout(() => {
        handleClose();
      }, duration);
    }

    return () => {
      if (timer) {
        clearTimeout(timer);
      }
    };
  }, [duration, handleClose]); // 添加 handleClose 到依赖数组

  // 动画变体
  const variants = {
    initial: {
      opacity: 0,
      y: position.includes('top') ? -20 : 20,
      scale: 0.95,
    },
    animate: {
      opacity: 1,
      y: 0,
      scale: 1,
      transition: {
        duration: 0.3,
        ease: [0.4, 0, 0.2, 1],
      },
    },
    exit: {
      opacity: 0,
      scale: 0.95,
      transition: {
        duration: 0.2,
        ease: [0.4, 0, 1, 1],
      },
    },
  };

  // 闪光效果
  const shimmerVariants = {
    animate: {
      x: ['0%', '100%'],
      opacity: [0, 0.1, 0],
      transition: {
        repeat: Infinity,
        repeatType: 'loop' as const,
        duration: 2,
        ease: 'linear',
      },
    },
  };

  return (
    <AnimatePresence mode="wait">
      <motion.div
        className={`fixed z-50 ${positionClasses[position]} shadow-xl rounded-lg overflow-hidden max-w-md w-full`}
        initial="initial"
        animate="animate"
        exit="exit"
        variants={variants}
        style={{ boxShadow: '0 10px 30px rgba(0, 0, 0, 0.1), 0 1px 5px rgba(0, 0, 0, 0.05)' }}
      >
        <div className={`relative ${typeConfig[type].bgClass}`}>
          {/* 闪光效果 */}
          <motion.div
            className="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white to-transparent"
            style={{ opacity: 0.1 }}
            animate="animate"
            variants={shimmerVariants}
          />
          
          <div className="px-4 py-3 flex items-start">
            {/* 图标 */}
            <div className={`mr-3 mt-0.5 ${typeConfig[type].iconColor} flex-shrink-0`}>
              {typeConfig[type].icon}
            </div>
            
            {/* 内容区域 */}
            <div className="flex-grow">
              {title || typeConfig[type].defaultTitle ? (
                <h4 className="text-white text-sm font-semibold mb-1 font-montserrat">
                  {title || typeConfig[type].defaultTitle}
                </h4>
              ) : null}
              
              <p className="text-white text-xs leading-relaxed font-montserrat">
                {message}
              </p>
              
              {/* 操作按钮 */}
              {hasAction && onAction && (
                <button
                  onClick={onAction}
                  className="mt-2 px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded text-xs font-medium transition-colors duration-200"
                >
                  {actionLabel}
                </button>
              )}
            </div>
            
            {/* 关闭按钮 */}
            <button 
              onClick={handleClose} 
              className="ml-2 p-1 rounded-full hover:bg-white/10 transition-colors text-white/80 hover:text-white flex-shrink-0"
              aria-label="Close notification"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          {/* 进度条 */}
          {duration > 0 && (
            <motion.div
              className="h-0.5 bg-white/20 origin-left"
              initial={{ scaleX: 1 }}
              animate={{ scaleX: 0 }}
              transition={{ duration: duration / 1000, ease: 'linear' }}
            />
          )}
        </div>
      </motion.div>
    </AnimatePresence>
  );
};

export default Toast; 
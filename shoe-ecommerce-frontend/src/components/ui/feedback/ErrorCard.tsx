import React from 'react';
import { motion } from 'framer-motion';

interface ErrorCardProps {
  title?: string;
  message: string;
  code?: string | number;
  retryAction?: () => void;
  retryLabel?: string;
  dismissAction?: () => void;
  dismissLabel?: string;
  variant?: 'default' | 'subtle' | 'bordered' | 'floating';
  className?: string;
  animated?: boolean;
}

/**
 * 高端奢华风格的错误卡片组件
 * 用于显示错误信息，提供多种样式和操作选项
 */
const ErrorCard: React.FC<ErrorCardProps> = ({
  title = 'An error occurred',
  message,
  code,
  retryAction,
  retryLabel = 'Try Again',
  dismissAction,
  dismissLabel = 'Dismiss',
  variant = 'default',
  className = '',
  animated = true,
}) => {
  // 变体样式
  const variantStyles = {
    default: `bg-gradient-to-b from-red-50 to-white dark:from-red-950/30 dark:to-gray-950 
              shadow-sm rounded-xl`,
    subtle: `bg-red-50 dark:bg-red-950/20 rounded-lg`,
    bordered: `bg-white dark:bg-gray-900 border border-red-300 dark:border-red-800/50 rounded-xl 
              shadow-sm`,
    floating: `bg-white dark:bg-gray-900 shadow-xl rounded-2xl`,
  };
  
  // 动画变体
  const cardVariants = {
    hidden: { opacity: 0, y: 20, scale: 0.97 },
    visible: {
      opacity: 1,
      y: 0,
      scale: 1,
      transition: {
        duration: 0.5,
        ease: [0.33, 1, 0.68, 1],
      }
    }
  };
  
  // 抖动动画
  const shakeAnimation = animated ? {
    x: [0, -3, 5, -5, 3, 0],
    transition: {
      duration: 0.5,
      ease: "easeInOut",
      times: [0, 0.2, 0.4, 0.6, 0.8, 1],
    }
  } : {};
  
  return (
    <motion.div
      className={`${variantStyles[variant]} overflow-hidden ${className}`}
      initial={animated ? "hidden" : "visible"}
      animate={animated ? "visible" : "visible"}
      variants={cardVariants}
      style={{ borderColor: 'rgba(157, 37, 32, 0.2)' }}
      {...(animated && { animate: shakeAnimation })}
    >
      {/* 渐变装饰效果 */}
      {variant === 'default' && (
        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-400 to-red-500" />
      )}
      
      <div className="px-6 py-5">
        <div className="flex items-start">
          {/* 错误图标 */}
          <div className="flex-shrink-0 mr-4">
            <div className="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30">
              <svg className="w-6 h-6 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
          
          {/* 错误内容 */}
          <div className="flex-1">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-playfair font-semibold text-gray-900 dark:text-white">
                {title}
              </h3>
              
              {/* 错误代码（如果有） */}
              {code && (
                <span className="text-xs font-medium font-montserrat bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 px-2 py-1 rounded-full">
                  {typeof code === 'string' ? code : `Error ${code}`}
                </span>
              )}
            </div>
            
            {/* 错误信息 */}
            <p className="mt-2 text-sm text-gray-600 dark:text-gray-300 font-montserrat leading-relaxed">
              {message}
            </p>
            
            {/* 操作按钮 */}
            {(retryAction || dismissAction) && (
              <div className="mt-4 flex items-center space-x-3">
                {retryAction && (
                  <motion.button
                    onClick={retryAction}
                    className="px-4 py-2 rounded-md bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium transition-all duration-200 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50"
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                  >
                    {retryLabel}
                  </motion.button>
                )}
                
                {dismissAction && (
                  <motion.button
                    onClick={dismissAction}
                    className="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200"
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                  >
                    {dismissLabel}
                  </motion.button>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </motion.div>
  );
};

export default ErrorCard; 
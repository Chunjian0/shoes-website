import React from 'react';
import { motion } from 'framer-motion';

interface EmptyStateProps {
  title?: string;
  message?: string;
  icon?: React.ReactNode;
  actions?: React.ReactNode;
  variant?: 'default' | 'compact' | 'luxury' | 'minimal';
  className?: string;
  animated?: boolean;
}

/**
 * 奢华风格的空状态组件
 * 用于显示无内容或无搜索结果状态
 */
const EmptyState: React.FC<EmptyStateProps> = ({
  title = 'No items found',
  message = 'We couldn\'t find any items matching your criteria.',
  icon,
  actions,
  variant = 'default',
  className = '',
  animated = true,
}) => {
  // 自定义内容动画变体
  const contentVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.6,
        ease: [0.33, 1, 0.68, 1],
      }
    }
  };

  // 图标动画变体
  const iconVariants = {
    hidden: { opacity: 0, scale: 0.8 },
    visible: {
      opacity: 1,
      scale: 1,
      transition: {
        duration: 0.7,
        ease: [0.33, 1, 0.68, 1],
      }
    }
  };

  // 浮动动画（仅限luxury变体）
  const floatAnimation = variant === 'luxury' ? {
    y: [0, -8, 0],
    transition: {
      repeat: Infinity,
      duration: 3,
      ease: "easeInOut",
    }
  } : {};

  // 默认图标
  const defaultIcon = (
    <svg className="w-16 h-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M20 12V8h-4V4h-8v4H4v4m1 0v6a2 2 0 002 2h10a2 2 0 002-2v-6M8 4v4h8V4m-4 8v6m-4-3h8" />
    </svg>
  );

  // 高端奢华图标
  const luxuryIcon = (
    <div className="relative">
      <svg className="w-24 h-24 text-amber-300 dark:text-amber-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="1" fill="none" />
        <path d="M8 12h8M12 8v8" stroke="currentColor" strokeWidth="1" strokeLinecap="round" />
        <circle cx="12" cy="12" r="3" stroke="currentColor" strokeWidth="1" fill="none" />
      </svg>
      <div className="absolute inset-0 bg-gradient-to-tr from-amber-200/30 to-transparent rounded-full blur-xl" />
    </div>
  );

  // 选择变体样式
  const variantStyles = {
    default: {
      container: "py-12 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900",
      title: "text-xl font-playfair font-semibold text-gray-800 dark:text-white",
      message: "text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto",
      icon: defaultIcon,
    },
    compact: {
      container: "py-8 rounded-lg border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900",
      title: "text-lg font-medium text-gray-800 dark:text-white",
      message: "text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto",
      icon: defaultIcon,
    },
    luxury: {
      container: "py-16 rounded-2xl bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 shadow-sm",
      title: "text-2xl font-playfair font-bold text-gray-900 dark:text-white",
      message: "text-base font-montserrat text-gray-600 dark:text-gray-300 max-w-lg mx-auto",
      icon: luxuryIcon,
    },
    minimal: {
      container: "py-8 bg-transparent",
      title: "text-base font-medium text-gray-700 dark:text-gray-300",
      message: "text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto",
      icon: defaultIcon,
    },
  };

  // 选择当前变体样式
  const currentStyle = variantStyles[variant];

  return (
    <div className={`text-center px-4 ${currentStyle.container} ${className}`}>
      {/* 图标 */}
      <motion.div 
        className="mb-4 flex justify-center"
        initial={animated ? "hidden" : "visible"}
        animate={animated ? { ...floatAnimation, scale: 1, opacity: 1 } : "visible"}
        variants={iconVariants}
      >
        {icon || currentStyle.icon}
      </motion.div>
      
      {/* 内容区域 */}
      <motion.div
        initial={animated ? "hidden" : "visible"}
        animate="visible"
        variants={contentVariants}
      >
        <h3 className={`${currentStyle.title} mb-2`}>{title}</h3>
        <p className={`${currentStyle.message} mb-6`}>{message}</p>
        
        {/* 操作按钮 */}
        {actions && (
          <div className="flex justify-center space-x-4">
            {actions}
          </div>
        )}
      </motion.div>
    </div>
  );
};

export default EmptyState; 
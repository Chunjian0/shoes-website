import React from 'react';
import { motion } from 'framer-motion';

interface SkeletonProps {
  variant?: 'text' | 'rectangle' | 'circle' | 'product-card' | 'image' | 'button';
  width?: string | number;
  height?: string | number;
  className?: string;
  animation?: 'pulse' | 'wave' | 'shimmer' | 'none';
  theme?: 'light' | 'dark' | 'gold';
  repeat?: number;
  responsive?: boolean;
}

/**
 * 高级骨架屏组件
 * 在内容加载期间显示占位元素，提供多种动画效果
 */
const Skeleton: React.FC<SkeletonProps> = ({
  variant = 'rectangle',
  width,
  height,
  className = '',
  animation = 'shimmer',
  theme = 'light',
  repeat = 1,
  responsive = false,
}) => {
  // 主题样式
  const themeStyles = {
    light: {
      base: 'bg-gray-200 dark:bg-gray-700',
      shimmer: 'from-gray-200 via-gray-100 to-gray-200 dark:from-gray-700 dark:via-gray-600 dark:to-gray-700',
    },
    dark: {
      base: 'bg-gray-700 dark:bg-gray-800',
      shimmer: 'from-gray-700 via-gray-600 to-gray-700 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800',
    },
    gold: {
      base: 'bg-amber-100 dark:bg-amber-900/30',
      shimmer: 'from-amber-100 via-amber-50/70 to-amber-100 dark:from-amber-900/30 dark:via-amber-800/20 dark:to-amber-900/30',
    },
  };

  // 变体默认尺寸和样式
  const variantConfig = {
    text: {
      width: width || '100%',
      height: height || '1rem',
      className: 'rounded',
    },
    rectangle: {
      width: width || '100%',
      height: height || '100px',
      className: 'rounded-md',
    },
    circle: {
      width: width || '40px',
      height: height || '40px',
      className: 'rounded-full',
    },
    'product-card': {
      width: width || '280px',
      height: height || '420px',
      className: 'rounded-xl overflow-hidden',
    },
    image: {
      width: width || '100%',
      height: height || '200px',
      className: 'rounded-lg',
    },
    button: {
      width: width || '120px',
      height: height || '40px',
      className: 'rounded-md',
    },
  };

  // 动画效果
  const getAnimationStyle = () => {
    if (animation === 'none') return {};

    if (animation === 'pulse') {
      return {
        animate: {
          opacity: [0.6, 0.8, 0.6],
        },
        transition: {
          repeat: Infinity,
          duration: 1.5,
          ease: [0.33, 1, 0.68, 1],
        },
      };
    }

    if (animation === 'wave') {
      return {
        initial: { opacity: 0.5 },
        animate: {
          opacity: [0.5, 0.8, 0.5],
        },
        transition: {
          repeat: Infinity,
          duration: 1.5,
          ease: "easeInOut",
        },
      };
    }

    // 默认使用shimmer效果，通过CSS处理
    return {};
  };

  // 构建基础样式
  const baseStyle = {
    width: variantConfig[variant].width,
    height: variantConfig[variant].height,
  };

  // 生成多个骨架元素
  const renderSkeleton = (index: number) => {
    const sharedClasses = `${themeStyles[theme].base} ${variantConfig[variant].className} ${className}`;
    
    // 对于 shimmer 动画，使用渐变背景
    if (animation === 'shimmer') {
      return (
        <div
          key={index}
          className={`relative overflow-hidden ${sharedClasses}`}
          style={baseStyle}
        >
          <div className="absolute inset-0 z-0">
            <div
              className={`absolute inset-0 z-10 transform -translate-x-full animate-shimmer bg-gradient-to-r ${themeStyles[theme].shimmer}`}
              style={{ animationDuration: '2s' }}
            />
          </div>
        </div>
      );
    }

    // 对于其他动画类型，使用 framer-motion
    const animationProps = getAnimationStyle();
    return (
      <motion.div
        key={index}
        className={sharedClasses}
        style={baseStyle}
        animate={animationProps.animate}
        transition={animationProps.transition}
        initial={animationProps.initial}
      />
    );
  };

  // 渲染产品卡片特殊骨架屏
  if (variant === 'product-card') {
    return (
      <div
        className={`${themeStyles[theme].base} rounded-xl overflow-hidden ${className}`}
        style={baseStyle}
      >
        {/* 图片区域 */}
        <div className="h-2/3 relative overflow-hidden">
          <div
            className={`absolute inset-0 z-10 transform -translate-x-full animate-shimmer bg-gradient-to-r ${themeStyles[theme].shimmer}`}
            style={{ animationDuration: '2s' }}
          />
        </div>
        
        {/* 内容区域 */}
        <div className="p-4 space-y-3">
          <div className="h-5 w-3/4 rounded relative overflow-hidden">
            <div
              className={`absolute inset-0 z-10 transform -translate-x-full animate-shimmer bg-gradient-to-r ${themeStyles[theme].shimmer}`}
              style={{ animationDuration: '2s', animationDelay: '0.2s' }}
            />
          </div>
          <div className="h-4 w-1/2 rounded relative overflow-hidden">
            <div
              className={`absolute inset-0 z-10 transform -translate-x-full animate-shimmer bg-gradient-to-r ${themeStyles[theme].shimmer}`}
              style={{ animationDuration: '2s', animationDelay: '0.4s' }}
            />
          </div>
          <div className="h-6 w-2/5 rounded relative overflow-hidden">
            <div
              className={`absolute inset-0 z-10 transform -translate-x-full animate-shimmer bg-gradient-to-r ${themeStyles[theme].shimmer}`}
              style={{ animationDuration: '2s', animationDelay: '0.6s' }}
            />
          </div>
        </div>
      </div>
    );
  }

  // 响应式容器
  if (responsive) {
    return (
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {Array.from({ length: repeat }).map((_, index) => renderSkeleton(index))}
      </div>
    );
  }

  // 多个重复元素
  if (repeat > 1) {
    return (
      <div className="flex flex-col space-y-2">
        {Array.from({ length: repeat }).map((_, index) => renderSkeleton(index))}
      </div>
    );
  }

  // 单个元素
  return renderSkeleton(0);
};

// 添加自定义CSS动画
const skeletonStyles = `
  @keyframes shimmer {
    0% {
      transform: translateX(-100%);
    }
    100% {
      transform: translateX(100%);
    }
  }
  
  .animate-shimmer {
    animation: shimmer 2s infinite linear;
  }
`;

// 将动画样式添加到文档头部
if (typeof document !== 'undefined') {
  const style = document.createElement('style');
  style.textContent = skeletonStyles;
  style.setAttribute('id', 'skeleton-styles');
  
  if (!document.getElementById('skeleton-styles')) {
    document.head.appendChild(style);
  }
}

export default Skeleton; 
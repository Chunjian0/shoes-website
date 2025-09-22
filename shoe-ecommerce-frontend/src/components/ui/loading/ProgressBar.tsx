import React, { useEffect, useState } from 'react';
import { motion, useAnimationControls } from 'framer-motion';

interface ProgressBarProps {
  value?: number;
  indeterminate?: boolean;
  variant?: 'primary' | 'secondary' | 'gold' | 'minimal';
  height?: number;
  showValue?: boolean;
  valuePosition?: 'inside' | 'right' | 'top';
  className?: string;
  rounded?: 'none' | 'sm' | 'md' | 'lg' | 'full';
  animated?: boolean;
  glowEffect?: boolean;
  label?: string;
}

/**
 * 进度条组件
 * 支持确定进度和不确定进度模式，提供多种样式和动画效果
 */
const ProgressBar: React.FC<ProgressBarProps> = ({
  value = 0,
  indeterminate = false,
  variant = 'primary',
  height = 4,
  showValue = false,
  valuePosition = 'right',
  className = '',
  rounded = 'md',
  animated = true,
  glowEffect = true,
  label,
}) => {
  // 处理值的范围
  const progress = Math.max(0, Math.min(100, value));
  const [animatedProgress, setAnimatedProgress] = useState(0);
  const controls = useAnimationControls();
  
  // 样式配置
  const variantStyles = {
    primary: {
      track: 'bg-gray-200 dark:bg-gray-700',
      bar: 'bg-black dark:bg-white',
      glow: 'rgba(0, 0, 0, 0.4)',
      text: 'text-black dark:text-white',
    },
    secondary: {
      track: 'bg-gray-200 dark:bg-gray-700',
      bar: 'bg-gray-500 dark:bg-gray-400',
      glow: 'rgba(75, 75, 75, 0.4)',
      text: 'text-gray-700 dark:text-gray-300',
    },
    gold: {
      track: 'bg-amber-100 dark:bg-amber-900/30',
      bar: 'bg-gradient-to-r from-amber-500 to-amber-600 dark:from-amber-400 dark:to-amber-500',
      glow: 'rgba(217, 119, 6, 0.5)',
      text: 'text-amber-700 dark:text-amber-400',
    },
    minimal: {
      track: 'bg-gray-100 dark:bg-gray-800',
      bar: 'bg-gray-400 dark:bg-gray-500',
      glow: 'transparent',
      text: 'text-gray-500 dark:text-gray-400',
    },
  };
  
  // 圆角样式
  const roundedStyles = {
    none: '',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full',
  };
  
  // 不确定进度动画
  useEffect(() => {
    if (indeterminate && animated) {
      controls.start({
        x: ['0%', '100%'],
        transition: {
          repeat: Infinity,
          repeatType: 'reverse',
          duration: 2.5,
          ease: [0.4, 0.0, 0.2, 1],
        },
      });
    } else if (animated) {
      // 平滑动画到目标进度
      setAnimatedProgress(progress);
    }
  }, [indeterminate, progress, animated, controls]);
  
  // 闪光效果动画变体
  const shimmerVariants = {
    initial: { x: '-100%', opacity: 0 },
    animate: {
      x: '100%',
      opacity: [0, 0.5, 0],
      transition: {
        repeat: Infinity,
        duration: 2,
        ease: [0.4, 0.0, 0.2, 1],
      },
    },
  };
  
  // 根据进度位置渲染不同布局
  const renderProgressTrack = () => (
    <div
      className={`relative overflow-hidden ${variantStyles[variant].track} ${roundedStyles[rounded]}`}
      style={{ height: `${height}px` }}
    >
      {/* 进度条 */}
      {indeterminate ? (
        <motion.div
          className={`h-full ${variantStyles[variant].bar} ${roundedStyles[rounded]}`}
          style={{ width: '30%', originX: 0 }}
          animate={controls}
        />
      ) : (
        <motion.div
          className={`h-full ${variantStyles[variant].bar} ${roundedStyles[rounded]}`}
          style={{
            width: animated ? `${animatedProgress}%` : `${progress}%`,
            boxShadow: glowEffect ? `0 0 8px ${variantStyles[variant].glow}` : 'none',
          }}
          animate={animated ? { width: `${progress}%` } : undefined}
          transition={animated ? { duration: 0.6, ease: [0.4, 0.0, 0.2, 1] } : undefined}
        >
          {/* 内部百分比 */}
          {showValue && valuePosition === 'inside' && progress > 10 && (
            <div className="h-full flex items-center justify-end pr-2">
              <span className="text-xs font-medium text-white">{`${progress}%`}</span>
            </div>
          )}
          
          {/* 闪光效果 */}
          {glowEffect && variant === 'gold' && animated && (
            <motion.div
              className="absolute top-0 bottom-0 w-20 bg-gradient-to-r from-transparent via-white/30 to-transparent"
              initial="initial"
              animate="animate"
              variants={shimmerVariants}
            />
          )}
        </motion.div>
      )}
    </div>
  );
  
  return (
    <div className={`w-full ${className}`}>
      {/* 顶部标签和值 */}
      {(label || (showValue && valuePosition === 'top')) && (
        <div className="flex justify-between items-center mb-1">
          {label && (
            <span className={`text-sm font-medium ${variantStyles[variant].text}`}>
              {label}
            </span>
          )}
          {showValue && valuePosition === 'top' && (
            <span className={`text-sm font-medium ${variantStyles[variant].text}`}>
              {`${progress}%`}
            </span>
          )}
        </div>
      )}
      
      {/* 主进度条 */}
      <div className="flex items-center">
        <div className="flex-grow">
          {renderProgressTrack()}
        </div>
        
        {/* 右侧百分比 */}
        {showValue && valuePosition === 'right' && (
          <span className={`ml-3 text-sm font-medium ${variantStyles[variant].text}`}>
            {`${progress}%`}
          </span>
        )}
      </div>
    </div>
  );
};

export default ProgressBar; 
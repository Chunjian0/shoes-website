import React, { useEffect } from 'react';
import { motion, useAnimation } from 'framer-motion';

interface SuccessIndicatorProps {
  message?: string;
  title?: string;
  variant?: 'standard' | 'compact' | 'luxury' | 'minimal';
  onComplete?: () => void;
  duration?: number;
  size?: 'sm' | 'md' | 'lg';
  animationOnly?: boolean;
  className?: string;
}

/**
 * 奢华风格的成功状态指示器组件
 * 提供多种样式和尺寸选项，带有优雅的动画效果
 */
const SuccessIndicator: React.FC<SuccessIndicatorProps> = ({
  message,
  title,
  variant = 'standard',
  onComplete,
  duration = 2000,
  size = 'md',
  animationOnly = false,
  className = '',
}) => {
  const checkmarkControls = useAnimation();
  const circleControls = useAnimation();
  const containerControls = useAnimation();
  
  // 尺寸配置
  const sizeConfig = {
    sm: {
      container: 'w-8 h-8',
      circleSize: 30,
      checkmarkPath: 'M5 12l4 4L19 7',
      strokeWidth: 2.5,
    },
    md: {
      container: 'w-16 h-16',
      circleSize: 60,
      checkmarkPath: 'M10 24l8 8L38 14',
      strokeWidth: 3,
    },
    lg: {
      container: 'w-24 h-24',
      circleSize: 90,
      checkmarkPath: 'M15 36l12 12L57 21',
      strokeWidth: 4,
    },
  };
  
  // 变体样式配置
  const variantConfig = {
    standard: {
      container: 'bg-white dark:bg-gray-900 rounded-lg shadow-md p-6',
      circle: 'stroke-green-500 dark:stroke-green-400',
      checkmark: 'stroke-white',
      circleFill: 'fill-green-500 dark:fill-green-400',
      title: 'text-gray-900 dark:text-white font-semibold',
      message: 'text-gray-600 dark:text-gray-300',
    },
    compact: {
      container: 'bg-white dark:bg-gray-900 rounded p-4',
      circle: 'stroke-green-500 dark:stroke-green-400',
      checkmark: 'stroke-white',
      circleFill: 'fill-green-500 dark:fill-green-400',
      title: 'text-gray-900 dark:text-white font-medium',
      message: 'text-gray-600 dark:text-gray-300',
    },
    luxury: {
      container: 'bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 rounded-xl shadow-lg p-8',
      circle: 'stroke-amber-500 dark:stroke-amber-400',
      checkmark: 'stroke-white',
      circleFill: 'fill-amber-500 dark:fill-amber-400',
      title: 'text-gray-900 dark:text-white font-playfair font-bold',
      message: 'text-gray-700 dark:text-gray-200 font-montserrat',
    },
    minimal: {
      container: 'bg-transparent',
      circle: 'stroke-green-600 dark:stroke-green-400',
      checkmark: 'stroke-white',
      circleFill: 'fill-green-600 dark:fill-green-400',
      title: 'text-gray-800 dark:text-white font-medium',
      message: 'text-gray-600 dark:text-gray-300',
    },
  };
  
  // 动画序列
  useEffect(() => {
    const sequence = async () => {
      // 1. 绘制圆形
      await circleControls.start({
        opacity: 1,
        pathLength: 1,
        transition: { duration: 0.5, ease: "easeInOut" }
      });
      
      // 2. 填充圆形 - 使用 variantConfig 中的颜色
      // 提取颜色值，需要一种方法来获取 Tailwind 类对应的颜色
      // 假设我们有一个函数 getTailwindColor 或直接使用颜色值
      // 暂时使用一个占位符 'currentColor'，这需要根据实际情况调整
      const fillColor = variantConfig[variant].circleFill.includes('amber') ? 'rgba(245, 158, 11, 1)' : 'rgba(34, 197, 94, 1)'; // Example: Green-500 or Amber-500
      await circleControls.start({
        fill: fillColor, // 使用实际颜色值
        transition: { duration: 0.2 }
      });
      
      // 3. 绘制对勾
      await checkmarkControls.start({
        pathLength: 1,
        opacity: 1,
        transition: { duration: 0.3, ease: "easeOut" }
      });
      
      // 4. 如果设置了持续时间和完成回调
      if (duration && onComplete) {
        setTimeout(() => {
          // 淡出容器
          containerControls.start({
            opacity: 0,
            scale: 0.9,
            transition: { duration: 0.3 }
          }).then(() => {
            onComplete();
          });
        }, duration);
      }
    };
    
    sequence();
  }, [checkmarkControls, circleControls, containerControls, duration, onComplete]);
  
  // 渲染仅动画模式
  if (animationOnly) {
    return (
      <motion.div 
        className={`relative ${sizeConfig[size].container} ${className}`}
        animate={containerControls}
        initial={{ opacity: 1, scale: 1 }}
      >
        <svg 
          viewBox={`0 0 ${sizeConfig[size].circleSize} ${sizeConfig[size].circleSize}`} 
          className="w-full h-full"
        >
          <motion.circle
            cx={sizeConfig[size].circleSize / 2}
            cy={sizeConfig[size].circleSize / 2}
            r={(sizeConfig[size].circleSize / 2) - 2}
            strokeWidth={sizeConfig[size].strokeWidth}
            className={`${variantConfig[variant].circle}`}
            fill="transparent"
            initial={{ pathLength: 0, opacity: 0, fill: 'transparent' }}
            animate={circleControls}
            strokeLinecap="round"
          />
          <motion.path
            d={sizeConfig[size].checkmarkPath}
            className={`${variantConfig[variant].checkmark}`}
            fill="transparent"
            strokeWidth={sizeConfig[size].strokeWidth}
            strokeLinecap="round"
            strokeLinejoin="round"
            initial={{ pathLength: 0, opacity: 0 }}
            animate={checkmarkControls}
          />
        </svg>
      </motion.div>
    );
  }
  
  // 渲染带文本的完整组件
  return (
    <motion.div 
      className={`flex flex-col items-center text-center ${variantConfig[variant].container} ${className}`}
      animate={containerControls}
      initial={{ opacity: 1, scale: 1 }}
    >
      <div className={`relative ${sizeConfig[size].container} mb-4`}>
        <svg 
          viewBox={`0 0 ${sizeConfig[size].circleSize} ${sizeConfig[size].circleSize}`} 
          className="w-full h-full"
        >
          <motion.circle
            cx={sizeConfig[size].circleSize / 2}
            cy={sizeConfig[size].circleSize / 2}
            r={(sizeConfig[size].circleSize / 2) - 2}
            strokeWidth={sizeConfig[size].strokeWidth}
            className={`${variantConfig[variant].circle}`}
            fill="transparent"
            initial={{ pathLength: 0, opacity: 0, fill: 'transparent' }}
            animate={circleControls}
            strokeLinecap="round"
          />
          <motion.path
            d={sizeConfig[size].checkmarkPath}
            className={`${variantConfig[variant].checkmark}`}
            fill="transparent"
            strokeWidth={sizeConfig[size].strokeWidth}
            strokeLinecap="round"
            strokeLinejoin="round"
            initial={{ pathLength: 0, opacity: 0 }}
            animate={checkmarkControls}
          />
        </svg>
      </div>
      
      {title && (
        <motion.h3 
          className={`text-lg ${variantConfig[variant].title} mb-1`}
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.7, duration: 0.3 }}
        >
          {title}
        </motion.h3>
      )}
      
      {message && (
        <motion.p 
          className={`text-sm ${variantConfig[variant].message}`}
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.8, duration: 0.3 }}
        >
          {message}
        </motion.p>
      )}
    </motion.div>
  );
};

export default SuccessIndicator; 
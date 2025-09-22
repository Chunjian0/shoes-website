import React from 'react';
import { motion } from 'framer-motion';

interface InlineLoaderProps {
  size?: 'xs' | 'sm' | 'md' | 'lg';
  color?: 'white' | 'black' | 'gold' | 'gray';
  className?: string;
  text?: string;
  textPosition?: 'left' | 'right';
}

/**
 * 内联加载指示器组件
 * 适用于按钮或其他交互元素的加载状态
 */
const InlineLoader: React.FC<InlineLoaderProps> = ({
  size = 'sm',
  color = 'white',
  className = '',
  text,
  textPosition = 'right',
}) => {
  // 尺寸配置
  const sizeConfig = {
    xs: {
      loaderSize: 'w-3 h-3',
      textSize: 'text-xs',
      gap: 'gap-1',
    },
    sm: {
      loaderSize: 'w-4 h-4',
      textSize: 'text-sm',
      gap: 'gap-1.5',
    },
    md: {
      loaderSize: 'w-5 h-5',
      textSize: 'text-sm',
      gap: 'gap-2',
    },
    lg: {
      loaderSize: 'w-6 h-6',
      textSize: 'text-base',
      gap: 'gap-2.5',
    },
  };

  // 颜色配置
  const colorConfig = {
    white: {
      dot: 'bg-white',
      text: 'text-white',
    },
    black: {
      dot: 'bg-black dark:bg-white',
      text: 'text-black dark:text-white',
    },
    gold: {
      dot: 'bg-amber-500 dark:bg-amber-400',
      text: 'text-amber-600 dark:text-amber-400',
    },
    gray: {
      dot: 'bg-gray-400 dark:bg-gray-500',
      text: 'text-gray-500 dark:text-gray-400',
    },
  };

  // 粒子动画参数
  const dotVariants = {
    initial: { scale: 0.8, opacity: 0.4 },
    animate: { 
      scale: 1, 
      opacity: 1,
      transition: {
        duration: 0.5,
        repeat: Infinity,
        repeatType: "reverse" as const,
        ease: [0.33, 1, 0.68, 1],
      }
    },
  };

  // 确定延迟时间
  const getDelay = (index: number) => index * 0.15;

  // 渲染加载指示器
  const renderDots = () => (
    <div className={`flex items-center ${sizeConfig[size].gap}`}>
      {[0, 1, 2].map((index) => (
        <motion.div
          key={index}
          className={`rounded-full ${colorConfig[color].dot} ${sizeConfig[size].loaderSize}`}
          initial="initial"
          animate="animate"
          variants={dotVariants}
          transition={{
            delay: getDelay(index),
          }}
        />
      ))}
    </div>
  );

  // 如果有文本，渲染带文本的布局
  if (text) {
    return (
      <div className={`inline-flex items-center ${sizeConfig[size].gap} ${className}`}>
        {textPosition === 'left' && (
          <span className={`${colorConfig[color].text} ${sizeConfig[size].textSize} font-medium`}>
            {text}
          </span>
        )}
        
        {renderDots()}
        
        {textPosition === 'right' && (
          <span className={`${colorConfig[color].text} ${sizeConfig[size].textSize} font-medium`}>
            {text}
          </span>
        )}
      </div>
    );
  }

  // 否则只渲染指示器
  return (
    <div className={className}>
      {renderDots()}
    </div>
  );
};

export default InlineLoader; 
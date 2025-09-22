import React from 'react';
import { motion } from 'framer-motion';

interface SkeletonLoaderProps {
  count?: number;
  type?: 'cart-item' | 'product-card' | 'order-item' | 'text-line' | 'circle';
  width?: string;
  height?: string;
  className?: string;
  animated?: boolean;
}

const SkeletonLoader: React.FC<SkeletonLoaderProps> = ({
  count = 1,
  type = 'text-line',
  width,
  height,
  className = '',
  animated = true
}) => {
  // 创建骨架元素数组
  const skeletons = Array.from({ length: count }, (_, i) => i);
  
  // 动画变体
  const pulseVariants = {
    initial: { opacity: 0.6 },
    animate: { 
      opacity: [0.6, 0.8, 0.6],
      transition: { 
        repeat: Infinity, 
        duration: 1.5,
        ease: "easeInOut"
      }
    }
  };
  
  // 获取尺寸样式
  const getSizeStyles = () => {
    if (width && height) {
      return { width, height };
    }
    
    switch (type) {
      case 'cart-item':
        return { width: '100%', height: '120px' };
      case 'product-card':
        return { width: '100%', height: '320px' };
      case 'order-item':
        return { width: '100%', height: '80px' };
      case 'circle':
        return { width: '40px', height: '40px' };
      case 'text-line':
      default:
        return { width: '100%', height: '20px' };
    }
  };
  
  // 渲染不同类型的骨架屏
  const renderSkeleton = (index: number) => {
    const sizeStyles = getSizeStyles();
    
    switch (type) {
      case 'cart-item':
        return (
          <div 
            key={index}
            className="relative bg-white rounded-lg p-4 overflow-hidden border border-gray-200"
            style={{ height: sizeStyles.height }}
          >
            <div className="flex items-center">
              <motion.div 
                className="rounded-md bg-gray-200 flex-shrink-0"
                style={{ width: '80px', height: '80px' }}
                variants={animated ? pulseVariants : {}}
                initial="initial"
                animate={animated ? "animate" : "initial"}
              />
              <div className="ml-4 flex-grow">
                <motion.div 
                  className="h-4 bg-gray-200 rounded w-3/4 mb-2"
                  variants={animated ? pulseVariants : {}}
                  initial="initial"
                  animate={animated ? "animate" : "initial"}
                />
                <motion.div 
                  className="h-3 bg-gray-200 rounded w-1/2 mb-3"
                  variants={animated ? pulseVariants : {}}
                  initial="initial"
                  animate={animated ? "animate" : "initial"}
                />
                <div className="flex justify-between items-center">
                  <motion.div 
                    className="h-6 bg-gray-200 rounded w-20"
                    variants={animated ? pulseVariants : {}}
                    initial="initial"
                    animate={animated ? "animate" : "initial"}
                  />
                  <motion.div 
                    className="h-8 bg-gray-200 rounded-full w-24"
                    variants={animated ? pulseVariants : {}}
                    initial="initial"
                    animate={animated ? "animate" : "initial"}
                  />
                </div>
              </div>
            </div>
          </div>
        );
      
      case 'product-card':
        return (
          <div 
            key={index}
            className="bg-white rounded-xl overflow-hidden shadow-sm"
            style={{ width: sizeStyles.width, height: sizeStyles.height }}
          >
            <motion.div 
              className="bg-gray-200 w-full"
              style={{ height: '65%' }}
              variants={animated ? pulseVariants : {}}
              initial="initial"
              animate={animated ? "animate" : "initial"}
            />
            <div className="p-4">
              <motion.div 
                className="h-5 bg-gray-200 rounded w-3/4 mb-2"
                variants={animated ? pulseVariants : {}}
                initial="initial"
                animate={animated ? "animate" : "initial"}
              />
              <motion.div 
                className="h-4 bg-gray-200 rounded w-1/2 mb-3"
                variants={animated ? pulseVariants : {}}
                initial="initial"
                animate={animated ? "animate" : "initial"}
              />
              <motion.div 
                className="h-6 bg-gray-200 rounded w-1/3"
                variants={animated ? pulseVariants : {}}
                initial="initial"
                animate={animated ? "animate" : "initial"}
              />
            </div>
          </div>
        );
      
      case 'order-item':
        return (
          <div 
            key={index}
            className="bg-white rounded-lg p-3 overflow-hidden border border-gray-200"
            style={{ height: sizeStyles.height }}
          >
            <div className="flex justify-between items-center">
              <div className="flex-grow">
                <motion.div 
                  className="h-4 bg-gray-200 rounded w-1/2 mb-2"
                  variants={animated ? pulseVariants : {}}
                  initial="initial"
                  animate={animated ? "animate" : "initial"}
                />
                <motion.div 
                  className="h-3 bg-gray-200 rounded w-3/4 mb-1"
                  variants={animated ? pulseVariants : {}}
                  initial="initial"
                  animate={animated ? "animate" : "initial"}
                />
                <motion.div 
                  className="h-3 bg-gray-200 rounded w-1/4"
                  variants={animated ? pulseVariants : {}}
                  initial="initial"
                  animate={animated ? "animate" : "initial"}
                />
              </div>
              <motion.div 
                className="h-8 bg-gray-200 rounded-full w-24"
                variants={animated ? pulseVariants : {}}
                initial="initial"
                animate={animated ? "animate" : "initial"}
              />
            </div>
          </div>
        );
        
      case 'circle':
        return (
          <motion.div 
            key={index}
            className="bg-gray-200 rounded-full"
            style={{ width: sizeStyles.width, height: sizeStyles.height }}
            variants={animated ? pulseVariants : {}}
            initial="initial"
            animate={animated ? "animate" : "initial"}
          />
        );
      
      case 'text-line':
      default:
        return (
          <motion.div 
            key={index}
            className={`bg-gray-200 rounded ${className}`}
            style={{ width: sizeStyles.width, height: sizeStyles.height }}
            variants={animated ? pulseVariants : {}}
            initial="initial"
            animate={animated ? "animate" : "initial"}
          />
        );
    }
  };
  
  return (
    <div className="skeleton-loader">
      {skeletons.map((_, index) => renderSkeleton(index))}
    </div>
  );
};

export default SkeletonLoader; 
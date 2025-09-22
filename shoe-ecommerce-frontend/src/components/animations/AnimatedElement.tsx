import React, { useEffect, useRef, useState } from 'react';

// 定义动画类型
export type AnimationType = 
  | 'fade-in' 
  | 'slide-up' 
  | 'slide-down' 
  | 'slide-left' 
  | 'slide-right' 
  | 'zoom-in' 
  | 'zoom-out' 
  | 'bounce'
  | 'rotate'
  | 'flip'
  | 'parallax-slow'
  | 'parallax-fast'
  | 'float'
  | '3d-tilt'
  | 'scale-in-bounce'
  | 'none';

// 动画选项接口
export interface AnimationOptions {
  delay?: number;
  duration?: number;
  distance?: string;
  easing?: string;
  threshold?: number;
  once?: boolean;
  intensity?: number; // 视差强度
  perspective?: number; // 3D视角
}

interface AnimatedElementProps {
  children: React.ReactNode;
  type: AnimationType;
  options?: AnimationOptions;
  className?: string;
  disabled?: boolean;
}

/**
 * 通用动画组件，支持多种动画类型和自定义选项
 */
const AnimatedElement: React.FC<AnimatedElementProps> = ({
  children,
  type,
  options = {},
  className = '',
  disabled = false,
}) => {
  const [isVisible, setIsVisible] = useState(false);
  const [isAnimated, setIsAnimated] = useState(false);
  const [scrollPosition, setScrollPosition] = useState(0);
  const ref = useRef<HTMLDivElement>(null);
  
  const {
    delay = 0,
    duration = 0.6,
    distance = '40px',
    easing = 'cubic-bezier(0.25, 0.1, 0.25, 1.0)',
    threshold = 0.1,
    intensity = 0.1, // 默认视差强度
    perspective = 1000, // 默认3D视角
  } = options;
  
  // 处理滚动事件（用于视差效果）
  useEffect(() => {
    const needsScrollTracking = type === 'parallax-slow' || type === 'parallax-fast' || type === '3d-tilt';
    
    if (needsScrollTracking) {
      const handleScroll = () => {
        setScrollPosition(window.scrollY);
      };
      
      window.addEventListener('scroll', handleScroll);
      return () => window.removeEventListener('scroll', handleScroll);
    }
  }, [type]);
  
  useEffect(() => {
    // 如果禁用动画，直接设置为可见
    if (disabled) {
      setIsVisible(true);
      return;
    }
    
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !isAnimated) {
          // 设置延迟显示
          setTimeout(() => {
            setIsVisible(true);
            setIsAnimated(true);
          }, delay * 1000);
          
          // 该元素已经被观察到，不需要再次观察
          if (ref.current) {
            observer.unobserve(ref.current);
          }
        }
      },
      {
        root: null, // 相对于视口
        rootMargin: '0px',
        threshold, // 当10%的元素可见时触发
      }
    );
    
    // 开始观察元素
    if (ref.current) {
      observer.observe(ref.current);
    }
    
    // 清理函数
    return () => {
      if (ref.current) {
        observer.unobserve(ref.current);
      }
    };
  }, [delay, disabled, isAnimated, threshold]);
  
  // 处理视差效果
  const getParallaxStyle = (): React.CSSProperties => {
    if (!ref.current) return {};
    
    // 获取元素在视口中的位置
    const rect = ref.current.getBoundingClientRect();
    const elementMiddle = rect.top + rect.height / 2;
    const viewportMiddle = window.innerHeight / 2;
    const distanceFromCenter = elementMiddle - viewportMiddle;
    
    // 计算视差偏移
    const parallaxFactor = type === 'parallax-slow' ? intensity : intensity * 2;
    const yOffset = distanceFromCenter * parallaxFactor;
    
    return {
      transform: `translateY(${-yOffset}px)`,
      transition: 'transform 0.1s ease-out',
    };
  };
  
  // 处理3D倾斜效果
  const get3DTiltStyle = (): React.CSSProperties => {
    if (!ref.current || !isVisible) return {};
    
    const rect = ref.current.getBoundingClientRect();
    const x = (window.innerWidth / 2 - rect.left - rect.width / 2) / 20;
    const y = (window.innerHeight / 2 - rect.top - rect.height / 2) / 20;
    
    return {
      transform: `perspective(${perspective}px) rotateX(${y}deg) rotateY(${-x}deg)`,
      transition: 'transform 0.5s ease-out',
    };
  };
  
  // 生成动画样式
  const getAnimationStyle = (): React.CSSProperties => {
    if (disabled) {
      return {};
    }
    
    // 基础样式
    const baseStyle: React.CSSProperties = {
      opacity: isVisible ? 1 : 0,
      transition: `all ${duration}s ${easing}`,
      transitionDelay: `${delay}s`,
    };
    
    // 对于某些效果，不需要初始透明度为0
    if (type === 'parallax-slow' || type === 'parallax-fast') {
      baseStyle.opacity = 1;
    }
    
    switch (type) {
      case 'fade-in':
        return {
          ...baseStyle,
        };
        
      case 'slide-up':
        return {
          ...baseStyle,
          transform: isVisible ? 'translateY(0)' : `translateY(${distance})`,
        };
        
      case 'slide-down':
        return {
          ...baseStyle,
          transform: isVisible ? 'translateY(0)' : `translateY(-${distance})`,
        };
        
      case 'slide-left':
        return {
          ...baseStyle,
          transform: isVisible ? 'translateX(0)' : `translateX(${distance})`,
        };
        
      case 'slide-right':
        return {
          ...baseStyle,
          transform: isVisible ? 'translateX(0)' : `translateX(-${distance})`,
        };
        
      case 'zoom-in':
        return {
          ...baseStyle,
          transform: isVisible ? 'scale(1)' : 'scale(0.8)',
        };
        
      case 'zoom-out':
        return {
          ...baseStyle,
          transform: isVisible ? 'scale(1)' : 'scale(1.2)',
        };
        
      case 'bounce':
        if (isVisible) {
          return {
            ...baseStyle,
            animation: `bounce ${duration}s ${easing}`,
          };
        }
        return baseStyle;
        
      case 'rotate':
        return {
          ...baseStyle,
          transform: isVisible ? 'rotate(0deg)' : 'rotate(-10deg)',
        };
        
      case 'flip':
        return {
          ...baseStyle,
          transform: isVisible ? 'perspective(1000px) rotateY(0)' : 'perspective(1000px) rotateY(90deg)',
        };
        
      case 'parallax-slow':
      case 'parallax-fast':
        return {
          ...baseStyle,
          ...getParallaxStyle(),
        };
        
      case 'float':
        if (isVisible) {
          return {
            ...baseStyle,
            animation: 'float 6s ease-in-out infinite',
          };
        }
        return baseStyle;
        
      case '3d-tilt':
        return {
          ...baseStyle,
          ...get3DTiltStyle(),
        };
        
      case 'scale-in-bounce':
        if (isVisible) {
          return {
            ...baseStyle,
            animation: 'scaleInBounce 0.9s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards',
            transformOrigin: 'center bottom',
          };
        }
        return {
          ...baseStyle,
          transform: 'scale(0.5)',
          transformOrigin: 'center bottom',
        };
        
      default:
        return baseStyle;
    }
  };
  
  // 添加CSS动画
  const animationStyles = `
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }
    
    @keyframes scaleInBounce {
      0% { transform: scale(0.5); }
      70% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-20px); }
      60% { transform: translateY(-10px); }
    }
  `;
  
  return (
    <>
      <style>{animationStyles}</style>
      <div
        ref={ref}
        className={className}
        style={getAnimationStyle()}
      >
        {children}
      </div>
    </>
  );
};

export default AnimatedElement; 
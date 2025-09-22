import React, { useState, useEffect, useRef, useCallback } from 'react';
// 使用try-catch导入，防止导入失败
let useInViewImported;
try {
  // 动态导入，防止编译时报错
  useInViewImported = require('react-intersection-observer').useInView;
} catch (e) {
  console.warn('react-intersection-observer not available, using fallback implementation');
  useInViewImported = null;
}

// 备选方案 - 如果useInView导入失败，提供一个简单的替代实现
// 使用自定义钩子模拟IntersectionObserver功能
const useCustomInView = (options = { threshold: 0.3 }) => {
  const [ref, setRef] = useState<HTMLElement | null>(null);
  const [inView, setInView] = useState(false);

  useEffect(() => {
    if (!ref) return;
    
    // 检查浏览器是否支持IntersectionObserver
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(
        ([entry]) => {
          setInView(entry.isIntersecting);
        },
        options
      );

      observer.observe(ref);
      return () => {
        observer.disconnect();
      };
    } else {
      // 降级方案：假设元素总是可见的
      setInView(true);
      return () => {};
    }
  }, [ref, options.threshold]);

  return { ref: setRef, inView };
};

// 使用可用的实现或备选方案
const useInViewSafe = useInViewImported || useCustomInView;

interface CarouselItem {
  id: number | string;
  imageUrl: string;
  title: string;
  subtitle?: string;
  buttonText?: string;
  buttonLink?: string;
  badgeText?: string;
}

interface CardCarouselProps {
  items: CarouselItem[];
  autoplay?: boolean;
  delay?: number;
  showNavigation?: boolean;
  showIndicators?: boolean;
  height?: string;
  enableTouchGestures?: boolean;
}

const CardCarousel: React.FC<CardCarouselProps> = ({
  items,
  autoplay = true,
  delay = 5000,
  showNavigation = true,
  showIndicators = true,
  height = "550px",
  enableTouchGestures = true,
}) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isPaused, setIsPaused] = useState(false);
  const [touchStart, setTouchStart] = useState(0);
  const [touchEnd, setTouchEnd] = useState(0);
  const [isTransitioning, setIsTransitioning] = useState(false);
  const [direction, setDirection] = useState<'prev' | 'next' | null>(null);
  const autoplayRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const carouselRef = useRef<HTMLDivElement | null>(null);
  
  // 使用安全版本的useInView
  const { ref: inViewRef, inView } = useInViewSafe({
    threshold: 0.3,
    triggerOnce: false
  });

  // 设置自动播放，只在视图中时播放
  useEffect(() => {
    if (!inView || !autoplay || isPaused || items.length <= 1) {
      if (autoplayRef.current) {
        clearTimeout(autoplayRef.current);
        autoplayRef.current = null;
      }
      return;
    }

    autoplayRef.current = setTimeout(() => {
      goToNextSlide();
    }, delay);

    return () => {
      if (autoplayRef.current) {
        clearTimeout(autoplayRef.current);
        autoplayRef.current = null;
      }
    };
  }, [autoplay, currentSlide, delay, isPaused, items.length, inView]);

  // 监听视口可见性变化
  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.hidden) {
        setIsPaused(true);
      } else {
        setIsPaused(false);
      }
    };

    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, []);

  // 使用 ResizeObserver 监听容器尺寸变化，优化响应式处理
  useEffect(() => {
    if (!carouselRef.current) return;
    
    const resizeObserver = new ResizeObserver(() => {
      // 容器尺寸变化时可以添加适配逻辑
      // 这里只是一个简单的示例
      if (window.innerWidth < 768) {
        // 移动设备上的适配
      } else {
        // 桌面设备上的适配
      }
    });
    
    resizeObserver.observe(carouselRef.current);
    
    return () => {
      resizeObserver.disconnect();
    };
  }, []);

  // 处理触摸开始事件
  const handleTouchStart = useCallback((e: React.TouchEvent) => {
    if (!enableTouchGestures) return;
    setTouchStart(e.touches[0].clientX);
  }, [enableTouchGestures]);

  // 处理触摸移动事件
  const handleTouchMove = useCallback((e: React.TouchEvent) => {
    if (!enableTouchGestures) return;
    setTouchEnd(e.touches[0].clientX);
  }, [enableTouchGestures]);

  // 处理触摸结束事件
  const handleTouchEnd = useCallback(() => {
    if (!enableTouchGestures || isTransitioning) return;
    
    const minSwipeDistance = 50;
    const touchDiff = touchStart - touchEnd;
    
    if (touchDiff > minSwipeDistance) {
      // 向左滑动，下一张
      goToNextSlide();
    } else if (touchDiff < -minSwipeDistance) {
      // 向右滑动，上一张
      goToPrevSlide();
    }
    
    setTouchStart(0);
    setTouchEnd(0);
  }, [enableTouchGestures, isTransitioning, touchStart, touchEnd]);

  // 跳转到下一张幻灯片
  const goToNextSlide = useCallback(() => {
    if (isTransitioning || items.length <= 1) return;
    
    setDirection('next');
    setIsTransitioning(true);
    setCurrentSlide((prevIndex) => (prevIndex + 1) % items.length);
    
    setTimeout(() => {
      setIsTransitioning(false);
      setDirection(null);
    }, 800);
  }, [isTransitioning, items.length]);

  // 跳转到上一张幻灯片
  const goToPrevSlide = useCallback(() => {
    if (isTransitioning || items.length <= 1) return;
    
    setDirection('prev');
    setIsTransitioning(true);
    setCurrentSlide((prevIndex) => (prevIndex - 1 + items.length) % items.length);
    
    setTimeout(() => {
      setIsTransitioning(false);
      setDirection(null);
    }, 800);
  }, [isTransitioning, items.length]);

  // 跳转到指定幻灯片
  const goToSlide = useCallback((index: number) => {
    if (isTransitioning || index === currentSlide || index < 0 || index >= items.length) return;
    
    setDirection(index > currentSlide ? 'next' : 'prev');
    setIsTransitioning(true);
    setCurrentSlide(index);
    
    setTimeout(() => {
      setIsTransitioning(false);
      setDirection(null);
    }, 800);
  }, [isTransitioning, currentSlide, items.length]);

  // 鼠标悬停暂停自动播放
  const handleMouseEnter = useCallback(() => {
    setIsPaused(true);
  }, []);

  // 鼠标离开恢复自动播放
  const handleMouseLeave = useCallback(() => {
    setIsPaused(false);
  }, []);

  // 计算卡片位置和样式的函数
  const getCardStyles = useCallback((index: number) => {
    // 根据当前幻灯片位置计算样式和位置
    const isActive = index === currentSlide;
    const isNext = (index === (currentSlide + 1) % items.length);
    const isNextNext = (index === (currentSlide + 2) % items.length);
    const isPrev = (index === (currentSlide - 1 + items.length) % items.length);
    const isPrevPrev = (index === (currentSlide - 2 + items.length) % items.length);
    const isHidden = !isActive && !isNext && !isNextNext && !isPrev && !isPrevPrev;
    
    // 创建基础类名
    const baseClasses = `
      absolute inset-0 transition-all duration-800 ease-out
      ${isTransitioning ? 'will-change-transform' : ''}
    `;
    
    // 根据方向和状态动态计算变换
    let transformClasses = '';
    let opacityClass = '';
    let zIndexClass = '';
    let scaleClass = '';
    
    if (isActive) {
      zIndexClass = 'z-50';
      opacityClass = 'opacity-100';
      transformClasses = 'rotate-0 translate-x-0 translate-y-0';
      scaleClass = 'scale-100';
    } else if (isNext) {
      zIndexClass = 'z-40';
      opacityClass = 'opacity-90';
      transformClasses = 'rotate-6 translate-x-[20%] -translate-y-[5%]';
      scaleClass = 'scale-95';
    } else if (isNextNext) {
      zIndexClass = 'z-30';
      opacityClass = 'opacity-60';
      transformClasses = 'rotate-12 translate-x-[35%] -translate-y-[10%]';
      scaleClass = 'scale-90';
    } else if (isPrev) {
      zIndexClass = 'z-40';
      opacityClass = 'opacity-90';
      transformClasses = '-rotate-6 -translate-x-[20%] -translate-y-[5%]';
      scaleClass = 'scale-95';
    } else if (isPrevPrev) {
      zIndexClass = 'z-30';
      opacityClass = 'opacity-60';
      transformClasses = '-rotate-12 -translate-x-[35%] -translate-y-[10%]';
      scaleClass = 'scale-90';
    } else {
      opacityClass = 'opacity-0';
      scaleClass = 'scale-0';
    }
    
    const positionClass = `
      ${baseClasses}
      ${zIndexClass} ${opacityClass} ${transformClasses} ${scaleClass}
    `;
    
    // 计算阴影效果
    const shadowStyle = isActive
      ? '0 25px 50px -12px rgba(0, 0, 0, 0.3)' 
      : '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
    
    return {
      positionClass,
      style: { 
        transformStyle: 'preserve-3d' as 'preserve-3d', 
        backfaceVisibility: 'hidden' as 'hidden',
        boxShadow: shadowStyle,
      }
    };
  }, [currentSlide, items.length, isTransitioning]);

  // 创建回调ref处理函数 - 解决React.Ref合并和TypeScript类型安全问题
  const setCombinedRefs = useCallback((node: HTMLDivElement | null) => {
    // 安全地更新内部ref
    carouselRef.current = node;
    
    // 调用inViewRef函数
    if (typeof inViewRef === 'function') {
      inViewRef(node);
    }
  }, [inViewRef]);

  if (!items || items.length === 0) {
    return (
      <div className="w-full bg-gray-100 flex items-center justify-center" style={{ height }}>
        <span className="text-gray-500">No items available</span>
      </div>
    );
  }

  return (
    <div 
      ref={setCombinedRefs}
      className="relative w-full overflow-hidden rounded-xl"
      style={{ height }}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
    >
      {/* 装饰背景元素 */}
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute top-1/3 left-1/4 w-20 h-20 rounded-full bg-blue-500/20 blur-xl animate-float"></div>
        <div className="absolute bottom-1/4 right-1/3 w-32 h-32 rounded-full bg-indigo-500/20 blur-xl animate-float" style={{ animationDelay: '2s' }}></div>
        <div className="absolute top-3/4 left-1/3 w-16 h-16 rounded-full bg-purple-500/20 blur-xl animate-float" style={{ animationDelay: '1s' }}></div>
      </div>
      
      {/* 卡片式轮播 */}
      <div className="w-full h-full relative perspective-1000">
        {items.map((item, index) => {
          const { positionClass, style } = getCardStyles(index);
          
          return (
            <div 
              key={item.id}
              className={positionClass}
              style={style}
            >
              <div className={`relative h-full transform transition-all duration-700 ${index === currentSlide ? 'hover:scale-[1.02]' : ''}`}>
                <div className="bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-2xl p-1.5 h-full shadow-xl">
                  <div className="bg-white dark:bg-gray-900 rounded-xl overflow-hidden h-full">
                    <div className="relative h-full">
                      <div className="w-full h-2/3 overflow-hidden">
                        <img 
                          src={item.imageUrl}
                          alt={item.title}
                          className={`w-full h-full object-cover transition-transform duration-700 ${index === currentSlide ? 'hover:scale-110' : ''}`}
                          loading="lazy"
                        />
                      </div>
                      
                      {/* Floating badge */}
                      {item.badgeText && (
                        <div className="absolute -top-3 -right-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold py-2 px-4 rounded-full shadow-lg transform rotate-3 animate-pulse">
                          {item.badgeText}
                        </div>
                      )}
                      
                      {/* Content overlay */}
                      <div className="absolute bottom-1/3 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-8 rounded-b-xl">
                        <h3 className="text-white font-bold text-2xl mb-2">{item.title}</h3>
                        {item.subtitle && (
                          <p className="text-white/90 mb-4">{item.subtitle}</p>
                        )}
                        {item.buttonText && item.buttonLink && (
                          <a 
                            href={item.buttonLink}
                            className="inline-flex items-center bg-white text-blue-600 hover:bg-blue-50 px-5 py-2 rounded-full shadow-md font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg"
                          >
                            {item.buttonText}
                            <svg className="ml-1.5 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7-7 7"></path>
                            </svg>
                          </a>
                        )}
                      </div>
                      
                      <div className="absolute bottom-0 left-0 right-0 h-1/3 bg-white dark:bg-gray-900 p-6">
                        <div className="flex items-start space-x-4">
                          <div className="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0 animate-pulse" style={{ animationDuration: '3s' }}>
                            <svg className="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                          </div>
                          <div>
                            <h4 className="text-gray-900 dark:text-white font-semibold text-lg">Premium Quality</h4>
                            <p className="text-gray-600 dark:text-gray-400 mt-1">Crafted with the finest materials for maximum comfort and durability</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>
      
      {/* 导航按钮 */}
      {showNavigation && items.length > 1 && (
        <>
          <div className="absolute top-1/2 left-4 md:left-8 transform -translate-y-1/2 z-50">
            <button
              onClick={goToPrevSlide}
              className="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center rounded-full bg-white/80 backdrop-blur-sm text-gray-800 hover:bg-white transition-all duration-300 hover:scale-110 hover:shadow-lg shadow-lg"
              aria-label="Previous slide"
              disabled={isTransitioning}
            >
              <svg className="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
          </div>
          
          <div className="absolute top-1/2 right-4 md:right-8 transform -translate-y-1/2 z-50">
            <button
              onClick={goToNextSlide}
              className="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center rounded-full bg-white/80 backdrop-blur-sm text-gray-800 hover:bg-white transition-all duration-300 hover:scale-110 hover:shadow-lg shadow-lg"
              aria-label="Next slide"
              disabled={isTransitioning}
            >
              <svg className="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </>
      )}

      {/* 指示器 */}
      {showIndicators && items.length > 1 && (
        <div className="absolute bottom-6 left-0 right-0 flex justify-center space-x-3 z-50">
          {items.map((_, index) => (
            <button
              key={index}
              onClick={() => goToSlide(index)}
              className={`transition-all duration-500 ease-out ${
                currentSlide === index 
                  ? 'h-3 bg-blue-600 w-12 rounded-full'
                  : 'h-3 w-3 bg-white/70 hover:bg-white rounded-full'
              }`}
              aria-label={`Go to slide ${index + 1}`}
            />
          ))}
        </div>
      )}
      
      {/* 进度指示器 */}
      {autoplay && inView && (
        <div className="absolute bottom-0 left-0 w-full h-1 bg-white/10 z-40">
          <div 
            className="h-full bg-blue-500 transition-none"
            style={{ 
              width: isPaused ? '0%' : '100%',
              transition: isPaused ? 'none' : `width ${delay}ms linear`
            }}
          />
        </div>
      )}
    </div>
  );
};

export default CardCarousel; 
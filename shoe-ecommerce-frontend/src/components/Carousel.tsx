import React, { useState, useEffect, useRef } from 'react';

interface CarouselImage {
  id: number;
  image_url: string;
  title?: string;
  subtitle?: string;
  link?: string;
}

interface CarouselProps {
  images: CarouselImage[];
  autoplay?: boolean;
  delay?: number;
  transition?: 'slide' | 'fade' | 'zoom';
  showNavigation?: boolean;
  showIndicators?: boolean;
  enableTouchGestures?: boolean;
}

const Carousel: React.FC<CarouselProps> = ({
  images,
  autoplay = true,
  delay = 5000,
  transition = 'slide',
  showNavigation = true,
  showIndicators = true,
  enableTouchGestures = true,
}) => {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isTransitioning, setIsTransitioning] = useState(false);
  const [touchStartX, setTouchStartX] = useState(0);
  const [touchEndX, setTouchEndX] = useState(0);
  const autoplayTimerRef = useRef<NodeJS.Timeout | null>(null);
  const carouselRef = useRef<HTMLDivElement>(null);

  // 重置自动播放计时器
  const resetAutoplayTimer = () => {
    if (autoplayTimerRef.current) {
      clearTimeout(autoplayTimerRef.current);
    }
    
    if (autoplay && images.length > 1) {
      autoplayTimerRef.current = setTimeout(() => {
        goToNextSlide();
      }, delay);
    }
  };

  // 设置初始自动播放
  useEffect(() => {
    resetAutoplayTimer();
    
    return () => {
      if (autoplayTimerRef.current) {
        clearTimeout(autoplayTimerRef.current);
      }
    };
  }, [autoplay, delay, currentIndex, images.length]);

  // 跳转到下一张幻灯片
  const goToNextSlide = () => {
    if (isTransitioning || images.length <= 1) return;
    
    setIsTransitioning(true);
    setCurrentIndex((prevIndex) => (prevIndex + 1) % images.length);
    
    setTimeout(() => {
      setIsTransitioning(false);
    }, 500); // 过渡动画持续时间
  };

  // 跳转到上一张幻灯片
  const goToPrevSlide = () => {
    if (isTransitioning || images.length <= 1) return;
    
    setIsTransitioning(true);
    setCurrentIndex((prevIndex) => (prevIndex - 1 + images.length) % images.length);
    
    setTimeout(() => {
      setIsTransitioning(false);
    }, 500); // 过渡动画持续时间
  };

  // 跳转到指定幻灯片
  const goToSlide = (index: number) => {
    if (isTransitioning || index === currentIndex || images.length <= 1) return;
    
    setIsTransitioning(true);
    setCurrentIndex(index);
    
    setTimeout(() => {
      setIsTransitioning(false);
    }, 500); // 过渡动画持续时间
  };

  // 处理触摸开始事件
  const handleTouchStart = (e: React.TouchEvent) => {
    if (!enableTouchGestures) return;
    
    setTouchStartX(e.touches[0].clientX);
    setTouchEndX(e.touches[0].clientX);
  };

  // 处理触摸移动事件
  const handleTouchMove = (e: React.TouchEvent) => {
    if (!enableTouchGestures) return;
    
    setTouchEndX(e.touches[0].clientX);
  };

  // 处理触摸结束事件
  const handleTouchEnd = () => {
    if (!enableTouchGestures) return;
    
    const touchDiff = touchStartX - touchEndX;
    const minSwipeDistance = 50;
    
    if (touchDiff > minSwipeDistance) {
      // 向左滑动，显示下一个
      goToNextSlide();
    } else if (touchDiff < -minSwipeDistance) {
      // 向右滑动，显示上一个
      goToPrevSlide();
    }
    
    setTouchStartX(0);
    setTouchEndX(0);
  };

  // 如果没有图片，显示占位符
  if (!images || images.length === 0) {
    return (
      <div className="w-full h-[400px] bg-gray-100 flex items-center justify-center">
        <span className="text-gray-400">No images available</span>
      </div>
    );
  }

  // 获取当前显示的幻灯片
  const currentImage = images[currentIndex];

  // 计算幻灯片动画类型
  const getTransitionClass = () => {
    if (!isTransitioning) return '';
    
    switch (transition) {
      case 'fade':
        return 'animate-fade-in';
      case 'zoom':
        return 'animate-zoom-in';
      case 'slide':
      default:
        return 'animate-slide-in';
    }
  };

  return (
    <div 
      ref={carouselRef}
      className="relative w-full h-[400px] md:h-[500px] lg:h-[600px] overflow-hidden rounded-lg"
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
    >
      {/* 幻灯片内容 */}
      <div 
        className={`absolute inset-0 bg-cover bg-center ${getTransitionClass()}`}
        style={{ backgroundImage: `url(${currentImage.image_url})` }}
      >
        {/* 文本叠加层 */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-6 md:p-10">
          <div className="max-w-3xl">
            {currentImage.title && (
              <h2 className="text-white text-2xl md:text-3xl lg:text-4xl font-bold leading-tight">
                {currentImage.title}
              </h2>
            )}
            
            {currentImage.subtitle && (
              <p className="text-white/90 mt-2 md:mt-4 text-sm md:text-base lg:text-lg max-w-xl">
                {currentImage.subtitle}
              </p>
            )}
            
            {currentImage.link && (
              <a 
                href={currentImage.link}
                className="inline-block mt-4 md:mt-6 px-6 py-2 bg-white text-gray-900 hover:bg-gray-100 font-medium rounded-md transition-colors"
              >
                Shop Now
              </a>
            )}
          </div>
        </div>
      </div>

      {/* 导航按钮 */}
      {showNavigation && images.length > 1 && (
        <>
          <button
            onClick={goToPrevSlide}
            className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/50 rounded-full p-2 text-white backdrop-blur-sm transition-colors z-10"
            aria-label="Previous slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-6 h-6">
              <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
          </button>
          
          <button
            onClick={goToNextSlide}
            className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/50 rounded-full p-2 text-white backdrop-blur-sm transition-colors z-10"
            aria-label="Next slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-6 h-6">
              <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </>
      )}

      {/* 指示器 */}
      {showIndicators && images.length > 1 && (
        <div className="absolute bottom-4 left-0 right-0 flex justify-center space-x-2 z-10">
          {images.map((_, index) => (
            <button
              key={index}
              onClick={() => goToSlide(index)}
              className={`w-2.5 h-2.5 rounded-full transition-all ${
                index === currentIndex
                  ? 'bg-white w-6'
                  : 'bg-white/50 hover:bg-white/80'
              }`}
              aria-label={`Go to slide ${index + 1}`}
            />
          ))}
        </div>
      )}
    </div>
  );
};

export default Carousel; 
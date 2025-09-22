import React, { useRef, useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { CarouselSettings, CarouselItem } from '../../types/homepage';

interface CarouselProps {
  items?: CarouselItem[];
  settings?: CarouselSettings | null;
  autoplay?: boolean;
  interval?: number;
  showDots?: boolean;
  showArrows?: boolean;
  style?: 'default' | 'minimal' | 'contained' | 'fullwidth';
  height?: string;
  className?: string;
}

/**
 * 轮播组件 - 支持不同的样式和自动轮播
 */
const Carousel: React.FC<CarouselProps> = ({
  items: propItems,
  settings,
  autoplay: propAutoplay,
  interval: propInterval,
  showDots = true,
  showArrows = true,
  style = 'default',
  height = 'h-[500px]',
  className = '',
}) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isTransitioning, setIsTransitioning] = useState(false);
  const autoplayTimerRef = useRef<NodeJS.Timeout | null>(null);
  
  // 默认轮播项
  const defaultItems: CarouselItem[] = [
    {
      id: 1,
      title: 'New Collection 2023',
      subtitle: 'Discover the latest trends in footwear',
      imageUrl: 'https://placehold.co/1920x700/e6f7ff/0099cc?text=New+Collection',
      buttonText: 'Shop Now',
      buttonLink: '/products',
    },
    {
      id: 2,
      title: 'Summer Sale',
      subtitle: 'Up to 40% off on selected items',
      imageUrl: 'https://placehold.co/1920x700/fff5e6/ff9900?text=Summer+Sale',
      buttonText: 'View Offers',
      buttonLink: '/sale',
    },
    {
      id: 3,
      title: 'Premium Comfort',
      subtitle: 'Experience unmatched comfort with our ergonomic designs',
      imageUrl: 'https://placehold.co/1920x700/f2f2f2/666666?text=Premium+Comfort',
      buttonText: 'Discover More',
      buttonLink: '/collections/comfort',
    },
  ];
  
  // 优先使用传入的 items，否则使用 defaultItems
  const carouselItems = propItems && propItems.length > 0 ? propItems : defaultItems;
  
  // 从 settings 或独立 props 获取控制参数
  const finalAutoplay = propAutoplay ?? settings?.autoplay ?? true;
  const finalInterval = propInterval ?? settings?.delay ?? 5000;
  const finalShowDots = showDots ?? settings?.showIndicators ?? true;
  const finalShowArrows = showArrows ?? settings?.showNavigation ?? true;
  
  // 自动轮播逻辑
  useEffect(() => {
    if (finalAutoplay && carouselItems.length > 1) {
      const startAutoplay = () => {
        autoplayTimerRef.current = setTimeout(() => {
          if (!isTransitioning) {
            goToNextSlide();
          }
        }, finalInterval);
      };
      
      startAutoplay();
      
      return () => {
        if (autoplayTimerRef.current) {
          clearTimeout(autoplayTimerRef.current);
        }
      };
    }
  }, [finalAutoplay, currentSlide, finalInterval, carouselItems.length, isTransitioning]);
  
  // 轮播控制
  const goToSlide = (index: number) => {
    if (!isTransitioning) {
      setIsTransitioning(true);
      setCurrentSlide(index);
      setTimeout(() => setIsTransitioning(false), 500); // 与CSS过渡时间相匹配
    }
  };
  
  const goToPrevSlide = () => {
    if (!isTransitioning) {
      const newIndex = currentSlide === 0 ? carouselItems.length - 1 : currentSlide - 1;
      goToSlide(newIndex);
    }
  };
  
  const goToNextSlide = () => {
    if (!isTransitioning) {
      const newIndex = (currentSlide + 1) % carouselItems.length;
      goToSlide(newIndex);
    }
  };
  
  // 鼠标悬停暂停自动轮播，离开后继续
  const handleMouseEnter = () => {
    if (autoplayTimerRef.current) {
      clearTimeout(autoplayTimerRef.current);
      autoplayTimerRef.current = null;
    }
  };
  
  const handleMouseLeave = () => {
    if (finalAutoplay && !autoplayTimerRef.current && carouselItems.length > 1) {
      autoplayTimerRef.current = setTimeout(goToNextSlide, finalInterval);
    }
  };
  
  // 根据样式渲染不同的轮播
  const renderCarousel = () => {
    switch (style) {
      case 'minimal':
        return (
          <div 
            className={`relative overflow-hidden ${height} ${className}`}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
          >
            <div 
              className="absolute inset-0 flex transition-transform duration-500 ease-in-out"
              style={{ transform: `translateX(-${currentSlide * 100}%)` }}
            >
              {carouselItems.map((item, index) => (
                <div key={item.id || index} className="min-w-full">
                  <div className="h-full bg-gray-50 flex flex-col justify-center px-8 md:px-16">
                    <div className="max-w-3xl mx-auto text-center">
                      <h2 className="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                        {item.title}
                      </h2>
                      <p className="text-lg md:text-xl text-gray-600 mb-8">
                        {item.subtitle}
                      </p>
                      <Link 
                        to={item.buttonLink || '#'}
                        className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors"
                      >
                        {item.buttonText || 'Learn More'}
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
            </div>
            
            {finalShowArrows && carouselItems.length > 1 && (
              <>
                <button 
                  className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/60 hover:bg-white/90 shadow-md flex items-center justify-center"
                  onClick={goToPrevSlide}
                >
                  <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button 
                  className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/60 hover:bg-white/90 shadow-md flex items-center justify-center"
                  onClick={goToNextSlide}
                >
                  <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </>
            )}
            
            {finalShowDots && carouselItems.length > 1 && (
              <div className="absolute bottom-6 left-0 right-0 flex justify-center gap-2">
                {carouselItems.map((_, index) => (
                  <button
                    key={index}
                    className={`w-3 h-3 rounded-full transition-all ${
                      currentSlide === index ? 'bg-blue-600 w-6' : 'bg-gray-300 hover:bg-gray-400'
                    }`}
                    onClick={() => goToSlide(index)}
                    aria-label={`Go to slide ${index + 1}`}
                  />
                ))}
              </div>
            )}
          </div>
        );
        
      case 'contained':
        return (
          <div 
            className={`relative overflow-hidden rounded-xl shadow-lg ${height} ${className}`}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
          >
            <div 
              className="absolute inset-0 flex transition-transform duration-500 ease-in-out"
              style={{ transform: `translateX(-${currentSlide * 100}%)` }}
            >
              {carouselItems.map((item, index) => (
                <div key={item.id || index} className="min-w-full relative">
                  <img 
                    src={item.imageUrl || ''}
                    alt={item.title}
                    className="w-full h-full object-cover object-center"
                  />
                  <div className="absolute inset-0 bg-black/40 flex items-center">
                    <div className="container mx-auto px-8">
                      <div className="max-w-lg">
                        <h2 className="text-3xl md:text-4xl font-bold text-white mb-3">
                          {item.title}
                        </h2>
                        <p className="text-lg text-white/90 mb-6">
                          {item.subtitle}
                        </p>
                        <Link 
                          to={item.buttonLink || '#'}
                          className="inline-block bg-white hover:bg-gray-100 text-gray-900 font-medium py-2 px-6 rounded-md transition-colors"
                        >
                          {item.buttonText || 'Learn More'}
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
            
            {finalShowArrows && carouselItems.length > 1 && (
              <>
                <button 
                  className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/60 hover:bg-white/90 shadow-md flex items-center justify-center z-10"
                  onClick={goToPrevSlide}
                >
                  <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button 
                  className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/60 hover:bg-white/90 shadow-md flex items-center justify-center z-10"
                  onClick={goToNextSlide}
                >
                  <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </>
            )}
            
            {finalShowDots && carouselItems.length > 1 && (
              <div className="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-10">
                {carouselItems.map((_, index) => (
                  <button
                    key={index}
                    className={`w-2.5 h-2.5 rounded-full transition-all ${
                      currentSlide === index ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'
                    }`}
                    onClick={() => goToSlide(index)}
                    aria-label={`Go to slide ${index + 1}`}
                  />
                ))}
              </div>
            )}
          </div>
        );
        
      case 'fullwidth':
        return (
          <div 
            className={`relative overflow-hidden ${height} ${className}`}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
          >
            <div 
              className="absolute inset-0 flex transition-transform duration-500 ease-in-out"
              style={{ transform: `translateX(-${currentSlide * 100}%)` }}
            >
              {carouselItems.map((item, index) => (
                <div key={item.id || index} className="min-w-full relative">
                  <img 
                    src={item.imageUrl || ''}
                    alt={item.title} 
                    className="w-full h-full object-cover object-center"
                  />
                  <div className="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                    <div className="max-w-2xl pl-12 md:pl-24">
                      <h2 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4">
                        {item.title}
                      </h2>
                      <p className="text-xl md:text-2xl text-white/90 mb-8">
                        {item.subtitle}
                      </p>
                      <Link 
                        to={item.buttonLink || '#'}
                        className="inline-block bg-white hover:bg-gray-100 text-gray-900 font-medium py-3 px-8 rounded-md transition-colors shadow-lg"
                      >
                        {item.buttonText || 'Learn More'}
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
            </div>
            
            {finalShowArrows && carouselItems.length > 1 && (
              <>
                <button 
                  className="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/30 hover:bg-white/60 shadow-md flex items-center justify-center z-10"
                  onClick={goToPrevSlide}
                >
                  <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button 
                  className="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/30 hover:bg-white/60 shadow-md flex items-center justify-center z-10"
                  onClick={goToNextSlide}
                >
                  <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </>
            )}
            
            {finalShowDots && carouselItems.length > 1 && (
              <div className="absolute bottom-8 left-0 right-0 flex justify-center gap-2 z-10">
                {carouselItems.map((_, index) => (
                  <button
                    key={index}
                    className={`w-3 h-3 rounded-full transition-all ${
                      currentSlide === index ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'
                    }`}
                    onClick={() => goToSlide(index)}
                    aria-label={`Go to slide ${index + 1}`}
                  />
                ))}
              </div>
            )}
          </div>
        );
        
      default:
        return (
          <div 
            className={`relative overflow-hidden ${height} ${className}`}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
          >
            <div 
              className="absolute inset-0 flex transition-transform duration-500 ease-in-out"
              style={{ transform: `translateX(-${currentSlide * 100}%)` }}
            >
              {carouselItems.map((item, index) => (
                <div key={item.id || index} className="min-w-full relative">
                  <img 
                    src={item.imageUrl || ''}
                    alt={item.title} 
                    className="w-full h-full object-cover object-center"
                  />
                  <div className="absolute inset-0 bg-black/30 flex flex-col justify-center items-center text-center text-white p-8">
                    <h2 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">
                      {item.title}
                    </h2>
                    <p className="text-lg md:text-xl mb-6 max-w-xl">
                      {item.subtitle}
                    </p>
                    <Link 
                      to={item.buttonLink || '#'}
                      className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors"
                    >
                      {item.buttonText || 'Learn More'}
                    </Link>
                  </div>
                </div>
              ))}
            </div>
            
            {finalShowArrows && carouselItems.length > 1 && (
              <>
                <button 
                  className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/60 hover:bg-white/90 shadow-md flex items-center justify-center"
                  onClick={goToPrevSlide}
                >
                  <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button 
                  className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/60 hover:bg-white/90 shadow-md flex items-center justify-center"
                  onClick={goToNextSlide}
                >
                  <svg className="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </>
            )}
            
            {finalShowDots && carouselItems.length > 1 && (
              <div className="absolute bottom-6 left-0 right-0 flex justify-center gap-2">
                {carouselItems.map((_, index) => (
                  <button
                    key={index}
                    className={`w-2.5 h-2.5 rounded-full transition-all ${
                      currentSlide === index ? 'bg-white' : 'bg-white/50 hover:bg-white/80'
                    }`}
                    onClick={() => goToSlide(index)}
                    aria-label={`Go to slide ${index + 1}`}
                  />
                ))}
              </div>
            )}
          </div>
        );
    }
  };

  return renderCarousel();
};

export default Carousel; 
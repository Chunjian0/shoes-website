import React from 'react';
import { BannerSettings } from '../../types/homepage';
import AnimatedElement, { AnimationType } from '../animations/AnimatedElement';

// 可用的banner样式枚举
export enum BannerStyle {
  CENTERED = 'centered',
  LEFT_ALIGNED = 'left-aligned',
  RIGHT_ALIGNED = 'right-aligned',
  FULL_WIDTH = 'full-width',
  OVERLAY = 'overlay',
  MINIMAL = 'minimal',
  SPLIT = 'split',
  MODERN_GRADIENT = 'modern-gradient',
  CREATIVE = 'creative'
}

// 组件接口
export interface HeroBannerProps {
  settings: BannerSettings;
  style?: BannerStyle;
  height?: 'short' | 'medium' | 'tall' | 'full';
  showButton?: boolean;
  animationType?: AnimationType;
  className?: string;
}

// HeroBanner组件 - 显示主Banner区域
const HeroBanner: React.FC<HeroBannerProps> = ({
  settings,
  style = BannerStyle.CENTERED,
  height = 'medium',
  showButton = true,
  animationType = 'fade-in',
  className = ''
}) => {
  const {
    title,
    subtitle,
    buttonText,
    buttonLink,
    imageUrl
  } = settings;

  // Access banner_color using type assertion as it's missing from the type def
  const banner_color = (settings as any).banner_color;

  // 高度映射到类名
  const heightClass = {
    short: 'min-h-[300px] md:min-h-[400px]',
    medium: 'min-h-[400px] md:min-h-[500px]',
    tall: 'min-h-[500px] md:min-h-[600px]',
    full: 'min-h-screen'
  };

  // 文本颜色（基于背景色）
  const textColor = (banner_color && banner_color.startsWith('#')) 
    ? (parseInt(banner_color.substring(1), 16) > 0xffffff / 2) 
      ? 'text-gray-800' 
      : 'text-white'
    : 'text-white';

  // 根据不同样式渲染banner内容
  const renderBannerContent = () => {
    // 通用标题组件
    const Title = () => (
      <h1 className={`text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-4 ${textColor}`}>
        {title}
      </h1>
    );

    // 通用副标题组件
    const Subtitle = () => (
      <p className={`text-lg md:text-xl ${textColor} opacity-90 mb-8 max-w-2xl`}>
        {subtitle}
      </p>
    );

    // 通用按钮组件
    const Button = () => (
      showButton && buttonText ? (
        <a
          href={buttonLink || '#'}
          className="inline-block bg-white text-gray-800 hover:bg-gray-100 font-medium py-3 px-8 rounded-md shadow-md hover:shadow-lg transition-all duration-300"
        >
          {buttonText}
        </a>
      ) : null
    );

    switch (style) {
      case BannerStyle.CENTERED:
        return (
          <div className="container mx-auto px-4 h-full flex flex-col justify-center items-center text-center">
            <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
              <Title />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
              <Subtitle />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
              <Button />
            </AnimatedElement>
          </div>
        );

      case BannerStyle.LEFT_ALIGNED:
        return (
          <div className="container mx-auto px-4 h-full flex flex-col justify-center items-start">
            <div className="max-w-lg">
              <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
                <Title />
              </AnimatedElement>
              <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
                <Subtitle />
              </AnimatedElement>
              <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
                <Button />
              </AnimatedElement>
            </div>
          </div>
        );

      case BannerStyle.RIGHT_ALIGNED:
        return (
          <div className="container mx-auto px-4 h-full flex flex-col justify-center items-end">
            <div className="max-w-lg text-right">
              <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
                <Title />
              </AnimatedElement>
              <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
                <Subtitle />
              </AnimatedElement>
              <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
                <Button />
              </AnimatedElement>
            </div>
          </div>
        );

      case BannerStyle.FULL_WIDTH:
        return (
          <div className="w-full px-4 h-full flex flex-col justify-center items-center text-center">
            <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
              <Title />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
              <Subtitle />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
              <Button />
            </AnimatedElement>
          </div>
        );

      case BannerStyle.OVERLAY:
        return (
          <div className="absolute inset-0 bg-black bg-opacity-40 flex flex-col justify-center items-center text-center px-4">
            <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
              <Title />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
              <Subtitle />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
              <Button />
            </AnimatedElement>
          </div>
        );

      case BannerStyle.MINIMAL:
        return (
          <div className="container mx-auto px-4 h-full flex flex-col justify-end pb-16 md:pb-24">
            <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
              <Title />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
              <div className="flex items-center">
                <div className="h-1 w-12 bg-white mr-4"></div>
                <Subtitle />
              </div>
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
              <Button />
            </AnimatedElement>
          </div>
        );

      case BannerStyle.SPLIT:
        return (
          <div className="container mx-auto h-full grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="flex flex-col justify-center items-start px-4">
              <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
                <Title />
              </AnimatedElement>
              <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
                <Subtitle />
              </AnimatedElement>
              <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
                <Button />
              </AnimatedElement>
            </div>
            <div className="hidden md:flex items-center justify-center">
              <AnimatedElement type="zoom-in" options={{ delay: 0.4 }}>
                <div className="relative w-full h-full max-h-[500px] overflow-hidden rounded-xl">
                  <div className="absolute inset-0 bg-gradient-to-tr from-black/20 to-transparent"></div>
                  <img 
                    src={imageUrl}
                    alt={title}
                    className="w-full h-full object-cover" 
                  />
                </div>
              </AnimatedElement>
            </div>
          </div>
        );

      case BannerStyle.MODERN_GRADIENT:
        return (
          <div className="relative z-10 w-full h-full">
            {/* 装饰背景元素 */}
            <div className="absolute top-0 right-0 w-1/3 h-1/3 bg-blue-500 opacity-20 rounded-full blur-[80px] transform translate-x-1/4 -translate-y-1/4"></div>
            <div className="absolute bottom-0 left-0 w-1/3 h-1/3 bg-indigo-600 opacity-20 rounded-full blur-[100px] transform -translate-x-1/4 translate-y-1/4"></div>
            
            {/* 主内容 */}
            <div className="container mx-auto px-4 h-full relative z-10">
              <div className="h-full flex flex-col md:flex-row items-center">
                <div className="w-full md:w-1/2 py-12 md:py-0">
                  <AnimatedElement type="slide-up" options={{ delay: 0.1, duration: 0.8 }}>
                    <h1 className={`text-4xl md:text-5xl lg:text-7xl font-bold leading-tight mb-6 ${textColor}`}>
                      {title}
                      <span className="relative">
                        <span className="relative z-10">{" elegance"}</span>
                        <svg className="absolute bottom-1 left-0 w-full h-3 text-blue-500/30" viewBox="0 0 200 9" xmlns="http://www.w3.org/2000/svg">
                          <path d="M0,6 C50,2 150,2 200,6" stroke="currentColor" strokeWidth="8" fill="none" />
                        </svg>
                      </span>
                    </h1>
                  </AnimatedElement>
                  
                  <AnimatedElement type="fade-in" options={{ delay: 0.3, duration: 0.8 }}>
                    <p className={`text-lg md:text-xl ${textColor} opacity-80 mb-8 max-w-xl leading-relaxed`}>
                      {subtitle}
                    </p>
                  </AnimatedElement>
                  
                  <AnimatedElement type="scale-in-bounce" options={{ delay: 0.5 }}>
                    {showButton && buttonText ? (
                      <a
                        href={buttonLink || '#'}
                        className="inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium shadow-lg hover:shadow-xl transform transition-all duration-300 hover:-translate-y-1"
                      >
                        {buttonText}
                        <svg className="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                      </a>
                    ) : null}
                  </AnimatedElement>
                  
                  {/* 特性标签 */}
                  <AnimatedElement type="slide-up" options={{ delay: 0.7 }}>
                    <div className="flex flex-wrap gap-3 mt-10">
                      {['Premium Quality', 'Sustainable', 'Exclusive Design'].map((tag, i) => (
                        <span 
                          key={i} 
                          className="inline-flex items-center px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm text-sm font-medium"
                        >
                          <span className="w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                          {tag}
                        </span>
                      ))}
                    </div>
                  </AnimatedElement>
                </div>
                
                <div className="w-full md:w-1/2 relative">
                  <AnimatedElement type="float" options={{ delay: 0.2 }}>
                    <div className="relative">
                      {/* 产品图片 */}
                      <div className="relative z-10 rounded-2xl overflow-hidden shadow-2xl">
                        <img 
                          src={imageUrl}
                          alt={title}
                          className="w-full h-auto object-cover" 
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                      </div>
                      
                      {/* 装饰圆形 */}
                      <div className="absolute top-[10%] -right-[5%] w-24 h-24 md:w-40 md:h-40 bg-blue-500/20 rounded-full blur-md"></div>
                      <div className="absolute -bottom-[5%] -left-[5%] w-32 h-32 md:w-48 md:h-48 bg-indigo-600/20 rounded-full blur-md"></div>
                    </div>
                  </AnimatedElement>
                </div>
              </div>
            </div>
          </div>
        );

      case BannerStyle.CREATIVE:
        return (
          <div className="relative w-full h-full overflow-hidden">
            {/* 装饰背景元素 */}
            <div className="absolute inset-0 z-0">
              {/* 波浪动画 */}
              <div className="absolute top-0 left-0 w-full h-full">
                <svg className="w-full h-full opacity-10" viewBox="0 0 1200 600" preserveAspectRatio="none">
                  <path 
                    className="wave wave1" 
                    d="M0,100 C300,250 600,50 1200,100 V600 H0 V100Z" 
                    fill="rgba(139, 92, 246, 0.5)"
                  ></path>
                  <path 
                    className="wave wave2" 
                    d="M0,150 C300,50 600,200 1200,150 V600 H0 V150Z" 
                    fill="rgba(79, 70, 229, 0.5)"
                  ></path>
                </svg>
              </div>
              
              {/* 动态图形 */}
              <div className="absolute inset-0">
                <div className="absolute top-[20%] left-[10%] w-24 h-24 md:w-40 md:h-40 rounded-full bg-purple-500/30 mix-blend-overlay animate-float"></div>
                <div className="absolute top-[60%] left-[80%] w-32 h-32 md:w-48 md:h-48 rounded-full bg-blue-500/20 mix-blend-overlay animate-float-delayed"></div>
                <div className="absolute top-[70%] left-[20%] w-16 h-16 md:w-32 md:h-32 rounded-full bg-indigo-500/30 mix-blend-overlay animate-float-slow"></div>
              </div>
            </div>
            
            {/* 主内容 */}
            <div className="container mx-auto px-4 h-full relative z-10">
              <div className="grid grid-cols-1 md:grid-cols-2 h-full gap-8 items-center">
                <div className="text-white order-2 md:order-1">
                  <AnimatedElement type="slide-up" options={{ delay: 0.1, duration: 0.8 }}>
                    <div className="mb-2">
                      <span className="inline-block px-3 py-1 bg-white/10 backdrop-blur-sm rounded-full text-sm font-medium mb-4">
                        Premium Collection
                      </span>
                    </div>
                    <h1 className={`text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6 ${textColor}`}>
                      {title?.split(' ').map((word, i) => (
                        <span key={i} className="inline-block">
                          {i > 0 && ' '}
                          <span className="relative">
                            {word}
                            {i === 1 && (
                              <span className="absolute -bottom-2 left-0 w-full h-2 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full transform skew-x-12"></span>
                            )}
                          </span>
                        </span>
                      ))}
                    </h1>
                  </AnimatedElement>
                  
                  <AnimatedElement type="fade-in" options={{ delay: 0.3, duration: 0.8 }}>
                    <p className={`text-lg md:text-xl text-white/80 mb-8 max-w-xl leading-relaxed`}>
                      {subtitle}
                    </p>
                  </AnimatedElement>
                  
                  <AnimatedElement type="fade-in" options={{ delay: 0.5 }}>
                    <div className="flex flex-wrap gap-6">
                      {showButton && buttonText ? (
                        <a
                          href={buttonLink || '#'}
                          className="inline-flex items-center px-6 py-3 rounded-full bg-white text-purple-900 font-medium shadow-lg hover:shadow-xl transform transition-all duration-300 hover:-translate-y-1"
                        >
                          {buttonText}
                          <svg className="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                          </svg>
                        </a>
                      ) : null}
                      
                      <a href="/collection" className="inline-flex items-center px-6 py-3 rounded-full bg-white/10 backdrop-blur-sm text-white font-medium hover:bg-white/20 transition-all duration-300">
                        Explore Collection
                      </a>
                    </div>
                  </AnimatedElement>
                  
                  {/* 轮播指示器 */}
                  <AnimatedElement type="slide-up" options={{ delay: 0.7 }}>
                    <div className="hidden md:flex items-center gap-3 mt-12">
                      <span className="text-white/60 text-sm">01</span>
                      <div className="relative w-32 h-1 bg-white/20 rounded-full overflow-hidden">
                        <div className="absolute left-0 top-0 h-full w-1/3 bg-white rounded-full"></div>
                      </div>
                      <span className="text-white/60 text-sm">03</span>
                    </div>
                  </AnimatedElement>
                </div>
                
                <div className="relative order-1 md:order-2">
                  <AnimatedElement type="fade-in" options={{ delay: 0.2 }}>
                    <div className="relative">
                      {/* 产品图片 */}
                      <div className="relative rounded-3xl overflow-hidden transform rotate-3 md:rotate-6 shadow-2xl">
                        <img 
                          src={imageUrl || "https://images.unsplash.com/photo-1543508282-6319a3e2621f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80"} 
                          alt={title}
                          className="w-full h-auto object-cover" 
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                      </div>
                      
                      {/* 装饰元素 */}
                      <div className="absolute -bottom-6 -left-6 w-32 h-32 rounded-full bg-gradient-to-br from-purple-500 to-indigo-500 opacity-80 blur-xl animate-pulse"></div>
                      
                      {/* 产品标签 */}
                      <div className="absolute -top-4 -right-4 w-24 h-24 rounded-full bg-white shadow-xl flex items-center justify-center p-2 animate-float">
                        <div className="w-full h-full rounded-full border-2 border-dashed border-purple-500 flex items-center justify-center">
                          <span className="text-purple-900 font-bold text-sm">NEW<br/>DESIGN</span>
                        </div>
                      </div>
                    </div>
                  </AnimatedElement>
                  
                  {/* 特性标签 */}
                  <div className="absolute bottom-8 right-8 z-20 bg-white/10 backdrop-blur-md px-4 py-2 rounded-lg shadow-lg">
                    <AnimatedElement type="slide-up" options={{ delay: 0.6 }}>
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 flex items-center justify-center">
                          <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                        </div>
                        <div>
                          <p className="text-white text-sm font-medium">Premium Quality</p>
                          <p className="text-white/70 text-xs">Handcrafted excellence</p>
                        </div>
                      </div>
                    </AnimatedElement>
                  </div>
                </div>
              </div>
            </div>
            
            {/* CSS动画 */}
            <style>{`
              @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-20px); }
              }
              
              @keyframes float-delayed {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-15px); }
              }
              
              @keyframes float-slow {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
              }
              
              @keyframes wave {
                0% { transform: translateX(0); }
                50% { transform: translateX(-25%); }
                100% { transform: translateX(0); }
              }
              
              .animate-float {
                animation: float 6s ease-in-out infinite;
              }
              
              .animate-float-delayed {
                animation: float-delayed 7s ease-in-out infinite 1s;
              }
              
              .animate-float-slow {
                animation: float-slow 8s ease-in-out infinite 2s;
              }
              
              .wave {
                animation: wave 15s ease-in-out infinite;
              }
              
              .wave1 {
                animation-duration: 20s;
              }
              
              .wave2 {
                animation-duration: 15s;
                animation-delay: 1s;
              }
            `}</style>
          </div>
        );

      default:
        return (
          <div className="container mx-auto px-4 h-full flex flex-col justify-center items-center text-center">
            <AnimatedElement type={animationType} options={{ delay: 0.1 }}>
              <Title />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.2 }}>
              <Subtitle />
            </AnimatedElement>
            <AnimatedElement type={animationType} options={{ delay: 0.3 }}>
              <Button />
            </AnimatedElement>
          </div>
        );
    }
  };

  // 为新的横幅样式提供不同的背景设置
  const getBackgroundStyles = () => {
    if (style === BannerStyle.MODERN_GRADIENT) {
      // Use banner_color safely
      const gradientFrom = banner_color ? `from-[${banner_color}]` : 'from-gray-800';
      return {
        background: `linear-gradient(135deg, ${gradientFrom} 0%, #4f46e5 100%)`,
        backgroundSize: 'cover',
        backgroundPosition: 'center'
      };
    }
    
    if (style === BannerStyle.CREATIVE) {
      // Keep as is or use banner_color
      return {
        background: `linear-gradient(135deg, #4c1d95 0%, #4338ca 50%, #1e40af 100%)`,
        backgroundSize: 'cover',
        backgroundPosition: 'center'
      };
    }

    return {
      backgroundImage: style !== BannerStyle.SPLIT ? `url(${imageUrl})` : 'none',
      backgroundColor: banner_color || '#000',
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    };
  };

  return (
    <section 
      className={`relative ${heightClass[height]} ${className}`} 
      style={getBackgroundStyles()}
    >
      {renderBannerContent()}
    </section>
  );
};

export default HeroBanner; 
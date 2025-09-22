import React, { useState } from 'react';
import { Link } from 'react-router-dom';
// Use 'any' for settings initially to avoid deep type issues
// import { BannerSettings } from '../../types/homepage'; 
import AnimatedElement from '../animations/AnimatedElement';
import { AnimationType } from '../templates/TemplateInterface';

interface HeroBannerProps {
  // settings: BannerSettings | null;
  settings: any | null; // Use any for now
  style?: 'fullscreen' | 'contained' | 'split' | 'minimal';
  overlayOpacity?: string;
  textAlignment?: 'left' | 'center' | 'right';
  animations?: boolean;
  className?: string;
  height?: string;
}

/**
 * 通用横幅组件 - 支持不同的样式和动画效果
 */
const HeroBanner: React.FC<HeroBannerProps> = ({
  settings,
  style = 'contained',
  overlayOpacity = 'bg-black/50',
  textAlignment = 'left',
  animations = true,
  className = '',
  height = 'h-[70vh]',
}) => {
  const [imageError, setImageError] = useState(false);

  // 默认设置 - Define a basic type inline or use any
  const defaultSettings: any = { // Use any for default settings type
    banner_title: 'Step Into Style & Comfort',
    banner_subtitle: 'Discover our premium collection of footwear designed for every occasion',
    banner_button_text: 'Shop Now',
    banner_button_link: '/products',
    banner_image: 'https://placehold.co/1920x600/e6f7ff/0099cc?text=Premium+Footwear',
  };

  // 合并设置，优先使用提供的设置 - Use safe access
  const bannerSettings = settings || defaultSettings;
  
  // 处理图像加载错误
  const handleImageError = () => {
    setImageError(true);
  };

  // 获取文本对齐样式
  const getTextAlignmentClass = () => {
    switch (textAlignment) {
      case 'right':
        return 'text-right items-end';
      case 'center':
        return 'text-center items-center';
      case 'left':
      default:
        return 'text-left items-start';
    }
  };

  // 根据样式渲染不同的横幅
  const renderBanner = () => {
    // Use safe access for settings properties throughout the render method
    const title = bannerSettings?.banner_title ?? defaultSettings.banner_title;
    const subtitle = bannerSettings?.banner_subtitle ?? defaultSettings.banner_subtitle;
    const buttonText = bannerSettings?.banner_button_text ?? defaultSettings.banner_button_text;
    const buttonLink = bannerSettings?.banner_button_link ?? defaultSettings.banner_button_link;
    const image = bannerSettings?.banner_image ?? defaultSettings.banner_image;

    switch (style) {
      case 'fullscreen':
        return (
          <div className={`relative ${height} min-h-[400px] w-full overflow-hidden ${className}`}>
            {!imageError && image ? ( // Check image existence
              <div className="absolute inset-0">
                <img 
                  src={image} // Use safe variable
                  alt={title} // Use safe variable
                  className="w-full h-full object-cover object-center"
                  onError={handleImageError}
                />
                <div className={`absolute inset-0 ${overlayOpacity}`}></div>
              </div>
            ) : (
              <div className={`absolute inset-0 bg-gradient-to-r from-blue-900 to-indigo-800`}></div>
            )}
            
            <div className="relative h-full container mx-auto px-4 flex items-center">
              <div className={`max-w-xl flex flex-col ${getTextAlignmentClass()}`}>
                {animations ? (
                  <>
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.1 }}>
                      <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight">
                        {title} {/* Use safe variable */}
                      </h1>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.2 }}>
                      <p className="text-lg md:text-xl text-gray-200 mb-8">
                        {subtitle} {/* Use safe variable */}
                      </p>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.3 }}>
                      <Link 
                        to={buttonLink} // Use safe variable
                        className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors shadow-lg hover:shadow-xl"
                      >
                        {buttonText} {/* Use safe variable */}
                      </Link>
                    </AnimatedElement>
                  </>
                ) : (
                  <>
                    <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight">
                      {title} {/* Use safe variable */}
                    </h1>
                    <p className="text-lg md:text-xl text-gray-200 mb-8">
                      {subtitle} {/* Use safe variable */}
                    </p>
                    <Link 
                      to={buttonLink} // Use safe variable
                      className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors shadow-lg hover:shadow-xl"
                    >
                      {buttonText} {/* Use safe variable */}
                    </Link>
                  </>
                )}
              </div>
            </div>
          </div>
        );
        
      case 'split':
        return (
          <div className={`grid md:grid-cols-2 ${className}`}>
            <div className={`relative ${height} min-h-[400px] bg-gradient-to-r from-blue-900 to-indigo-800 flex items-center`}>
              <div className={`px-8 py-12 md:px-12 ${getTextAlignmentClass()}`}>
                {animations ? (
                  <>
                    <AnimatedElement type={"slide-right"} options={{ delay: 0.1 }}>
                      <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                        {title} {/* Use safe variable */}
                      </h1>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-right"} options={{ delay: 0.2 }}>
                      <p className="text-lg text-gray-200 mb-8">
                        {subtitle} {/* Use safe variable */}
                      </p>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-right"} options={{ delay: 0.3 }}>
                      <Link 
                        to={buttonLink} // Use safe variable
                        className="inline-block bg-white text-blue-900 font-medium py-3 px-8 rounded-md hover:bg-gray-100 transition-colors shadow-lg hover:shadow-xl"
                      >
                        {buttonText} {/* Use safe variable */}
                      </Link>
                    </AnimatedElement>
                  </>
                ) : (
                  <>
                    <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                      {title} {/* Use safe variable */}
                    </h1>
                    <p className="text-lg text-gray-200 mb-8">
                      {subtitle} {/* Use safe variable */}
                    </p>
                    <Link 
                      to={buttonLink} // Use safe variable
                      className="inline-block bg-white text-blue-900 font-medium py-3 px-8 rounded-md hover:bg-gray-100 transition-colors shadow-lg hover:shadow-xl"
                    >
                      {buttonText} {/* Use safe variable */}
                    </Link>
                  </>
                )}
              </div>
            </div>
            <div className="relative">
              {!imageError && image ? ( // Check image existence
                <img 
                  src={image} // Use safe variable
                  alt={title} // Use safe variable
                  className={`w-full ${height} min-h-[400px] object-cover object-center`}
                  onError={handleImageError}
                />
              ) : (
                <div className={`${height} min-h-[400px] bg-gray-200 flex items-center justify-center`}>
                  <span className="text-gray-400">Image not available</span>
                </div>
              )}
            </div>
          </div>
        );
        
      case 'minimal':
        return (
          <div className={`py-16 bg-gray-50 ${className}`}>
            <div className="container mx-auto px-4">
              <div className={`max-w-4xl mx-auto ${getTextAlignmentClass()}`}>
                {animations ? (
                  <>
                    <AnimatedElement type={"fade-in"} options={{ delay: 0.1 }}>
                      <h1 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                        {title} {/* Use safe variable */}
                      </h1>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"fade-in"} options={{ delay: 0.2 }}>
                      <p className="text-xl text-gray-600 mb-8 max-w-2xl">
                        {subtitle} {/* Use safe variable */}
                      </p>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"fade-in"} options={{ delay: 0.3 }}>
                      <Link 
                        to={buttonLink} // Use safe variable
                        className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors"
                      >
                        {buttonText} {/* Use safe variable */}
                      </Link>
                    </AnimatedElement>
                  </>
                ) : (
                  <>
                    <h1 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                      {title} {/* Use safe variable */}
                    </h1>
                    <p className="text-xl text-gray-600 mb-8 max-w-2xl">
                      {subtitle} {/* Use safe variable */}
                    </p>
                    <Link 
                      to={buttonLink} // Use safe variable
                      className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors"
                    >
                      {buttonText} {/* Use safe variable */}
                    </Link>
                  </>
                )}
              </div>
            </div>
          </div>
        );
        
      case 'contained':
      default:
        return (
          <div className={`relative overflow-hidden bg-gray-900 ${className}`}>
            {!imageError && image ? ( // Check image existence
              <div className="absolute inset-0">
                <img 
                  src={image} // Use safe variable
                  alt={title} // Use safe variable
                  className={`w-full ${height} min-h-[400px] object-cover object-center`}
                  onError={handleImageError}
                />
                <div className={`absolute inset-0 ${overlayOpacity}`}></div>
              </div>
            ) : (
              <div className={`absolute inset-0 ${height} min-h-[400px] bg-gradient-to-r from-blue-800 to-indigo-700`}></div>
            )}
            
            <div className="relative container mx-auto px-4 py-16 md:py-24">
              <div className={`max-w-2xl flex flex-col ${getTextAlignmentClass()}`}>
                {animations ? (
                  <>
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.1 }}>
                      <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight">
                        {title} {/* Use safe variable */}
                      </h1>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.2 }}>
                      <p className="text-lg md:text-xl text-gray-200 mb-8">
                        {subtitle} {/* Use safe variable */}
                      </p>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.3 }}>
                      <Link 
                        to={buttonLink} // Use safe variable
                        className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors shadow-lg hover:shadow-xl"
                      >
                        {buttonText} {/* Use safe variable */}
                      </Link>
                    </AnimatedElement>
                  </>
                ) : (
                   <>
                    <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight">
                      {title} {/* Use safe variable */}
                    </h1>
                    <p className="text-lg md:text-xl text-gray-200 mb-8">
                      {subtitle} {/* Use safe variable */}
                    </p>
                    <Link 
                      to={buttonLink} // Use safe variable
                      className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors shadow-lg hover:shadow-xl"
                    >
                      {buttonText} {/* Use safe variable */}
                    </Link>
                  </>
                )}
              </div>
            </div>
          </div>
        );
    }
  };

  return renderBanner();
};

export default HeroBanner; 
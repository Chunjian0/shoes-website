import React from 'react';
import { Link } from 'react-router-dom';
// Use 'any' for settings initially to avoid deep type issues
// import { OfferSettings } from '../../types/homepage';
import AnimatedElement from '../animations/AnimatedElement';
import { AnimationType } from '../templates/TemplateInterface';

interface OfferSectionProps {
  // settings: OfferSettings | null;
  settings: any | null; // Use any for now
  layout?: 'card' | 'banner' | 'inline' | 'featured';
  style?: 'dark' | 'light' | 'accent';
  animations?: boolean;
  className?: string;
}

/**
 * 特价优惠组件 - 可以在不同的模板中以不同的布局呈现
 */
const OfferSection: React.FC<OfferSectionProps> = ({
  settings,
  layout = 'banner',
  style = 'dark',
  animations = true,
  className = '',
}) => {
  // 默认设置 - Use any for type
  const defaultSettings: any = {
    offer_title: 'Special Limited Time Offer',
    offer_subtitle: 'Get up to 50% off on selected premium footwear. Hurry while stocks last!',
    offer_button_text: 'Shop the Sale',
    offer_button_link: '/sale',
    offer_image: 'https://placehold.co/800x600/ffebee/cc0000?text=Limited+Time+Offer',
    offer_background_color: '#f5f5f5',
    offer_text_color: '#333333',
    offer_accent_color: '#cc0000'
  };

  // 合并设置，优先使用提供的设置
  const offerSettings = settings || defaultSettings;
  
  // 获取样式类
  const getStyleClasses = () => {
    switch (style) {
      case 'light':
        return {
          bg: 'bg-white',
          text: 'text-gray-800',
          subtext: 'text-gray-600',
          button: 'bg-blue-600 hover:bg-blue-700 text-white',
          accent: 'text-blue-600',
        };
      case 'accent':
        return {
          bg: 'bg-blue-50',
          text: 'text-gray-900',
          subtext: 'text-gray-700',
          button: 'bg-blue-600 hover:bg-blue-700 text-white',
          accent: 'text-blue-600',
        };
      case 'dark':
      default:
        return {
          bg: 'bg-gray-900',
          text: 'text-white',
          subtext: 'text-gray-200',
          button: 'bg-white hover:bg-gray-100 text-gray-900',
          accent: 'text-blue-400',
        };
    }
  };

  const styleClasses = getStyleClasses();
  
  // 根据布局渲染不同的优惠区
  const renderOffer = () => {
    // Use safe access
    const title = offerSettings?.title ?? offerSettings?.offer_title ?? defaultSettings.offer_title;
    const subtitle = offerSettings?.subtitle ?? offerSettings?.offer_subtitle ?? defaultSettings.offer_subtitle;
    const buttonText = offerSettings?.buttonText ?? offerSettings?.offer_button_text ?? defaultSettings.offer_button_text;
    const buttonLink = offerSettings?.buttonLink ?? offerSettings?.offer_button_link ?? defaultSettings.offer_button_link;
    const image = offerSettings?.imageUrl ?? offerSettings?.offer_image ?? defaultSettings.offer_image;

    switch (layout) {
      case 'card':
        return (
          <div className={`${styleClasses.bg} rounded-xl overflow-hidden shadow-lg ${className}`}>
            <div className="grid md:grid-cols-2">
              <div className="order-2 md:order-1 p-8 flex flex-col justify-center">
                {animations ? (
                  <>
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.1 }}>
                      <h2 className={`text-2xl md:text-3xl font-bold mb-4 ${styleClasses.text}`}>
                        {title}
                      </h2>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.2 }}>
                      <p className={`text-base md:text-lg mb-6 ${styleClasses.subtext}`}>
                        {subtitle}
                      </p>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.3 }}>
                      <Link 
                        to={buttonLink} 
                        className={`inline-block ${styleClasses.button} font-medium py-3 px-6 rounded-md transition-colors`}
                      >
                        {buttonText}
                      </Link>
                    </AnimatedElement>
                  </>
                ) : (
                  <>
                    <h2 className={`text-2xl md:text-3xl font-bold mb-4 ${styleClasses.text}`}>
                      {title}
                    </h2>
                    <p className={`text-base md:text-lg mb-6 ${styleClasses.subtext}`}>
                      {subtitle}
                    </p>
                    <Link 
                      to={buttonLink} 
                      className={`inline-block ${styleClasses.button} font-medium py-3 px-6 rounded-md transition-colors`}
                    >
                      {buttonText}
                    </Link>
                  </>
                )}
              </div>
              <div className="order-1 md:order-2">
                {image && (
                <img 
                    src={image} 
                    alt={title} 
                  className="w-full h-full object-cover object-center min-h-[200px]"
                />
                )} 
              </div>
            </div>
          </div>
        );
        
      case 'inline':
        return (
          <div className={`${styleClasses.bg} py-12 ${className}`}>
            <div className="container mx-auto px-4">
              <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                <div className="mb-6 md:mb-0 md:mr-8">
                  {animations ? (
                    <>
                      <AnimatedElement type={"fade-in"} options={{ delay: 0.1 }}>
                        <h2 className={`text-2xl font-bold ${styleClasses.text}`}>
                          {title}
                        </h2>
                      </AnimatedElement>
                      
                      <AnimatedElement type={"fade-in"} options={{ delay: 0.2 }}>
                        <p className={`mt-2 ${styleClasses.subtext}`}>
                          {subtitle}
                        </p>
                      </AnimatedElement>
                    </>
                  ) : (
                    <>
                      <h2 className={`text-2xl font-bold ${styleClasses.text}`}>
                        {title}
                      </h2>
                      <p className={`mt-2 ${styleClasses.subtext}`}>
                        {subtitle}
                      </p>
                    </>
                  )}
                </div>
                
                {animations ? (
                  <AnimatedElement type={"fade-in"} options={{ delay: 0.3 }}>
                    <Link 
                      to={buttonLink} 
                      className={`inline-block ${styleClasses.button} font-medium py-3 px-6 rounded-md transition-colors`}
                    >
                      {buttonText}
                    </Link>
                  </AnimatedElement>
                ) : (
                  <Link 
                    to={buttonLink} 
                    className={`inline-block ${styleClasses.button} font-medium py-3 px-6 rounded-md transition-colors`}
                  >
                    {buttonText}
                  </Link>
                )}
              </div>
            </div>
          </div>
        );
        
      case 'featured':
        return (
          <div className={`${styleClasses.bg} rounded-xl overflow-hidden shadow-2xl ${className}`}>
            <div className="relative">
              {image && (
              <img 
                  src={image} 
                  alt={title} 
                className="w-full h-auto"
              />
              )}
              <div className="absolute inset-0 bg-gradient-to-t from-black/80 to-black/20"></div>
              
              <div className="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                {animations ? (
                  <>
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.1 }}>
                      <span className="inline-block bg-red-600 text-white text-sm font-medium px-3 py-1 rounded-md mb-3">
                        Limited Time Offer
                      </span>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.2 }}>
                      <h2 className="text-2xl md:text-3xl lg:text-4xl font-bold text-white mb-3">
                        {title}
                      </h2>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.3 }}>
                      <p className="text-white/90 text-lg mb-5 max-w-2xl">
                        {subtitle}
                      </p>
                    </AnimatedElement>
                    
                    <AnimatedElement type={"slide-up"} options={{ delay: 0.4 }}>
                      <Link 
                        to={buttonLink} 
                        className="inline-block bg-white hover:bg-gray-100 text-red-600 font-medium py-3 px-6 rounded-md transition-colors shadow-lg"
                      >
                        {buttonText}
                      </Link>
                    </AnimatedElement>
                  </>
                ) : (
                  <>
                    <span className="inline-block bg-red-600 text-white text-sm font-medium px-3 py-1 rounded-md mb-3">
                      Limited Time Offer
                    </span>
                    <h2 className="text-2xl md:text-3xl lg:text-4xl font-bold text-white mb-3">
                      {title}
                    </h2>
                    <p className="text-white/90 text-lg mb-5 max-w-2xl">
                      {subtitle}
                    </p>
                    <Link 
                      to={buttonLink} 
                      className="inline-block bg-white hover:bg-gray-100 text-red-600 font-medium py-3 px-6 rounded-md transition-colors shadow-lg"
                    >
                      {buttonText}
                    </Link>
                  </>
                )}
              </div>
            </div>
          </div>
        );
        
      case 'banner':
      default:
        return (
          <div className={`${styleClasses.bg} py-16 md:py-24 ${className}`}>
            <div className="container mx-auto px-4">
              <div className="max-w-3xl mx-auto text-center">
                  {animations ? (
                    <>
                    <AnimatedElement type={"fade-in"} options={{ delay: 0.1 }}>
                        <h2 className={`text-3xl md:text-4xl font-bold mb-4 ${styleClasses.text}`}>
                        {title}
                        </h2>
                      </AnimatedElement>
                      
                    <AnimatedElement type={"fade-in"} options={{ delay: 0.2 }}>
                      <p className={`text-lg md:text-xl mb-8 ${styleClasses.subtext}`}>
                        {subtitle}
                        </p>
                      </AnimatedElement>
                      
                    <AnimatedElement type={"fade-in"} options={{ delay: 0.3 }}>
                        <Link 
                        to={buttonLink} 
                        className={`inline-block ${styleClasses.button} font-medium py-3 px-8 rounded-md transition-colors shadow-lg hover:shadow-xl`}
                        >
                        {buttonText}
                        </Link>
                      </AnimatedElement>
                    </>
                  ) : (
                    <>
                      <h2 className={`text-3xl md:text-4xl font-bold mb-4 ${styleClasses.text}`}>
                      {title}
                      </h2>
                    <p className={`text-lg md:text-xl mb-8 ${styleClasses.subtext}`}>
                      {subtitle}
                      </p>
                      <Link 
                      to={buttonLink} 
                      className={`inline-block ${styleClasses.button} font-medium py-3 px-8 rounded-md transition-colors shadow-lg hover:shadow-xl`}
                      >
                      {buttonText}
                      </Link>
                    </>
                  )}
              </div>
            </div>
          </div>
        );
    }
  };

  return renderOffer();
};

export default OfferSection; 
import React, { useEffect, useRef, useState } from 'react';
import AnimatedElement from './animations/AnimatedElement';

interface AboutSectionProps {
  title?: string;
  subtitle?: string;
  description?: string;
  buttonText?: string;
  buttonLink?: string;
  imageSrc?: string;
}

const AboutSection: React.FC<AboutSectionProps> = ({
  title = "About YCE Shoes",
  subtitle = "Our Story",
  description = "Crafting premium footwear since 2018, combining style, comfort and sustainability.",
  buttonText = "Learn More",
  buttonLink = "/about",
  imageSrc = "https://images.unsplash.com/photo-1556906781-9a412961c28c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80",
}) => {
  const [isInView, setIsInView] = useState(false);
  const sectionRef = useRef<HTMLElement>(null);

  // 监听滚动，检测元素是否在视口内
  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsInView(true);
          observer.disconnect();
        }
      },
      { threshold: 0.3 }
    );

    if (sectionRef.current) {
      observer.observe(sectionRef.current);
    }

    return () => {
      observer.disconnect();
    };
  }, []);

  return (
    <section 
      ref={sectionRef}
      className="py-12 md:py-16 relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-blue-900"
    >
      {/* 动态背景装饰 */}
      <div className="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden">
        <div className="absolute top-0 left-0 w-full h-full bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0aC0ydi00aDJ2NHptMC02aC0ydi00aDJ2NHptMC02aC0ydi00aDJ2NHptMC02aC0yVjZoMnY0em0wLTZoLTJ2LTRoMnY0eiIvPjwvZz48L2c+PC9zdmc+')] opacity-20"></div>
        <div className="absolute -top-24 -right-24 w-96 h-96 rounded-full border border-white/10 opacity-30"></div>
        <div className="absolute -bottom-32 -left-32 w-80 h-80 rounded-full border border-white/10 opacity-30"></div>
        <div className="absolute top-40 right-20 w-16 h-16 rounded-full bg-blue-500/30 blur-xl"></div>
        <div className="absolute bottom-20 left-40 w-20 h-20 rounded-full bg-indigo-500/30 blur-xl"></div>
      </div>

      <div className="container mx-auto px-4 relative z-10">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            {/* 图片部分 */}
            <div className="lg:col-span-5 order-2 lg:order-1">
              <div className={`relative ${isInView ? 'animate-fadeIn' : 'opacity-0'}`} style={{ animationDelay: '0.2s' }}>
                <div className="aspect-w-4 aspect-h-5 rounded-2xl overflow-hidden shadow-2xl transform rotate-2 hover:rotate-0 transition-all duration-500">
                  <img 
                    src={imageSrc} 
                    alt="About YCE Shoes" 
                    className="w-full h-full object-cover transition-all duration-700 hover:scale-110"
                  />
                  <div className="absolute inset-0 bg-gradient-to-tl from-blue-900/40 to-transparent"></div>
                </div>
                
                {/* 悬浮卡片元素 */}
                <div className={`absolute -bottom-6 -right-6 bg-gradient-to-br from-blue-600 to-indigo-800 p-4 rounded-lg shadow-xl max-w-[180px] transform ${isInView ? 'animate-slideUp' : 'opacity-0 translate-y-10'}`} style={{ animationDelay: '0.6s' }}>
                  <div className="flex items-center mb-2">
                    <div className="w-8 h-8 rounded-full bg-blue-500/30 backdrop-blur-sm flex items-center justify-center mr-2">
                      <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <span className="text-white font-bold">Since 2018</span>
                  </div>
                  <p className="text-blue-100 text-sm">Excellence in footwear design</p>
                </div>
              </div>
            </div>

            {/* 文本内容部分 */}
            <div className="lg:col-span-7 order-1 lg:order-2 text-white">
              <div className={`inline-flex items-center px-3 py-1 rounded-full bg-blue-900/50 border border-blue-500/30 backdrop-blur-sm mb-4 ${isInView ? 'animate-fadeIn' : 'opacity-0'}`}>
                <span className="w-2 h-2 rounded-full bg-blue-400 mr-2"></span>
                <span className="text-sm font-medium text-blue-300">{subtitle}</span>
              </div>
              
              <h2 className={`text-3xl md:text-4xl font-bold mb-6 ${isInView ? 'animate-slideRight' : 'opacity-0 -translate-x-10'}`} style={{ animationDelay: '0.3s' }}>
                {title}
                <span className="block mt-2 h-1 w-20 bg-gradient-to-r from-blue-400 to-indigo-500"></span>
              </h2>
              
              <div className={`text-gray-300 mb-8 max-w-xl text-lg ${isInView ? 'animate-fadeIn' : 'opacity-0'}`} style={{ animationDelay: '0.4s' }}>
                <p>{description}</p>
              </div>
              
              {/* 特点展示 */}
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
                {[
                  { icon: '🌱', title: 'Sustainable', delay: 0.5 },
                  { icon: '🛠️', title: 'Durable', delay: 0.6 },
                  { icon: '✨', title: 'Stylish', delay: 0.7 }
                ].map((feature, index) => (
                  <div 
                    key={index} 
                    className={`flex items-center space-x-3 ${isInView ? 'animate-slideUp' : 'opacity-0 translate-y-10'}`}
                    style={{ animationDelay: `${feature.delay}s` }}
                  >
                    <div className="w-10 h-10 rounded-lg bg-blue-600/20 backdrop-blur-sm flex items-center justify-center text-xl">
                      {feature.icon}
                    </div>
                    <span className="font-medium">{feature.title}</span>
                  </div>
                ))}
              </div>
              
              <div className={`${isInView ? 'animate-fadeIn' : 'opacity-0'}`} style={{ animationDelay: '0.8s' }}>
                <a
                  href={buttonLink}
                  className="inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/30 hover:scale-105 hover:-translate-y-1"
                >
                  {buttonText}
                  <svg className="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* 添加自定义动画样式 */}
      <style>{`
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        
        @keyframes slideUp {
          from { opacity: 0; transform: translateY(20px); }
          to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideRight {
          from { opacity: 0; transform: translateX(-20px); }
          to { opacity: 1; transform: translateX(0); }
        }
        
        .animate-fadeIn {
          animation: fadeIn 0.8s ease-out forwards;
        }
        
        .animate-slideUp {
          animation: slideUp 0.8s ease-out forwards;
        }
        
        .animate-slideRight {
          animation: slideRight 0.8s ease-out forwards;
        }
      `}</style>
    </section>
  );
};

export default AboutSection; 
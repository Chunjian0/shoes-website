import React, { useEffect, useState } from 'react';

const BannerCarousel = ({ images, settings = {} }) => {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isAnimating, setIsAnimating] = useState(false);
    
    // Default settings
    const {
        autoplay = true,
        delay = 5000,
        transition = 'slide',
        showIndicators = true,
        showNavigation = true
    } = settings;
    
    // Set up autoplay
    useEffect(() => {
        let interval;
        
        if (autoplay && images.length > 1) {
            interval = setInterval(() => {
                nextSlide();
            }, delay);
        }
        
        return () => {
            if (interval) {
                clearInterval(interval);
            }
        };
    }, [autoplay, delay, currentIndex, images.length]);
    
    // Handle navigation
    const nextSlide = () => {
        if (isAnimating || images.length <= 1) return;
        
        setIsAnimating(true);
        setCurrentIndex((prevIndex) => (prevIndex + 1) % images.length);
        
        // Reset animation state
        setTimeout(() => setIsAnimating(false), 700);
    };
    
    const prevSlide = () => {
        if (isAnimating || images.length <= 1) return;
        
        setIsAnimating(true);
        setCurrentIndex((prevIndex) => (prevIndex - 1 + images.length) % images.length);
        
        // Reset animation state
        setTimeout(() => setIsAnimating(false), 700);
    };
    
    const goToSlide = (index) => {
        if (isAnimating || index === currentIndex) return;
        
        setIsAnimating(true);
        setCurrentIndex(index);
        
        // Reset animation state
        setTimeout(() => setIsAnimating(false), 700);
    };
    
    // If no images, return null
    if (!images || images.length === 0) {
        return null;
    }
    
    // If only one image, render without controls
    if (images.length === 1) {
        const image = images[0];
        return (
            <div className="relative overflow-hidden">
                <div className="w-full h-auto">
                    <img 
                        src={image.image} 
                        alt={image.title} 
                        className="w-full h-auto object-cover"
                    />
                    
                    <div className="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                        <div className="container mx-auto px-4">
                            <div className="max-w-lg">
                                <h1 className="text-4xl md:text-5xl font-bold text-white mb-4">
                                    {image.title}
                                </h1>
                                <p className="text-lg text-white mb-6">
                                    {image.subtitle}
                                </p>
                                {image.button_text && (
                                    <a 
                                        href={image.button_link} 
                                        className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors"
                                    >
                                        {image.button_text}
                                    </a>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
    
    // Multiple images - render carousel
    return (
        <div className="relative overflow-hidden">
            {/* Slides container */}
            <div className="relative h-full">
                {images.map((image, index) => (
                    <div 
                        key={image.id || index}
                        className={`absolute w-full transition-transform duration-700 ease-in-out ${
                            index === currentIndex 
                                ? 'translate-x-0 z-10' 
                                : index < currentIndex 
                                    ? '-translate-x-full z-0' 
                                    : 'translate-x-full z-0'
                        }`}
                    >
                        <img 
                            src={image.image} 
                            alt={image.title} 
                            className="w-full h-auto object-cover"
                        />
                        
                        <div className="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                            <div className="container mx-auto px-4">
                                <div className="max-w-lg">
                                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-4">
                                        {image.title}
                                    </h1>
                                    <p className="text-lg text-white mb-6">
                                        {image.subtitle}
                                    </p>
                                    {image.button_text && (
                                        <a 
                                            href={image.button_link} 
                                            className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors"
                                        >
                                            {image.button_text}
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
            
            {/* Navigation buttons */}
            {showNavigation && images.length > 1 && (
                <>
                    <button 
                        onClick={prevSlide}
                        className="absolute top-1/2 left-4 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 focus:outline-none transition-colors z-20"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button 
                        onClick={nextSlide}
                        className="absolute top-1/2 right-4 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 focus:outline-none transition-colors z-20"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </>
            )}
            
            {/* Indicators */}
            {showIndicators && images.length > 1 && (
                <div className="absolute bottom-4 left-0 right-0 flex justify-center space-x-2 z-20">
                    {images.map((_, index) => (
                        <button
                            key={index}
                            onClick={() => goToSlide(index)}
                            className={`w-3 h-3 rounded-full focus:outline-none transition-colors ${
                                index === currentIndex ? 'bg-white' : 'bg-white/50 hover:bg-white/80'
                            }`}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};

export default BannerCarousel; 
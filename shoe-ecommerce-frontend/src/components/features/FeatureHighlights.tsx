import React from 'react';
import AnimatedElement from '../animations/AnimatedElement';

interface Feature {
  icon: React.ReactNode;
  title: string;
  description: string;
}

interface FeatureHighlightsProps {
  title?: string;
  subtitle?: string;
  features?: Feature[];
  bgColor?: string;
  textColor?: string;
}

const FeatureHighlights: React.FC<FeatureHighlightsProps> = ({
  title = "Why Choose Us",
  subtitle = "We offer the best experience in footwear shopping",
  features = [],
  bgColor = "bg-white",
  textColor = "text-gray-900"
}) => {
  // Default features if none provided
  const defaultFeatures: Feature[] = [
    {
      icon: (
        <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
        </svg>
      ),
      title: "Premium Quality",
      description: "Our shoes are crafted with the finest materials for maximum comfort and durability"
    },
    {
      icon: (
        <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      ),
      title: "Fast Delivery",
      description: "Get your order quickly with our expedited shipping options and efficient logistics"
    },
    {
      icon: (
        <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
        </svg>
      ),
      title: "Secure Payment",
      description: "Shop with confidence using our secure and encrypted payment methods"
    }
  ];

  const displayFeatures = features.length > 0 ? features : defaultFeatures;

  return (
    <section className={`py-16 ${bgColor}`}>
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <AnimatedElement type="fade-in">
            <h2 className={`text-3xl font-bold ${textColor} mb-4`}>{title}</h2>
            <p className="text-gray-600 max-w-2xl mx-auto">{subtitle}</p>
          </AnimatedElement>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {displayFeatures.map((feature, index) => (
            <AnimatedElement 
              key={index} 
              type="slide-up" 
              options={{ delay: 0.1 * (index + 1) }}
            >
              <div className="p-6 rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow">
                <div className="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mb-4">
                  {feature.icon}
                </div>
                <h3 className="text-xl font-bold mb-2">{feature.title}</h3>
                <p className="text-gray-600">{feature.description}</p>
              </div>
            </AnimatedElement>
          ))}
        </div>
      </div>
    </section>
  );
};

export default FeatureHighlights; 
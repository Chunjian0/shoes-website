import React from 'react';

interface LoadingIndicatorProps {
  message?: string;
}

const LoadingIndicator: React.FC<LoadingIndicatorProps> = ({ message = 'Loading...' }) => {
  return (
    <section className="py-12 md:py-16 bg-white">
      <div className="container mx-auto px-4">
        <div className="animate-pulse text-center mb-10">
          <div className="h-10 bg-gray-200 rounded-lg w-1/3 mx-auto mb-4"></div>
          <div className="h-6 bg-gray-200 rounded-lg w-1/2 mx-auto"></div>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-pulse">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="bg-gray-200 rounded-xl p-4 h-80"></div>
          ))}
        </div>
        <div className="text-center mt-8 text-gray-500">{message}</div>
      </div>
    </section>
  );
};

export default LoadingIndicator; 
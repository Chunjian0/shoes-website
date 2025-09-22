import React from 'react';

interface IconProps {
  className?: string;
}

const CheckIcon: React.FC<IconProps> = ({ className = 'w-5 h-5' }) => {
  return (
    <svg 
      className={className} 
      viewBox="0 0 24 24" 
      fill="none" 
      stroke="currentColor" 
      strokeWidth="2" 
      strokeLinecap="round" 
      strokeLinejoin="round"
    >
      <path d="M5 13l4 4L19 7" />
    </svg>
  );
};

export default CheckIcon; 
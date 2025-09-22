import React from 'react';
import { motion } from 'framer-motion';

export type SpinnerSize = 'xs' | 'sm' | 'md' | 'lg' | 'xl' | number;

interface SpinnerProps {
  size?: SpinnerSize;
  className?: string;
}

// Colors based on the image provided (approximated)
const dotColors = ["#EF4444", "#FBBF24", "#14B8A6", "#374151"]; // red-500, amber-400, teal-500, gray-700

const loadingContainerVariants = {
  start: {
    transition: {
      staggerChildren: 0.15, // Delay between each dot's animation start
    },
  },
  end: {
    transition: {
      staggerChildren: 0.15,
    },
  },
};

const loadingDotVariants = {
  start: {
    y: "0%",    // Start at baseline
    scale: 1,
  },
  end: {
    y: "-70%",  // Bounce distance (adjust percentage for desired height)
    scale: 1.1, // Optional: scale slightly when up
  },
};

const loadingDotTransition = {
  duration: 0.35, // Speed of one bounce (up or down)
  repeat: Infinity, // Repeat forever
  repeatType: "reverse" as const, // Bounce back down
  ease: "easeInOut", // Smooth easing function
};

const Spinner: React.FC<SpinnerProps> = ({
  size = 'md',
  className = '',
}) => {
  // Size mapping for the overall container (adjust if needed)
  const sizeMap: Record<string, number> = {
    xs: 16,
    sm: 20,
    md: 24,
    lg: 32,
    xl: 40,
  };

  // Calculate container size
  const mappedSize = typeof size === 'string' ? sizeMap[size] : size;
  const containerSize = mappedSize ?? sizeMap.md;

  // Calculate individual dot size based on container size
  const dotSize = Math.max(3, containerSize / 4.5); // Ensure minimum size, adjust divisor for relative size

  return (
    <div
      className={`inline-flex items-center justify-center ${className}`}
      style={{ 
        width: containerSize * 1.8, // Adjust container width if needed based on dots + spacing
        height: containerSize * 1.5 // Adjust height for bounce space 
      }}
      role="status"
      aria-label="Loading"
    >
      <motion.div
        style={{
          display: 'flex',
          justifyContent: 'space-around', // Evenly space dots
          alignItems: 'flex-end', // Align dots to bottom for bounce
          width: '100%',
          height: '60%', // Use portion of height for dots themselves
        }}
        variants={loadingContainerVariants}
        initial="start"
        animate="end"
      >
        {dotColors.map((color, index) => (
          <motion.div
            key={index}
            style={{
              width: dotSize,
              height: dotSize,
              backgroundColor: color,
              borderRadius: '50%',
            }}
            variants={loadingDotVariants}
            transition={loadingDotTransition}
          />
        ))}
      </motion.div>
    </div>
  );
};

export default Spinner; 
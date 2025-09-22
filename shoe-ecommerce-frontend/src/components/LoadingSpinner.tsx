import React from 'react';
import Spinner from './ui/loading/Spinner';

interface LoadingSpinnerProps {
  size?: 'xs' | 'small' | 'medium' | 'large' | 'lg' | number;
  /**
   * @deprecated The color prop is no longer used as the underlying Spinner controls its own colors.
   */
  color?: 'primary' | 'white' | 'gray';
  className?: string;
}

/**
 * Displays a styled loading indicator.
 * Wraps the ui/loading/Spinner component for potential backward compatibility or future adaptation.
 */
const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({
  size = 'medium',
  // color prop is intentionally not used here as Spinner defines its own colors
  className = '',
}) => {
  // Map the legacy size names to the sizes expected by the new Spinner component
  const sizeMap = {
    xs: 'xs',
    small: 'sm',
    medium: 'md',
    large: 'lg',
    lg: 'xl', // Map 'lg' to 'xl' or 'lg' based on desired visual outcome
  } as const;

  // Convert the size prop value
  const mappedSize = typeof size === 'string' ? sizeMap[size] : size;

  return (
    <Spinner
      size={mappedSize}
      className={className}
    />
  );
};

export default LoadingSpinner; 
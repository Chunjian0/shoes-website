import React, { useState, useEffect, useCallback } from 'react';
import Slider from 'rc-slider';
import 'rc-slider/assets/index.css';

interface PriceRangeSliderProps {
  min: number;
  max: number;
  step: number;
  minValue?: number; // Use number for initial values
  maxValue?: number; // Use number for initial values
  onChange: (min: number | string, max: number | string) => void; // Keep string for final callback? Or enforce number? Let's keep string for flexibility with API.
  disabled?: boolean; // Add disabled prop
  // Optional class names for deeper customization if needed
  // trackClassName?: string;
  // handleClassName?: string;
  // rangeClassName?: string;
}

// Debounce function
const debounce = <F extends (...args: any[]) => any>(func: F, waitFor: number) => {
  let timeout: ReturnType<typeof setTimeout> | null = null;

  const debounced = (...args: Parameters<F>) => {
    if (timeout !== null) {
      clearTimeout(timeout);
      timeout = null;
    }
    timeout = setTimeout(() => func(...args), waitFor);
  };

  return debounced;
};


const PriceRangeSlider: React.FC<PriceRangeSliderProps> = ({
  min,
  max,
  step,
  minValue,
  maxValue,
  onChange,
  disabled = false, // Default to not disabled
}) => {
  // rc-slider uses an array [min, max] for range value
  const [value, setValue] = useState<[number, number]>([
    minValue !== undefined ? Math.max(min, minValue) : min,
    maxValue !== undefined ? Math.min(max, maxValue) : max,
  ]);
  
  // Update internal state if external values change
  useEffect(() => {
    setValue([
      minValue !== undefined ? Math.max(min, minValue) : min,
      maxValue !== undefined ? Math.min(max, maxValue) : max,
    ]);
  }, [minValue, maxValue, min, max]);
  
  // Debounced onChange handler for rc-slider (value is [number, number])
  const debouncedOnChange = useCallback(
    debounce((newValue: [number, number]) => {
        // Ensure values are within bounds before calling external onChange
        const finalMin = Math.max(min, newValue[0]);
        const finalMax = Math.min(max, newValue[1]);
        onChange(String(finalMin), String(finalMax));
    }, 500),
    [onChange, min, max]
  );

  // rc-slider onChange provides the new value array directly
  const handleSliderChange = (newValue: number | number[]) => {
    if (disabled || !Array.isArray(newValue)) return; // Prevent changes when disabled or invalid value type
    
    // Ensure values are within bounds for immediate visual feedback
    const boundedValue: [number, number] = [
        Math.max(min, newValue[0]),
        Math.min(max, newValue[1]),
    ];
    setValue(boundedValue); // Update visual state immediately
    // Call the debounced function for external update
    debouncedOnChange(boundedValue);
  };

  // Handlers for direct input changes
  const handleMinInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      if (disabled) return;
      let newMin = parseInt(e.target.value, 10);
      if (isNaN(newMin)) newMin = min;
      // Clamp between min and current max (value[1])
      newMin = Math.max(min, Math.min(newMin, value[1])); 
      
      const newValue: [number, number] = [newMin, value[1]];
      setValue(newValue);
      debouncedOnChange(newValue);
  };

  const handleMaxInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      if (disabled) return;
      let newMax = parseInt(e.target.value, 10);
      if (isNaN(newMax)) newMax = max;
      // Clamp between max and current min (value[0])
      newMax = Math.min(max, Math.max(newMax, value[0])); 

      const newValue: [number, number] = [value[0], newMax];
      setValue(newValue);
      debouncedOnChange(newValue);
  };

  // Custom styles using accent color
  const railStyle = {
    backgroundColor: '#e5e7eb', // Light gray rail
    height: '4px',
  };
  const trackStyle = {
    // Use accent color for the track
    backgroundColor: disabled ? '#a5b4fc' : '#4f46e5', // Indigo-300 disabled, Indigo-600 enabled
    height: '4px',
  };
  const handleStyle = {
    // Use accent color for the handle border/background
    backgroundColor: disabled ? '#a5b4fc' : '#4f46e5', // Indigo-300 disabled, Indigo-600 enabled
    borderColor: disabled ? '#a5b4fc' : '#3730a3',    // Indigo-300 disabled, Indigo-800 enabled
    height: '16px',
    width: '16px',
    marginTop: '-6px',
    boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
    opacity: 1,
  };
  const activeDotStyle = {
    // Use accent color for active dots
    borderColor: disabled ? '#a5b4fc' : '#3730a3', // Indigo-300 disabled, Indigo-800 enabled
    backgroundColor: '#fff',
  }
  
  return (
    <div className={`space-y-4 price-range-slider-container ${disabled ? 'opacity-60 cursor-not-allowed' : ''}`}>
      <Slider
        range // Enable range mode
        min={min}
        max={max}
        step={step}
        value={value} // Pass the [min, max] array
        onChange={handleSliderChange} // Update handler
        disabled={disabled}
        allowCross={false} // Prevent handles from crossing
        // Apply custom styles via props
        railStyle={railStyle}
        trackStyle={trackStyle} // rc-slider uses a single track style for the active range
        handleStyle={[handleStyle, handleStyle]} // Apply same style to both handles
        // Apply accent color to active dots
        activeDotStyle={activeDotStyle}
      />
      <div className="flex justify-between items-center text-sm text-gray-700 mt-2">
        {/* Min Input Box */}
        <div className={`flex items-center border rounded-md px-2 py-1 bg-white ${disabled ? 'border-gray-200 bg-gray-50' : 'border-gray-300'}`}>
            <span className={`${disabled ? 'text-gray-400' : 'text-gray-500'}`}>$</span>
          <input
            type="number"
                min={min}
                max={value[1]} // Dynamic max based on slider max handle
                step={step}
                value={value[0]} // Use value[0] for min input
                onChange={handleMinInputChange}
                disabled={disabled}
                className={`w-16 text-right border-none focus:ring-0 p-0 ml-1 text-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none ${disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'text-gray-800'}`}
                aria-label="Minimum price"
          />
        </div>
        <span className={`${disabled ? 'text-gray-300' : 'text-gray-400'}`}>-</span>
        {/* Max Input Box */}
        <div className={`flex items-center border rounded-md px-2 py-1 bg-white ${disabled ? 'border-gray-200 bg-gray-50' : 'border-gray-300'}`}>
             <span className={`${disabled ? 'text-gray-400' : 'text-gray-500'}`}>$</span>
          <input
            type="number"
                min={value[0]} // Dynamic min based on slider min handle
                max={max}
                step={step}
                value={value[1]} // Use value[1] for max input
                onChange={handleMaxInputChange}
                disabled={disabled}
                className={`w-16 text-right border-none focus:ring-0 p-0 ml-1 text-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none ${disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'text-gray-800'}`}
                aria-label="Maximum price"
          />
        </div>
      </div>
    </div>
  );
};

export default PriceRangeSlider; 
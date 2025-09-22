import React, { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

interface SearchInputProps {
  placeholder?: string;
  value?: string;
  onChange?: (value: string) => void;
  onSearch?: (value: string) => void;
  className?: string;
  autoFocus?: boolean;
  variant?: 'default' | 'minimal' | 'luxury';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  clearable?: boolean;
  loading?: boolean;
  icon?: React.ReactNode;
}

/**
 * 奢华风格的搜索输入框组件
 * 提供优雅的动画效果和高端视觉体验
 */
const SearchInput: React.FC<SearchInputProps> = ({
  placeholder = 'Search products...',
  value = '',
  onChange,
  onSearch,
  className = '',
  autoFocus = false,
  variant = 'default',
  size = 'md',
  disabled = false,
  clearable = true,
  loading = false,
  icon,
}) => {
  const [inputValue, setInputValue] = useState(value);
  const [isFocused, setIsFocused] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    setInputValue(value);
  }, [value]);

  // 当autoFocus为true时，自动聚焦输入框
  useEffect(() => {
    if (autoFocus && inputRef.current) {
      inputRef.current.focus();
    }
  }, [autoFocus]);

  // 变体样式配置
  const variantClasses = {
    default: `bg-white dark:bg-gray-900 border-b-2 ${
      isFocused 
        ? 'border-amber-500 dark:border-amber-400' 
        : 'border-gray-300 dark:border-gray-700'
    }`,
    minimal: `bg-transparent border-b ${
      isFocused 
        ? 'border-amber-500 dark:border-amber-400' 
        : 'border-gray-300 dark:border-gray-700'
    }`,
    luxury: `bg-gradient-to-r from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 border-b-2 ${
      isFocused 
        ? 'border-amber-500 dark:border-amber-400 shadow-lg' 
        : 'border-gray-300 dark:border-gray-700'
    }`,
  };

  // 尺寸样式配置
  const sizeClasses = {
    sm: 'h-9 text-sm',
    md: 'h-11 text-base',
    lg: 'h-14 text-lg',
  };

  // 处理输入变化
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newValue = e.target.value;
    setInputValue(newValue);
    onChange?.(newValue);
  };

  // 处理按键事件（回车触发搜索）
  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter' && onSearch) {
      onSearch(inputValue);
    }
  };
  
  // 清除输入
  const handleClear = () => {
    setInputValue('');
    onChange?.('');
    inputRef.current?.focus();
  };
  
  // 触发搜索
  const handleSearchClick = () => {
    if (onSearch) {
      onSearch(inputValue);
    }
  };

  // 搜索图标动画变体
  const iconVariants = {
    idle: { scale: 1 },
    hover: { scale: 1.1 },
    focused: { scale: 1.1, rotate: 5, transition: { duration: 0.2 } },
    unfocused: { scale: 1, rotate: 0, transition: { duration: 0.2 } }
  };

  return (
    <div
      className={`relative flex items-center ${variantClasses[variant]} ${sizeClasses[size]} px-4 
      transition-all duration-300 rounded-t-md ${className}`}
    >
      {/* 搜索图标 */}
      <motion.div 
        className="mr-3 flex-shrink-0 text-gray-500 dark:text-gray-400"
        variants={iconVariants}
        initial="idle"
        animate={isFocused ? 'focused' : 'unfocused'}
        whileHover="hover"
        onClick={handleSearchClick}
        style={{ cursor: onSearch ? 'pointer' : 'default' }}
      >
        {icon || (
          <svg 
            className={`${size === 'sm' ? 'w-4 h-4' : size === 'md' ? 'w-5 h-5' : 'w-6 h-6'}`} 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={1.5}
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        )}
      </motion.div>

      {/* 输入框 */}
      <input
        ref={inputRef}
        type="text"
        className={`w-full h-full bg-transparent outline-none font-montserrat
          text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500
          disabled:text-gray-400 disabled:cursor-not-allowed`}
        placeholder={placeholder}
        value={inputValue}
        onChange={handleChange}
        onKeyDown={handleKeyDown}
        onFocus={() => setIsFocused(true)}
        onBlur={() => setIsFocused(false)}
        disabled={disabled}
      />

      {/* 清除按钮和加载指示器 */}
      <div className="ml-2 flex-shrink-0">
        <AnimatePresence>
          {loading ? (
            <motion.div
              initial={{ opacity: 0, scale: 0.8 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.8 }}
              className="flex items-center justify-center"
            >
              <svg
                className={`animate-spin text-amber-500 dark:text-amber-400 ${
                  size === 'sm' ? 'w-4 h-4' : size === 'md' ? 'w-5 h-5' : 'w-6 h-6'
                }`}
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  className="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  strokeWidth="4"
                ></circle>
                <path
                  className="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
            </motion.div>
          ) : (
            clearable && inputValue && (
              <motion.button
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.8 }}
                whileHover={{ scale: 1.1 }}
                whileTap={{ scale: 0.95 }}
                onClick={handleClear}
                className="text-gray-400 hover:text-gray-500 dark:text-gray-600 dark:hover:text-gray-500 focus:outline-none"
                aria-label="Clear search"
              >
                <svg 
                  className={`${size === 'sm' ? 'w-4 h-4' : size === 'md' ? 'w-5 h-5' : 'w-6 h-6'}`} 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24" 
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </motion.button>
            )
          )}
        </AnimatePresence>
      </div>

      {/* 底部装饰线：聚焦时展开效果 */}
      <motion.div
        className="absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-amber-400 via-amber-500 to-amber-400 dark:from-amber-400 dark:via-amber-300 dark:to-amber-400"
        initial={{ width: '0%' }}
        animate={{ width: isFocused ? '100%' : '0%' }}
        transition={{ duration: 0.3, ease: [0.33, 1, 0.68, 1] }}
      />
    </div>
  );
};

export default SearchInput; 
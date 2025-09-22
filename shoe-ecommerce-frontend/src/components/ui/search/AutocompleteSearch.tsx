import React, { useState, useRef, useCallback, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import SearchInput from './SearchInput';
import SearchResults, { SearchResultItem } from './SearchResults';
import useDebounce from '../../../hooks/useDebounce';
import useClickOutside from '../../../hooks/useClickOutside';

interface AutocompleteSearchProps {
  onSearch?: (value: string) => void;
  onItemClick?: (item: SearchResultItem) => void;
  fetchResults?: (query: string) => Promise<SearchResultItem[]>;
  placeholder?: string;
  minChars?: number;
  delay?: number;
  className?: string;
  variant?: 'default' | 'minimal' | 'luxury';
  size?: 'sm' | 'md' | 'lg';
  autoFocus?: boolean;
  recentSearches?: SearchResultItem[];
  popularSearches?: SearchResultItem[];
  categories?: { [key: string]: string };
  maxHeight?: string | number;
  showImages?: boolean;
}

/**
 * 自动完成搜索组件
 * 包含搜索输入框和结果展示，支持实时搜索和建议展示
 */
const AutocompleteSearch: React.FC<AutocompleteSearchProps> = ({
  onSearch,
  onItemClick,
  fetchResults,
  placeholder = 'Search for luxury shoes...',
  minChars = 2,
  delay = 300,
  className = '',
  variant = 'luxury',
  size = 'md',
  autoFocus = false,
  recentSearches = [],
  popularSearches = [],
  categories = {},
  maxHeight = '400px',
  showImages = true,
}) => {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<SearchResultItem[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | undefined>();
  const [isFocused, setIsFocused] = useState(false);
  const [showResults, setShowResults] = useState(false);
  
  const containerRef = useRef<HTMLDivElement>(null);
  const debouncedQuery = useDebounce(query, delay);

  // 处理输入变化
  const handleInputChange = (value: string) => {
    setQuery(value);
    if (value.length >= minChars) {
      setShowResults(true);
    }
  };

  // 处理外部点击，关闭结果面板
  useClickOutside(containerRef, () => {
    setShowResults(false);
  });

  // 处理搜索结果项点击
  const handleResultClick = (item: SearchResultItem) => {
    setQuery(item.name);
    setShowResults(false);
    onItemClick?.(item);
  };

  // 处理搜索提交
  const handleSearch = (value: string) => {
    onSearch?.(value);
    setShowResults(false);
  };

  // 键盘导航支持
  const handleKeyDown = (e: React.KeyboardEvent) => {
    // 按Escape键关闭结果面板
    if (e.key === 'Escape') {
      setShowResults(false);
    }
    
    // 可以在这里添加更多键盘导航功能，如上下键选择结果等
  };

  // 获取搜索结果
  const fetchSearchResults = useCallback(async () => {
    if (!fetchResults || debouncedQuery.length < minChars) {
      // 查询为空或太短时显示最近和热门搜索
      if (debouncedQuery.length === 0) {
        const combined = [
          ...recentSearches.map(item => ({ ...item, category: 'Recent Searches' })),
          ...popularSearches.map(item => ({ ...item, category: 'Popular Searches' })),
        ];
        setResults(combined);
      } else {
        setResults([]);
      }
      setIsLoading(false);
      return;
    }

    setIsLoading(true);
    setError(undefined);

    try {
      const data = await fetchResults(debouncedQuery);
      setResults(data);
    } catch (err) {
      setError('Failed to fetch results. Please try again.');
      setResults([]);
    } finally {
      setIsLoading(false);
    }
  }, [debouncedQuery, fetchResults, minChars, popularSearches, recentSearches]);

  // 当查询变化时获取结果
  useEffect(() => {
    fetchSearchResults();
  }, [debouncedQuery, fetchResults, minChars, popularSearches, recentSearches]);

  // 组件挂载时设置焦点
  useEffect(() => {
    if (autoFocus) {
      setIsFocused(true);
    }
  }, [autoFocus]);

  // 结果面板的动画变体
  const resultsVariants = {
    hidden: { opacity: 0, y: 10, scale: 0.98 },
    visible: { 
      opacity: 1, 
      y: 0, 
      scale: 1,
      transition: { duration: 0.2, ease: 'easeOut' }
    },
    exit: { 
      opacity: 0, 
      y: 5, 
      scale: 0.98,
      transition: { duration: 0.15, ease: 'easeIn' }
    }
  };

  return (
    <div 
      ref={containerRef} 
      className={`relative ${className}`}
      onKeyDown={handleKeyDown}
    >
      {/* 搜索输入框 */}
      <SearchInput
        value={query}
        onChange={handleInputChange}
        onSearch={handleSearch}
        placeholder={placeholder}
        autoFocus={autoFocus}
        variant={variant}
        size={size}
        loading={isLoading}
        clearable={true}
      />

      {/* 搜索结果面板 */}
      <AnimatePresence>
        {showResults && (isFocused || query.length >= minChars) && (
          <motion.div
            className="absolute left-0 right-0 z-50 mt-1"
            variants={resultsVariants}
            initial="hidden"
            animate="visible"
            exit="exit"
          >
            <SearchResults
              results={results}
              isLoading={isLoading}
              error={error}
              onItemClick={handleResultClick}
              categories={{
                'Recent Searches': 'Recent Searches',
                'Popular Searches': 'Popular Searches',
                ...categories
              }}
              maxHeight={maxHeight}
              showImages={showImages}
              emptyMessage={
                query.length < minChars && query.length > 0
                  ? `Type at least ${minChars} characters to search`
                  : 'No results found. Try a different search.'
              }
            />
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default AutocompleteSearch; 
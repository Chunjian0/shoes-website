import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import EmptyState from '../feedback/EmptyState';

export interface SearchResultItem {
  id: string | number;
  name: string;
  image?: string;
  price: number;
  originalPrice?: number;
  category?: string;
  brand?: string;
  description?: string;
  slug?: string;
}

interface SearchResultsProps {
  results: SearchResultItem[];
  isLoading?: boolean;
  error?: string;
  onItemClick?: (item: SearchResultItem) => void;
  categories?: { [key: string]: string };
  emptyMessage?: string;
  className?: string;
  maxHeight?: string | number;
  groupByCategory?: boolean;
  showImages?: boolean;
}

/**
 * 奢华风格的搜索结果组件
 * 支持分类展示和优雅的视觉效果
 */
const SearchResults: React.FC<SearchResultsProps> = ({
  results,
  isLoading = false,
  error,
  onItemClick,
  categories = {},
  emptyMessage = 'No results found. Try a different search.',
  className = '',
  maxHeight = '400px',
  groupByCategory = true,
  showImages = true,
}) => {
  // 动画变体
  const containerVariants = {
    hidden: { opacity: 0, y: -20 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: {
        duration: 0.3,
        when: "beforeChildren",
        staggerChildren: 0.05
      }
    },
    exit: { 
      opacity: 0,
      y: -20,
      transition: { 
        duration: 0.2,
        when: "afterChildren",
        staggerChildren: 0.02,
        staggerDirection: -1
      }
    }
  };

  const itemVariants = {
    hidden: { opacity: 0, y: 10 },
    visible: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: 5 }
  };

  // 骨架屏加载状态
  const renderSkeletons = () => (
    <div className="px-4 py-3 space-y-4">
      {[...Array(3)].map((_, index) => (
        <div key={index} className="flex items-center space-x-3">
          <div className="w-12 h-12 bg-gray-200 dark:bg-gray-800 rounded-md animate-pulse"></div>
          <div className="flex-1 space-y-2">
            <div className="h-4 bg-gray-200 dark:bg-gray-800 rounded w-3/4 animate-pulse"></div>
            <div className="h-3 bg-gray-200 dark:bg-gray-800 rounded w-1/2 animate-pulse"></div>
          </div>
        </div>
      ))}
    </div>
  );

  // 错误状态
  const renderError = () => (
    <div className="p-4 text-center">
      <p className="text-red-500 dark:text-red-400 text-sm">{error}</p>
    </div>
  );

  // 空结果状态
  const renderEmpty = () => (
    <div className="px-4 py-6">
      <EmptyState
        title="No results"
        message={emptyMessage}
        variant="minimal"
        animated={true}
      />
    </div>
  );

  // 按类别分组结果
  const groupedResults = () => {
    if (!groupByCategory) return { 'All Results': results };
    
    return results.reduce<{ [key: string]: SearchResultItem[] }>((groups, item) => {
      const category = item.category || 'Other';
      if (!groups[category]) {
        groups[category] = [];
      }
      groups[category].push(item);
      return groups;
    }, {});
  };

  // 格式化价格
  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2
    }).format(price);
  };

  // 渲染单个结果项
  const renderResultItem = (item: SearchResultItem) => (
    <motion.div
      key={item.id}
      variants={itemVariants}
      className="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors duration-150 cursor-pointer rounded-md"
      onClick={() => onItemClick?.(item)}
      whileHover={{ x: 4 }}
      whileTap={{ scale: 0.98 }}
    >
      <div className="flex items-center space-x-3">
        {showImages && (
          <div className="flex-shrink-0">
            {item.image ? (
              <div className="w-14 h-14 rounded-md overflow-hidden border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
                <img 
                  src={item.image} 
                  alt={item.name} 
                  className="w-full h-full object-cover"
                  loading="lazy"
                />
              </div>
            ) : (
              <div className="w-14 h-14 rounded-md bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            )}
          </div>
        )}
        
        <div className="flex-1 min-w-0">
          <h4 className="text-sm font-medium text-gray-900 dark:text-white truncate">
            {item.name}
          </h4>
          
          {item.brand && (
            <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
              {item.brand}
            </p>
          )}
          
          <div className="mt-1 flex items-center">
            <span className="text-sm font-semibold text-gray-900 dark:text-white font-didot">
              {formatPrice(item.price)}
            </span>
            
            {item.originalPrice && item.originalPrice > item.price && (
              <span className="ml-2 text-xs line-through text-gray-500 dark:text-gray-500 font-didot">
                {formatPrice(item.originalPrice)}
              </span>
            )}
          </div>
        </div>
        
        <div className="flex-shrink-0 text-gray-400">
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </div>
    </motion.div>
  );

  // 渲染搜索结果
  const renderResults = () => {
    const grouped = groupedResults();
    
    return (
      <div className="py-2">
        {Object.entries(grouped).map(([category, items]) => (
          <div key={category} className="mb-2 last:mb-0">
            {groupByCategory && Object.keys(grouped).length > 1 && (
              <div className="px-4 py-2 sticky top-0 bg-gray-50 dark:bg-gray-950 border-y border-gray-200 dark:border-gray-800 z-10">
                <h3 className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  {categories[category] || category}
                </h3>
              </div>
            )}
            
            <div className="divide-y divide-gray-100 dark:divide-gray-900">
              {items.map(renderResultItem)}
            </div>
          </div>
        ))}
      </div>
    );
  };

  return (
    <div 
      className={`bg-white dark:bg-gray-950 rounded-md shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden ${className}`}
      style={{ maxHeight }}
    >
      <AnimatePresence mode="wait">
        {isLoading ? (
          <motion.div
            key="loading"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
          >
            {renderSkeletons()}
          </motion.div>
        ) : error ? (
          <motion.div
            key="error"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
          >
            {renderError()}
          </motion.div>
        ) : results.length === 0 ? (
          <motion.div
            key="empty"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
          >
            {renderEmpty()}
          </motion.div>
        ) : (
          <motion.div
            key="results"
            className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent"
            style={{ maxHeight }}
            variants={containerVariants}
            initial="hidden"
            animate="visible"
            exit="exit"
          >
            {renderResults()}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default SearchResults; 
import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { FiX } from 'react-icons/fi';

interface FilterItem {
  type: string;
  label: string;
  value: string;
}

interface ActiveFiltersProps {
  activeFilters: FilterItem[];
  onRemoveFilter: (type: string) => void;
  onClearAll: () => void;
  categories: Array<{id: number; name: string}>;
}

const ActiveFilters: React.FC<ActiveFiltersProps> = ({
  activeFilters,
  onRemoveFilter,
  onClearAll,
  categories
}) => {
  if (activeFilters.length === 0) {
    return null;
  }
  
  // 获取分类名称
  const getCategoryName = (categoryId: string) => {
    const category = categories.find(cat => cat.id.toString() === categoryId);
    return category ? category.name : categoryId;
  };
  
  // 格式化筛选项标签
  const getFilterLabel = (filter: FilterItem) => {
    switch (filter.type) {
      case 'category':
        return `Category: ${getCategoryName(filter.value)}`;
      case 'minPrice':
        return `Min Price: $${filter.value}`;
      case 'maxPrice':
        return `Max Price: $${filter.value}`;
      case 'sort':
        const sortLabels: {[key: string]: string} = {
          'newest': 'Newest First',
          'price_asc': 'Price: Low to High',
          'price_desc': 'Price: High to Low',
          'popularity': 'Popularity'
        };
        return `Sort: ${sortLabels[filter.value] || filter.value}`;
      default:
        return `${filter.type}: ${filter.value}`;
    }
  };
  
  // 定义动画变体
  const itemVariants = {
    initial: { opacity: 0, scale: 0.9, y: 10 },
    animate: { 
      opacity: 1, 
      scale: 1, 
      y: 0,
      transition: { 
        duration: 0.25,
        ease: [0.33, 1, 0.68, 1]
      }
    },
    exit: { 
      opacity: 0, 
      scale: 0.9, 
      y: -10,
      transition: { 
        duration: 0.2,
        ease: [0.33, 1, 0.68, 1]
      }
    }
  };
  
  return (
    <div className="mb-6">
      <div className="flex flex-wrap items-center gap-2 mb-3">
        <AnimatePresence>
          {activeFilters.map((filter) => (
            <motion.div
              key={`${filter.type}-${filter.value}`}
              variants={itemVariants}
              initial="initial"
              animate="animate"
              exit="exit"
              className="inline-flex items-center rounded-full px-3 py-1.5 text-sm"
              style={{
                background: 'linear-gradient(135deg, rgba(10,10,10,0.04) 0%, rgba(212,175,55,0.08) 100%)',
                border: '1px solid rgba(212, 175, 55, 0.2)',
                color: '#0A0A0A',
                fontFamily: 'Montserrat, sans-serif'
              }}
            >
              <span className="mr-1" style={{ fontWeight: 500 }}>{getFilterLabel(filter)}</span>
              <motion.button
                onClick={() => onRemoveFilter(filter.type)}
                className="focus:outline-none"
                aria-label={`Remove ${filter.type} filter`}
                whileHover={{ scale: 1.2 }}
                whileTap={{ scale: 0.9 }}
                style={{ color: '#D4AF37' }}
              >
                <FiX size={16} />
              </motion.button>
            </motion.div>
          ))}
        </AnimatePresence>
        
        {activeFilters.length > 1 && (
          <motion.button
            onClick={onClearAll}
            className="text-sm font-medium focus:outline-none"
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            style={{ 
              color: '#D4AF37',
              fontFamily: 'Montserrat, sans-serif',
              letterSpacing: '0.02em',
              position: 'relative'
            }}
          >
            Clear All
            <motion.div 
              style={{ 
                position: 'absolute',
                bottom: '-2px',
                left: 0,
                right: 0,
                height: '1px',
                background: '#D4AF37',
                transformOrigin: 'left',
                scaleX: 0
              }}
              whileHover={{ scaleX: 1 }}
              transition={{ duration: 0.3 }}
            />
          </motion.button>
        )}
      </div>
    </div>
  );
};

export default ActiveFilters; 
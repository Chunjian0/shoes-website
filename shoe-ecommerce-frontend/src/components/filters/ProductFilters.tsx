import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { FiFilter, FiX, FiRefreshCw, FiChevronDown } from 'react-icons/fi';

// 筛选器接口
export interface ProductFiltersProps {
  categories: Array<{id: number; name: string}>;
  initialFilters: {
    category: string;
    minPrice: string;
    maxPrice: string;
    sort: string;
  };
  onFilterChange: (name: string, value: string) => void;
  onReset: () => void;
  onRefresh: () => void;
  className?: string;
  isRefreshing?: boolean;
  isMobile?: boolean;
}

const ProductFilters: React.FC<ProductFiltersProps> = ({
  categories,
  initialFilters,
  onFilterChange,
  onReset,
  onRefresh,
  className = '',
  isRefreshing = false,
  isMobile = false
}) => {
  const [filters, setFilters] = useState(initialFilters);
  const [expanded, setExpanded] = useState(!isMobile);
  
  // 当父组件传递的初始筛选器更新时，更新本地状态
  useEffect(() => {
    setFilters(initialFilters);
  }, [initialFilters]);
  
  // 处理筛选器变更
  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
    onFilterChange(name, value);
  };
  
  // 处理价格范围变更
  const handlePriceChange = (min: number | string, max: number | string) => {
    // 确保价格值是有效的
    const validMin = min !== undefined && min !== '' && !isNaN(Number(min));
    const validMax = max !== undefined && max !== '' && !isNaN(Number(max));
    
    // 更新本地状态
    setFilters(prev => ({ 
      ...prev, 
      minPrice: validMin ? min.toString() : '', 
      maxPrice: validMax ? max.toString() : '' 
    }));
    
    // 通知父组件
    if (validMin) {
      onFilterChange('minPrice', min.toString());
    } else {
      onFilterChange('minPrice', '');
    }
    
    if (validMax) {
      onFilterChange('maxPrice', max.toString());
    } else {
      onFilterChange('maxPrice', '');
    }
  };
  
  // 切换展开/折叠状态
  const toggleExpanded = () => {
    setExpanded(!expanded);
  };
  
  // 筛选面板动画
  const filterPanelVariants = {
    hidden: { 
      height: 0,
      opacity: 0,
      transition: { 
        duration: 0.3,
        ease: [0.33, 1, 0.68, 1]
      }
    },
    visible: { 
      height: 'auto',
      opacity: 1,
      transition: { 
        duration: 0.4,
        ease: [0.33, 1, 0.68, 1]
      }
    }
  };
  
  // 按钮动画变体
  const buttonVariants = {
    hover: { 
      scale: 1.03, 
      transition: { duration: 0.3, ease: [0.33, 1, 0.68, 1] }
    },
    tap: { 
      scale: 0.98, 
      transition: { duration: 0.1, ease: [0.33, 1, 0.68, 1] }
    }
  };
  
  // 过滤器重置
  const handleReset = () => {
    onReset();
  };
  
  return (
    <div className={`rounded-md ${className}`} style={{
      background: 'linear-gradient(145deg, #ffffff, #fefdf8)',
      border: '1px solid rgba(212, 175, 55, 0.3)',
      boxShadow: '0 10px 30px rgba(0, 0, 0, 0.08), 0 1px 5px rgba(0, 0, 0, 0.03)',
      borderRadius: '4px'
    }}>
      {/* 筛选面板标题栏 */}
      <div className="p-6 flex justify-between items-center" style={{
        borderBottom: '1px solid rgba(212, 175, 55, 0.2)'
      }}>
        <h2 className="flex items-center" style={{
          fontFamily: 'Playfair Display, serif',
          fontSize: '18px',
          lineHeight: '1.3',
          letterSpacing: '-0.02em',
          color: '#0A0A0A'
        }}>
          <FiFilter size={18} className="mr-3 text-[#D4AF37]" />
          Refined Search
        </h2>
        {isMobile && (
          <motion.button
            onClick={toggleExpanded}
            className="focus:outline-none"
            aria-expanded={expanded}
            variants={buttonVariants}
            whileHover="hover"
            whileTap="tap"
            style={{ color: '#D4AF37' }}
          >
            <motion.div
              animate={{ rotate: expanded ? 180 : 0 }}
              transition={{ duration: 0.3, ease: [0.33, 1, 0.68, 1] }}
            >
              <FiChevronDown size={20} />
            </motion.div>
          </motion.button>
        )}
      </div>
      
      {/* 筛选面板内容 */}
      <AnimatePresence>
        {(expanded || !isMobile) && (
          <motion.div
            variants={filterPanelVariants}
            initial="hidden"
            animate="visible"
            exit="hidden"
          >
            <div className="p-6 space-y-8">
              {/* 分类筛选 */}
              <div className="mb-6">
                <label htmlFor="category" className="block mb-4" style={{
                  fontFamily: 'Playfair Display, serif',
                  fontSize: '16px',
                  letterSpacing: '-0.02em',
                  color: '#0A0A0A'
                }}>
                  Category
                </label>
                <div className="relative">
                  <select
                    id="category"
                    name="category"
                    value={filters.category}
                    onChange={handleFilterChange}
                    className="w-full px-3 py-2.5 appearance-none focus:outline-none text-sm"
                    style={{
                      fontFamily: 'Montserrat, sans-serif',
                      border: 'none',
                      borderBottom: '1px solid rgba(212, 175, 55, 0.3)',
                      background: 'transparent',
                      color: '#0A0A0A',
                      cursor: 'pointer',
                      transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                    }}
                    onFocus={(e) => {
                      e.target.style.borderBottom = '2px solid #D4AF37';
                    }}
                    onBlur={(e) => {
                      e.target.style.borderBottom = '1px solid rgba(212, 175, 55, 0.3)';
                    }}
                  >
                    <option value="">All Categories</option>
                    {categories.map((category) => (
                      <option key={category.id} value={category.id.toString()}>
                        {category.name}
                      </option>
                    ))}
                  </select>
                  <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2">
                    <motion.svg 
                      width="12" 
                      height="12" 
                      viewBox="0 0 12 12"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                      className="transform transition-transform duration-300"
                      animate={{ rotate: 0 }}
                      style={{ color: '#D4AF37' }}
                    >
                      <path
                        d="M2.5 4.5L6 8L9.5 4.5"
                        stroke="currentColor"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      />
                    </motion.svg>
                  </div>
                </div>
              </div>
              
              {/* 价格范围 */}
              {/*
              <div className="mb-6">
                <label className="block mb-4" style={{ ... }}>
                  Price Range
                </label>
                <PriceRangeSlider
                  minPrice={filters.minPrice}
                  maxPrice={filters.maxPrice}
                  absoluteMin={0}
                  absoluteMax={5000}
                  onChange={handlePriceChange}
                  currencySymbol="$"
                />
              </div>
              */}
              
              {/* 排序选项 */}
              {/*
              <div className="mb-6">
                <SortSelect
                  value={filters.sort}
                  onChange={(value) => {
                    setFilters(prev => ({ ...prev, sort: value }));
                    onFilterChange('sort', value);
                  }}
                />
              </div>
              */}
              
              {/* 操作按钮 */}
              <div className="grid grid-cols-1 gap-4 pt-4" style={{
                borderTop: '1px solid rgba(212, 175, 55, 0.2)'
              }}>
                <motion.button
                  onClick={onRefresh}
                  className="w-full py-3 flex items-center justify-center"
                  variants={buttonVariants}
                  whileHover="hover"
                  whileTap="tap"
                  disabled={isRefreshing}
                  style={{
                    background: '#0A0A0A',
                    color: '#D4AF37',
                    fontFamily: 'Montserrat, sans-serif',
                    fontSize: '14px',
                    fontWeight: 500,
                    letterSpacing: '0.05em',
                    border: 'none',
                    boxShadow: '0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1)',
                    textTransform: 'uppercase'
                  }}
                >
                  <FiRefreshCw className={`mr-2 ${isRefreshing ? "animate-spin" : ""}`} size={14} />
                  Refresh Results
                </motion.button>
                
                <motion.button
                  onClick={handleReset}
                  className="w-full py-3"
                  variants={buttonVariants}
                  whileHover="hover"
                  whileTap="tap"
                  style={{
                    background: 'transparent',
                    color: '#0A0A0A',
                    fontFamily: 'Montserrat, sans-serif',
                    fontSize: '14px',
                    fontWeight: 500,
                    letterSpacing: '0.05em',
                    border: '1px solid rgba(212, 175, 55, 0.3)',
                    textTransform: 'uppercase'
                  }}
                >
                  Reset Filters
                </motion.button>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default ProductFilters; 
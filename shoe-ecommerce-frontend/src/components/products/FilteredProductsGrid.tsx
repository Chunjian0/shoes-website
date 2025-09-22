import React, { useState, useEffect, useRef } from 'react';
import { useMediaQuery } from 'react-responsive';
import { motion, AnimatePresence } from 'framer-motion';
import { FiFilter, FiGrid, FiList, FiRefreshCw } from 'react-icons/fi';
import MinimalProductCard from './MinimalProductCard';
import EnhancedProductCard from './EnhancedProductCard';
import ProductFilters from '../filters/ProductFilters';
import MobileFilterDrawer from '../filters/MobileFilterDrawer';
import ActiveFilters from '../filters/ActiveFilters';

// 产品项接口
interface ProductItem {
  id: number;
  title: string;
  description?: string;
  price: number;
  discount_percentage?: number;
  category?: {
    id: number;
    name: string;
  };
  images?: any[];
  image_url?: string;
  rating?: number;
  reviews_count?: number;
  is_new_arrival?: boolean;
  is_sale?: boolean;
  created_at?: string;
  updated_at?: string;
  linked_products?: any[];
  related_products?: any[];
  views?: number;
}

interface Category {
  id: number;
  name: string;
}

interface FilteredProductsGridProps {
  products: ProductItem[];
  categories: Category[];
  isLoading?: boolean;
  error?: string | null;
  onFilterChange?: (filters: any) => void;
  initialFilters?: {
    category: string;
    minPrice: string;
    maxPrice: string;
    sort: string;
  };
}

const FilteredProductsGrid: React.FC<FilteredProductsGridProps> = ({
  products,
  categories,
  isLoading = false,
  error = null,
  onFilterChange,
  initialFilters = {
    category: '',
    minPrice: '',
    maxPrice: '',
    sort: 'newest'
  }
}) => {
  // 响应式检测是否为移动设备
  const isMobile = useMediaQuery({ maxWidth: 768 });
  
  // 状态管理
  const [filters, setFilters] = useState(initialFilters);
  const [filteredProducts, setFilteredProducts] = useState<ProductItem[]>([]);
  const [isMobileFilterOpen, setIsMobileFilterOpen] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  
  // Refs
  const gridRef = useRef<HTMLDivElement>(null);
  
  // 计算活跃的筛选条件
  const getActiveFilters = () => {
    return Object.entries(filters)
      .filter(([key, value]) => value !== '' && value !== 'newest')
      .map(([key, value]) => ({
        type: key,
        label: key,
        value: value
      }));
  };
  
  // 筛选产品
  useEffect(() => {
    if (products.length === 0) {
      setFilteredProducts([]);
      return;
    }
    
    let result = [...products];
    
    // 应用分类筛选
    if (filters.category) {
      const categoryIdToFilter = Number(filters.category);
      // 确保 categoryIdToFilter 是有效数字再进行筛选
      if (!isNaN(categoryIdToFilter) && categoryIdToFilter > 0) { 
        result = result.filter(product => 
          product.category && product.category.id === categoryIdToFilter
        );
      }
    }
    
    // 应用价格筛选
    if (filters.minPrice) {
      const minPrice = Number(filters.minPrice);
      result = result.filter(product => {
        const productPrice = product.price || 0;
        return productPrice >= minPrice;
      });
    }
    
    if (filters.maxPrice) {
      const maxPrice = Number(filters.maxPrice);
      result = result.filter(product => {
        const productPrice = product.price || 0;
        return productPrice <= maxPrice;
      });
    }
    
    // 应用排序
    switch (filters.sort) {
      case 'price_asc':
        result.sort((a, b) => (a.price || 0) - (b.price || 0));
        break;
      case 'price_desc':
        result.sort((a, b) => (b.price || 0) - (a.price || 0));
        break;
      case 'popularity':
        result.sort((a, b) => (b.views || 0) - (a.views || 0));
        break;
      case 'newest':
      default:
        result.sort((a, b) => {
          const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
          const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
          return dateB - dateA;
        });
        break;
    }
    
    setFilteredProducts(result);
  }, [products, filters]);
  
  // 处理筛选器变化
  const handleFilterChange = (name: string, value: string) => {
    const newFilters = { ...filters, [name]: value };
    setFilters(newFilters);
    
    // 如果有外部筛选器更改回调，调用它
    if (onFilterChange) {
      onFilterChange(newFilters);
    }
  };
  
  // 重置所有筛选器
  const handleResetFilters = () => {
    setFilters(initialFilters);
    
    // 如果有外部筛选器更改回调，调用它
    if (onFilterChange) {
      onFilterChange(initialFilters);
    }
  };
  
  // 刷新数据
  const handleRefresh = () => {
    setIsRefreshing(true);
    
    // 模拟刷新行为
    setTimeout(() => {
      setIsRefreshing(false);
    }, 1000);
  };
  
  // 移除单个筛选条件
  const handleRemoveFilter = (type: string) => {
    const newFilters = { 
      ...filters, 
      [type]: type === 'sort' ? 'newest' : '' 
    };
    setFilters(newFilters);
    
    // 如果有外部筛选器更改回调，调用它
    if (onFilterChange) {
      onFilterChange(newFilters);
    }
  };
  
  // 切换移动筛选抽屉
  const toggleMobileFilter = () => {
    setIsMobileFilterOpen(!isMobileFilterOpen);
  };
  
  // 当没有产品数据时的空状态
  const EmptyState = () => (
    <div className="text-center py-16 bg-gray-50 rounded-lg border border-gray-200">
      <svg 
        className="w-16 h-16 text-gray-400 mx-auto mb-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path 
          strokeLinecap="round" 
          strokeLinejoin="round" 
          strokeWidth="1" 
          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
        />
      </svg>
      <h3 className="text-lg font-medium text-gray-900 mb-2">No Products Found</h3>
      <p className="text-gray-600 mb-6">Try adjusting your filters or search criteria.</p>
      <button
        onClick={handleResetFilters}
        className="bg-black text-white px-6 py-2 rounded-sm hover:bg-gray-800 transition-colors"
      >
        Reset Filters
      </button>
    </div>
  );
  
  // 加载状态
  const LoadingState = () => (
    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      {Array(8).fill(0).map((_, index) => (
        <div key={index} className="bg-white rounded-lg shadow-sm overflow-hidden animate-pulse">
          <div className="w-full h-48 bg-gray-200"></div>
          <div className="p-4">
            <div className="h-4 bg-gray-200 rounded mb-2 w-1/2"></div>
            <div className="h-4 bg-gray-200 rounded mb-4 w-3/4"></div>
            <div className="h-4 bg-gray-200 rounded w-1/3"></div>
          </div>
        </div>
      ))}
    </div>
  );
  
  // 错误状态
  const ErrorState = () => (
    <div className="text-center py-16 bg-red-50 rounded-lg border border-red-200">
      <svg 
        className="w-16 h-16 text-red-400 mx-auto mb-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path 
          strokeLinecap="round" 
          strokeLinejoin="round" 
          strokeWidth="1" 
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
      <h3 className="text-lg font-medium text-gray-900 mb-2">Error Loading Products</h3>
      <p className="text-gray-600 mb-6">{error || 'Something went wrong. Please try again.'}</p>
      <button
        onClick={handleRefresh}
        className="bg-black text-white px-6 py-2 rounded-sm hover:bg-gray-800 transition-colors"
      >
        Try Again
      </button>
    </div>
  );
  
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex flex-col md:flex-row gap-8">
        {/* 移动端筛选器按钮 */}
        <div className="md:hidden flex justify-between items-center mb-4">
          <motion.button
            onClick={toggleMobileFilter}
            className="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-sm text-gray-700 hover:bg-gray-200 transition-colors"
            whileHover={{ scale: 1.03 }}
            whileTap={{ scale: 0.97 }}
          >
            <FiFilter size={16} />
            <span>Filters</span>
          </motion.button>
          
          <div className="flex items-center">
            {/* 视图切换 */}
            <div className="flex bg-gray-100 rounded-sm p-1 mr-2">
              <button 
                onClick={() => setViewMode('grid')}
                className={`p-2 rounded-sm transition-colors ${viewMode === 'grid' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-600 hover:text-gray-900'}`}
                aria-label="Grid view"
              >
                <FiGrid size={16} />
              </button>
              <button 
                onClick={() => setViewMode('list')}
                className={`p-2 rounded-sm transition-colors ${viewMode === 'list' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-600 hover:text-gray-900'}`}
                aria-label="List view"
              >
                <FiList size={16} />
              </button>
            </div>
            
            <motion.button
              onClick={handleRefresh}
              className="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-sm text-gray-700 hover:bg-gray-200 transition-colors"
              whileHover={{ scale: 1.03 }}
              whileTap={{ scale: 0.97 }}
            >
              <FiRefreshCw className={isRefreshing ? "animate-spin" : ""} size={16} />
              <span>Refresh</span>
            </motion.button>
          </div>
        </div>
        
        {/* 桌面端筛选器侧边栏 */}
        <div className="hidden md:block md:w-64 flex-shrink-0">
          <ProductFilters
            categories={categories}
            initialFilters={filters}
            onFilterChange={handleFilterChange}
            onReset={handleResetFilters}
            onRefresh={handleRefresh}
            isRefreshing={isRefreshing}
          />
        </div>
        
        {/* 产品网格 */}
        <div className="flex-1">
          {/* 已激活的筛选条件 */}
          <ActiveFilters
            activeFilters={getActiveFilters()}
            onRemoveFilter={handleRemoveFilter}
            onClearAll={handleResetFilters}
            categories={categories}
          />
          
          {/* 产品数量和视图控制 */}
          <div className="flex flex-wrap justify-between items-center mb-6">
            <div className="text-gray-600 mb-2 md:mb-0">
              {!isLoading && (
                <span>Found <b>{filteredProducts.length}</b> products</span>
              )}
            </div>
            
            <div className="hidden md:flex items-center gap-4">
              {/* 桌面端视图切换 */}
              <div className="flex bg-gray-100 rounded-sm p-1">
                <button 
                  onClick={() => setViewMode('grid')}
                  className={`p-2 rounded-sm transition-colors ${viewMode === 'grid' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-600 hover:text-gray-900'}`}
                  aria-label="Grid view"
                >
                  <FiGrid size={16} />
                </button>
                <button 
                  onClick={() => setViewMode('list')}
                  className={`p-2 rounded-sm transition-colors ${viewMode === 'list' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-600 hover:text-gray-900'}`}
                  aria-label="List view"
                >
                  <FiList size={16} />
                </button>
              </div>
              
              {/* 桌面端刷新按钮 */}
              <motion.button
                onClick={handleRefresh}
                className="flex items-center gap-2 px-4 py-2 text-gray-700 bg-gray-100 rounded-sm hover:bg-gray-200 transition-colors"
                whileHover={{ scale: 1.03 }}
                whileTap={{ scale: 0.97 }}
              >
                <FiRefreshCw className={isRefreshing ? "animate-spin" : ""} size={16} />
                <span>Refresh</span>
              </motion.button>
            </div>
          </div>
          
          {/* 产品列表 */}
          <div ref={gridRef}>
            {isLoading ? (
              <LoadingState />
            ) : error ? (
              <ErrorState />
            ) : filteredProducts.length === 0 ? (
              <EmptyState />
            ) : (
              <AnimatePresence>
                {viewMode === 'grid' ? (
                  <motion.div 
                    key="grid-view"
                    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.3 }}
                  >
                    {filteredProducts.map((product, index) => (
                      <MinimalProductCard
                        key={product.id}
                        template={product}
                        index={index}
                      />
                    ))}
                  </motion.div>
                ) : (
                  <motion.div
                    key="list-view"
                    className="flex flex-col space-y-4"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.3 }}
                  >
                    {filteredProducts.map((product, index) => (
                      <EnhancedProductCard
                        key={product.id}
                        template={product}
                        index={index}
                        compact={true}
                      />
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            )}
          </div>
        </div>
      </div>
      
      {/* 移动端筛选抽屉 */}
      <MobileFilterDrawer
        isOpen={isMobileFilterOpen}
        onClose={toggleMobileFilter}
        categories={categories}
        filters={filters}
        onFilterChange={handleFilterChange}
        onReset={handleResetFilters}
        onRefresh={handleRefresh}
        isRefreshing={isRefreshing}
      />
    </div>
  );
};

export default FilteredProductsGrid; 
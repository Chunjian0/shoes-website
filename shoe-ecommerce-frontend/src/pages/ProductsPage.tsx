import { useEffect, useState, useRef, useCallback, useMemo } from 'react';
import { useAppDispatch, useAppSelector } from '../store';
import { fetchTemplates, fetchCategories } from '../store/slices/productSlice';
import { ProductTemplate } from '../types/apiTypes';
import TemplateCard from '../components/templates/TemplateCard';
import LoadingSpinner from '../components/LoadingSpinner';
import MinimalProductCard from '../components/products/MinimalProductCard';
import { motion, AnimatePresence } from 'framer-motion';
import { FiSearch, FiRefreshCw, FiFilter, FiX, FiChevronLeft, FiChevronRight, FiGrid, FiList, FiSliders, FiArrowUp } from 'react-icons/fi';
import { useNavigate, useLocation } from 'react-router-dom';
import PriceRangeSlider from '../components/filters/PriceRangeSlider';
import ProductFilters from '../components/filters/ProductFilters';
import FilterSidebar from '../components/filters/FilterSidebar';
import { useInView } from 'react-intersection-observer';
import useRipple from '../hooks/useRipple';

// 日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[ProductsPage] ${message}`, data ? data : '');
};

// 防抖函数
const useDebounce = <T,>(value: T, delay: number): T => {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

// Helper function to calculate effective price for sorting
const getEffectivePrice = (template: ProductTemplate): number => {
  // 1. Check direct price first
  if (template.price && typeof template.price === 'number') {
    return template.price;
  }

  // 2. Check linked products *only if* direct price is missing (Changed from related_products)
  if (template.linked_products && template.linked_products.length > 0) {
    const validPrices = template.linked_products
      .map((p: { price?: number | string }) => (p.price ? Number(p.price) : Infinity))
      .filter((price: number): price is number => price !== Infinity && !isNaN(price)); // Type guard for filter

    // 3. Return minimum if valid linked prices exist
    if (validPrices.length > 0) {
      return Math.min(...validPrices);
    }
  }

  // 4. Default to Infinity if no valid price found
  return Infinity; 
};

// Re-define button animation variants
const buttonHoverTapVariants = {
  hover: {
    y: -2,
    boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
    transition: { duration: 0.2, ease: 'easeOut' }
  },
  tap: {
    scale: 0.98,
    transition: { duration: 0.1, ease: 'easeOut' }
  }
};

const iconButtonHoverTapVariants = {
  hover: {
    scale: 1.1,
    transition: { duration: 0.2 }
  },
  tap: {
    scale: 0.9,
    transition: { duration: 0.1 }
  }
};

// Define interface for filters state
interface FiltersState {
  category: string | number; // Allow number for potential ID usage
  minPrice: string;
  maxPrice: string;
  sort: string;
  featured?: boolean; // Optional boolean flags
  new?: boolean;
  sale?: boolean;
}

const ProductsPage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useAppDispatch();
  const {
    templates,
    templatesLoading,
    categoriesLoading,
    error,
    totalTemplates,
    currentPage: reduxCurrentPage,
    totalPages,
    perPage,
    categories
  } = useAppSelector(state => state.products);
  
  const { addRipple, RippleContainer } = useRipple();
  
  // Filter states - Update type and initial value
  const [filters, setFilters] = useState<FiltersState>({
    category: '',
    minPrice: '',
    maxPrice: '',
    sort: 'newest',
    featured: false, // Add boolean filters
    new: false,
    sale: false,
  });

  // 内部搜索查询状态
  const [searchInputValue, setSearchInputValue] = useState('');
  const searchQuery = useDebounce(searchInputValue, 800);
  const [isSearching, setIsSearching] = useState(false);

  const [currentPage, setCurrentPage] = useState(1);
  
  const [isMobileFilterOpen, setIsMobileFilterOpen] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [scrollY, setScrollY] = useState(0);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [showScrollTop, setShowScrollTop] = useState(false);
  
  const productsContainerRef = useRef<HTMLDivElement>(null);
  const filterContainerRef = useRef<HTMLDivElement>(null);
  const searchTimeoutRef = useRef<NodeJS.Timeout | null>(null);

  // Animation variants for the grid container and items
  const gridContainerVariants = {
    hidden: { opacity: 0 },
    show: {
      opacity: 1,
      transition: {
        staggerChildren: 0.07, // Stagger delay between cards
      }
    }
  };

  const gridItemVariants = {
    hidden: { opacity: 0, y: 30 }, // Start slightly lower and faded out
    show: { 
      opacity: 1, 
      y: 0,       // Animate to original position
      transition: {
        duration: 0.5, 
        ease: [0.33, 1, 0.68, 1] // Use the same easing as the card
      }
    }
  };

  // Intersection observer for main content fade-in
  const { ref: contentRef, inView: contentInView } = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });
  
  // Intersection observer for pagination fade-in
  const { ref: paginationRef, inView: paginationInView } = useInView({
    triggerOnce: true,
    threshold: 0.2, // Trigger slightly earlier
  });

  // Ensure debug info
  useEffect(() => {
    logDebug('Redux state updated', {
      templatesLength: templates?.length || 0,
      templatesLoading,
      error,
      totalTemplates,
      categories: categories?.length || 0
    });
  }, [templates, templatesLoading, error, totalTemplates, categories]);
  
  // 监听搜索查询变化，自动触发搜索
  useEffect(() => {
    if (searchInputValue !== searchQuery) {
      // 如果防抖后的搜索查询变了，重置到第一页
      setCurrentPage(1);
    }
    
    // 如果是空查询，不要显示搜索loading
    if (searchQuery.trim() === '') {
      setIsSearching(false);
    } else {
      // 标记搜索状态
      setIsSearching(true);
      
      // 设置最多3秒后自动取消搜索状态，避免loading状态卡住
      const timer = setTimeout(() => {
        setIsSearching(false);
      }, 3000);
      
      // 在下一次effect前清理计时器
      return () => clearTimeout(timer);
    }
  }, [searchQuery, searchInputValue]);
  
  // 当接收到新的模板数据时，关闭搜索中状态
  useEffect(() => {
    if (templates && templates.length > 0) {
      setIsSearching(false);
    }
  }, [templates]);

  // 处理搜索输入变化
  const handleSearchInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearchInputValue(e.target.value);
  };

  // 处理搜索提交 - 现在只需重置页码，搜索逻辑由防抖后的searchQuery变化自动触发
  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    
    // 防止频繁搜索
    if (isSearching) return;
    
    // 清除之前的超时
    if (searchTimeoutRef.current) {
      clearTimeout(searchTimeoutRef.current);
    }
    
    setIsSearching(true);
    
    // 设置3秒超时自动取消搜索状态
    searchTimeoutRef.current = setTimeout(() => {
      setIsSearching(false);
    }, 3000);
    
    // 滚动到顶部
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // 优化搜索匹配函数，支持模糊匹配和部分匹配
  const isSearchMatch = (template: any, searchQuery: string): boolean => {
    if (!searchQuery || searchQuery.trim() === '') return true;
    
    const query = searchQuery.toLowerCase().trim();
    
    // 直接匹配常见字段
    const directMatches = [
      template.title,
      template.name,
      template.description,
      template.category?.name,
      // 增加对SKU的搜索支持
      template.sku
    ]
    .filter(Boolean) // 过滤掉undefined或null值
    .map(field => String(field).toLowerCase()) // 确保所有字段都是字符串
    .some(field => field.includes(query));
    
    if (directMatches) return true;
    
    // 尝试匹配关联产品
    if (template.linked_products && Array.isArray(template.linked_products)) {
      const linkedProductMatch = template.linked_products.some((product: any) => {
        return (
          (product.name && product.name.toLowerCase().includes(query)) || 
          (product.sku && product.sku.toLowerCase().includes(query))
        );
      });
      
      if (linkedProductMatch) return true;
    }
    
    // 尝试匹配参数
    if (template.parameters && Array.isArray(template.parameters)) {
      const parameterMatch = template.parameters.some((param: any) => {
        return (
          (param.name && param.name.toLowerCase().includes(query)) ||
          (param.values && Array.isArray(param.values) && 
           param.values.some((value: any) => String(value).toLowerCase().includes(query)))
        );
      });
      
      if (parameterMatch) return true;
    }
    
    return false;
  };

  // 使用useMemo缓存过滤结果
  const filteredTemplates = useMemo(() => {
    if (!templates || templates.length === 0) {
      return [];
    }
    
    logDebug('应用前端过滤 (Search/Sort Only)', { 
      templates: templates.length, 
      searchQuery, 
      filters 
    });
    
    let result = [...templates];
    
    // Apply search filter (Keep frontend search for now)
    if (searchQuery.trim() !== '') {
      const query = searchQuery.toLowerCase();
      result = result.filter(template => isSearchMatch(template, query));
      
      logDebug('搜索过滤后的结果', { 
        searchQuery: query, 
        resultsCount: result.length,
        firstResult: result.length > 0 ? result[0].name || result[0].title : 'none'
      });
      
      if (result.length === 0) {
        logDebug('搜索结果为空');
      }
    }
    
    // Apply category filter 
    if (filters.category) {
        // Convert category filter (can be string or number) to string/number for comparison
        const categoryFilterValue = typeof filters.category === 'string' ? filters.category.toLowerCase() : filters.category;
      result = result.filter(template => 
        template.category && 
            (
                // Compare IDs if filter is a number
                (typeof categoryFilterValue === 'number' && template.category.id === categoryFilterValue) ||
                // Compare names (case-insensitive) if filter is a string
                (typeof categoryFilterValue === 'string' && template.category.name.toLowerCase() === categoryFilterValue)
                // Potentially add slug comparison if needed: || (typeof categoryFilterValue === 'string' && template.category.slug?.toLowerCase() === categoryFilterValue)
            )
      );
    }
    
    // Apply sorting (Keep frontend sorting)
    switch (filters.sort) {
      case 'price_asc':
        result.sort((a: ProductTemplate, b: ProductTemplate) => { 
          const priceA = getEffectivePrice(a);
          const priceB = getEffectivePrice(b);
          // Handle Infinity cases: items without price go last when ascending
          if (priceA === Infinity && priceB === Infinity) return 0;
          if (priceA === Infinity) return 1; 
          if (priceB === Infinity) return -1;
          return priceA - priceB;
        });
        break;
      case 'price_desc':
        result.sort((a: ProductTemplate, b: ProductTemplate) => { 
          const priceA = getEffectivePrice(a);
          const priceB = getEffectivePrice(b);
           // Handle Infinity cases: items without price go last when descending
           // We use Infinity here as well, but reverse the sort order for comparison
          if (priceA === Infinity && priceB === Infinity) return 0;
          if (priceA === Infinity) return 1; // Item A (no price) goes after B
          if (priceB === Infinity) return -1; // Item B (no price) goes after A
          return priceB - priceA; // Standard descending sort
        });
        break;
      case 'name_asc': // Updated to use lowercase compare
        result.sort((a: ProductTemplate, b: ProductTemplate) => { 
          const nameA = (a.name || a.title || '').toLowerCase();
          const nameB = (b.name || b.title || '').toLowerCase();
          return nameA.localeCompare(nameB);
        });
        break;
      case 'name_desc': // Updated to use lowercase compare
        result.sort((a: ProductTemplate, b: ProductTemplate) => { 
          const nameA = (a.name || a.title || '').toLowerCase();
          const nameB = (b.name || b.title || '').toLowerCase();
          return nameB.localeCompare(nameA);
        });
        break;
      case 'popularity': // Added popularity sort
        result.sort((a: ProductTemplate, b: ProductTemplate) => (b.views || 0) - (a.views || 0));
        break;
      case 'newest':
      default:
        result.sort((a: ProductTemplate, b: ProductTemplate) => { 
          const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
          const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
          return dateB - dateA;
        });
        break;
    }
    
    return result;
  }, [templates, searchQuery, filters]);
  
  // 使用useMemo优化渲染状态计算
  const renderState = useMemo(() => {
    const hasFilteredResults = filteredTemplates && filteredTemplates.length > 0;
    const hasTemplates = templates && templates.length > 0;
    
    return {
      hasFilteredResults,
      filteredCount: filteredTemplates?.length || 0,
      hasTemplates,
      templatesCount: templates?.length || 0
    };
  }, [filteredTemplates, templates]);

  // 监听滚动位置，用于滚动动画和显示返回顶部按钮
  useEffect(() => {
    const handleScroll = () => {
      setScrollY(window.scrollY);
      setShowScrollTop(window.scrollY > 300);
    };
    
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  
  // 监听窗口大小，用于移动端响应式布局
  useEffect(() => {
    const handleResize = () => {
      // 当屏幕变宽时自动关闭移动端筛选器
      if (window.innerWidth >= 768 && isMobileFilterOpen) {
        setIsMobileFilterOpen(false);
      }
    };
    
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, [isMobileFilterOpen]);

  // 在页面加载时获取数据 (Removed Redis logic)
  useEffect(() => {
    let isRequestPending = false;
    let requestTimer: NodeJS.Timeout | null = null;
    
    const loadPageData = () => {
      if (isRequestPending) {
        logDebug('请求已在进行中，跳过重复请求');
        return;
      }
      
      if (requestTimer) {
        clearTimeout(requestTimer);
      }
      
      logDebug('计划加载产品模板页 - 依赖项:', { filters, searchQuery, currentPage });
      
      requestTimer = setTimeout(async () => {
        isRequestPending = true;
        logDebug('执行加载产品模板页', { filters, searchQuery, currentPage });
        
        try {
          // --- Directly Dispatch Fetch Templates --- 
          const params: any = {
            page: currentPage,
            per_page: 12,
          };
          if (filters.category) params.category = String(filters.category); // Ensure string for API
          if (filters.minPrice) params.min_price = filters.minPrice;
          if (filters.maxPrice) params.max_price = filters.maxPrice;
          if (filters.sort) params.sort = filters.sort;
          if (searchQuery.trim() !== '') params.search = searchQuery;
          if (filters.featured) params.featured = 'true';
          if (filters.new) params.new = 'true';
          if (filters.sale) params.sale = 'true';
          
          logDebug('Dispatching fetchTemplates with params:', params);
          await dispatch(fetchTemplates(params));
          // --- End Direct Dispatch --- 

          // --- Fetch Categories if needed --- 
        if (!categories || categories.length === 0) {
          logDebug('请求分类数据');
            await dispatch(fetchCategories());
          }
          // --- End Fetch Categories --- 
          
      } catch (error) {
        logDebug('加载产品模板页面时出错', error);
        console.error('加载产品模板页面时出错:', error);
        } finally {
          isRequestPending = false;
      }
      }, 300); 
    };
    
    loadPageData();
    
    return () => {
      if (requestTimer) {
        clearTimeout(requestTimer);
      }
    };
    // Removed useCache from dependencies
  }, [dispatch, filters, searchQuery, currentPage, categories]); 

  // 处理筛选变更
  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    logDebug('处理筛选变更 (legacy handler)', { name, value }); // Added log
    setFilters(prev => ({ ...prev, [name]: value }));
    setCurrentPage(1); // 重置到第一页
    
    // 滚动到页面顶部，平滑效果
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // 处理分页
  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    
    // 平滑滚动到产品列表顶部
    if (productsContainerRef.current) {
      const headerOffset = 80; // 考虑固定导航栏高度
      const elementPosition = productsContainerRef.current.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
      
      window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth'
      });
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  // 重新加载数据 (Simplified)
  const handleRefreshData = () => {
    logDebug('手动刷新数据 (直接重新获取)');
    setIsRefreshing(true);

    // Directly dispatch fetchTemplates again
    const params: any = {
      page: currentPage,
      per_page: 12,
    };
    if (filters.category) params.category = String(filters.category);
    if (filters.minPrice) params.min_price = filters.minPrice;
    if (filters.maxPrice) params.max_price = filters.maxPrice;
    if (filters.sort) params.sort = filters.sort;
    if (searchQuery.trim() !== '') params.search = searchQuery;
    if (filters.featured) params.featured = 'true';
    if (filters.new) params.new = 'true';
    if (filters.sale) params.sale = 'true';

    dispatch(fetchTemplates(params))
      .finally(() => {
         // Delay ending refresh state for visual feedback
      setTimeout(() => {
        setIsRefreshing(false);
      }, 600);
      });
  };
  
  // 切换移动端筛选器显示
  const toggleMobileFilter = () => {
    setIsMobileFilterOpen(prev => !prev);
  };

  // 处理价格范围变更
  const handlePriceChange = (min: number | string, max: number | string) => {
    logDebug('价格范围变更', { min, max });
    
    // 确保价格值是有效的
    const validMin = min !== undefined && min !== '' && !isNaN(Number(min));
    const validMax = max !== undefined && max !== '' && !isNaN(Number(max));
    
    setFilters(prev => ({ 
      ...prev, 
      minPrice: validMin ? min.toString() : '', 
      maxPrice: validMax ? max.toString() : ''
    }));
    
    setCurrentPage(1); // 重置到第一页
  };

  // Generate pagination buttons
  const renderPagination = useCallback(() => {
    if (!totalPages || totalPages <= 1) return null;

    const pages = [];
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    // Add first page button
    if (startPage > 1) {
      pages.push(
        <motion.button
          key="first"
          onClick={() => handlePageChange(1)}
          className="w-8 h-8 rounded-md border border-transparent hover:border-indigo-200 hover:text-indigo-600 flex items-center justify-center text-sm text-gray-600 transition-all"
          whileHover={{ scale: 1.05 }}
          whileTap={{ scale: 0.95 }}
        >
          1
        </motion.button>
      );
      if (startPage > 2) {
        pages.push(<span key="ellipsis1" className="px-1 text-gray-400">...</span>);
      }
    }

    // Add page number buttons
    for (let i = startPage; i <= endPage; i++) {
      pages.push(
        <motion.button
          key={i}
          onClick={() => handlePageChange(i)}
          className={`w-8 h-8 rounded-md flex items-center justify-center text-sm transition-all ${ 
            i === currentPage
              ? 'bg-indigo-600 text-white font-medium shadow-sm'
              : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700'
          }`}
          whileHover={{ scale: i !== currentPage ? 1.05 : 1 }}
          whileTap={{ scale: 0.95 }}
        >
          {i}
        </motion.button>
      );
    }

    // Add last page button
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        pages.push(<span key="ellipsis2" className="px-1 text-gray-400">...</span>);
      }
      pages.push(
        <motion.button
          key="last"
          onClick={() => handlePageChange(totalPages)}
          className="w-8 h-8 rounded-md border border-transparent hover:border-indigo-200 hover:text-indigo-600 flex items-center justify-center text-sm text-gray-600 transition-all"
          whileHover={{ scale: 1.05 }}
          whileTap={{ scale: 0.95 }}
        >
          {totalPages}
        </motion.button>
      );
    }

    return (
      // Wrap in motion.div for inView animation
      <motion.div 
        ref={paginationRef}
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: paginationInView ? 1 : 0, y: paginationInView ? 0 : 10 }}
        transition={{ duration: 0.4, delay: 0.1 }}
        className="flex justify-center items-center space-x-1 mt-10 pb-8"
      >
        <motion.button
          onClick={() => handlePageChange(Math.max(1, currentPage - 1))}
          disabled={currentPage === 1}
          className={`h-8 px-3 rounded-md flex items-center justify-center text-sm ${ 
            currentPage === 1 
              ? 'opacity-50 cursor-not-allowed text-gray-400' 
              : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700'
          } transition-all`}
          whileHover={currentPage !== 1 ? { scale: 1.05 } : {}}
          whileTap={currentPage !== 1 ? { scale: 0.95 } : {}}
        >
          <FiChevronLeft size={16} />
        </motion.button>
        
        <div className="hidden sm:flex space-x-1">
          {pages}
        </div>
        
        <div className="sm:hidden text-sm text-gray-700">
          {currentPage} / {totalPages}
        </div>
        
        <motion.button
          onClick={() => handlePageChange(Math.min(totalPages, currentPage + 1))}
          disabled={currentPage === totalPages}
          className={`h-8 px-3 rounded-md flex items-center justify-center text-sm ${ 
            currentPage === totalPages 
              ? 'opacity-50 cursor-not-allowed text-gray-400' 
              : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700'
          } transition-all`}
          whileHover={currentPage !== totalPages ? { scale: 1.05 } : {}}
          whileTap={currentPage !== totalPages ? { scale: 0.95 } : {}}
        >
          <FiChevronRight size={16} />
        </motion.button>
      </motion.div>
    );
  }, [currentPage, totalPages, handlePageChange, paginationRef, paginationInView]); // Add observer refs/state to deps

  // optimize renderProductGrid function (Added loading checks)
  const renderProductGrid = () => {
    logDebug('渲染产品网格', { templatesLoading, error, totalTemplates, templatesCount: templates?.length, filteredCount: filteredTemplates?.length });

    // 1. Still loading templates from API? Show spinner.
    if (templatesLoading) {
      logDebug('渲染产品网格 - 状态: Loading');
      return (
        <div className="flex flex-col items-center justify-center h-64">
          <LoadingSpinner size="large" />
          <p className="mt-4 text-gray-500 text-sm">Loading products...</p>
        </div>
      );
    }

    // 2. Error occurred during template fetch? Show error.
    if (error) {
      logDebug('渲染产品网格 - 状态: Error', error);
      const errorMessage = typeof error === 'string' ? error : 'An unknown error occurred';
      return (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
          <p className="text-red-600">Error loading products: {String(errorMessage).includes('429') ? 'Too many requests, please try again later.' : errorMessage}</p>
          <button
            onClick={handleRefreshData}
            className="mt-2 px-4 py-1 text-sm bg-red-100 text-red-700 rounded-md hover:bg-red-200"
          >
            Retry
          </button>
        </div>
      );
    }

    // 3. Loading finished, no errors. Now check totals and filters.

    // Case A: No products available AT ALL (API confirmed total is 0)
    // This check should happen only after loading is complete and there's no error.
    if (totalTemplates === 0) {
      logDebug('渲染产品网格 - 状态: No Products Available (totalTemplates is 0)');
      return (
        <div className="bg-white border border-gray-200 rounded-lg p-8 text-center">
           <p className="text-gray-600 mb-4">No products available at the moment.</p>
           <motion.button
            onClick={(e) => { addRipple(e); handleRefreshData(); }}
            className="relative overflow-hidden px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all text-sm shadow-sm"
            variants={buttonHoverTapVariants}
            whileHover="hover"
            whileTap="tap"
          >
            Refresh
            <RippleContainer />
          </motion.button>
        </div>
      );
    }

    // Case B: Products exist overall (totalTemplates > 0), but filters yield no results
    // This check happens only if totalTemplates > 0
    if (renderState.filteredCount === 0) { 
        logDebug('渲染产品网格 - 状态: No Matching Filters (totalTemplates > 0)');
            return (
                <div className="bg-white border border-gray-200 rounded-lg p-8 text-center">
                  <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 mb-4">
                    <svg className="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </div>
                 <h3 className="text-lg font-medium text-gray-900 mb-2">No products match your criteria</h3>
                 <p className="text-gray-500 mb-4">
                   Try adjusting your search or filter criteria.
                    {(typeof searchQuery === 'string' && searchQuery.trim() !== '') && (
                       <span className="block mt-2">Current search: "{searchQuery}"</span>
                     )}
                 </p>
                 <motion.button
                   onClick={(e) => {
                     addRipple(e);
                     setFilters({
                       category: '',
                       minPrice: '',
                       maxPrice: '',
                   sort: 'newest',
                   featured: false, // Ensure reset includes boolean
                   new: false,
                   sale: false
                     });
                     setSearchInputValue('');
                     setCurrentPage(1);
                   }}
                   className="relative overflow-hidden px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all text-sm shadow-sm"
                   variants={buttonHoverTapVariants}
                   whileHover="hover"
                   whileTap="tap"
                 >
                   Clear filters
                   <RippleContainer />
                 </motion.button>
               </div>
            );
    }

    // Case C: Render the product grid (totalTemplates > 0 and filteredCount > 0)
    logDebug(`渲染产品网格 - 状态: Rendering Grid with ${renderState.filteredCount} items`);
    return (
      <AnimatePresence mode='wait'>
        <motion.div
          className={`grid gap-6 ${
            viewMode === 'grid'
              ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'
              : 'grid-cols-1'
          }`}
          // Use a key that changes when filters/page change to trigger AnimatePresence
          key={`product-grid-${currentPage}-${JSON.stringify(filters)}-${searchQuery}`}
          variants={gridContainerVariants}
          initial="hidden"
          animate="show"
          exit={{ opacity: 0, transition: { duration: 0.2 } }} // Define exit animation
        >
          {filteredTemplates.map((template, index) => (
            <motion.div key={template.id} variants={gridItemVariants}>
              <MinimalProductCard
                template={template}
                index={index}
                className="h-full"
              />
            </motion.div>
          ))}
        </motion.div>
      </AnimatePresence>
    );
  };

  // 在组件首次加载或URL参数变化时从 URL 同步筛选条件
  useEffect(() => {
    logDebug('URL参数变化，从URL同步筛选条件', location.search);
    const searchParams = new URLSearchParams(location.search);
    
    // Read filters from URL
    const categoryFromUrl = searchParams.get('category');
    const minPriceFromUrl = searchParams.get('price_min');
    const maxPriceFromUrl = searchParams.get('price_max');
    const sortFromUrl = searchParams.get('sort');
    const searchFromUrl = searchParams.get('search');
    const featuredFromUrl = searchParams.get('featured') === 'true';
    const newFromUrl = searchParams.get('new') === 'true';
    const saleFromUrl = searchParams.get('sale') === 'true';

    let needsUpdate = false;
    const initialFilters: FiltersState = {
      category: categoryFromUrl || '', // Keep as string initially
      minPrice: minPriceFromUrl || '',
      maxPrice: maxPriceFromUrl || '',
      sort: sortFromUrl || 'newest',
      featured: featuredFromUrl,
      new: newFromUrl,
      sale: saleFromUrl,
    };

    // Update filters state if different from URL
    setFilters(prevFilters => {
      if (
        String(prevFilters.category) !== initialFilters.category || // Compare as strings for simplicity here
        prevFilters.minPrice !== initialFilters.minPrice ||
        prevFilters.maxPrice !== initialFilters.maxPrice ||
        prevFilters.sort !== initialFilters.sort ||
        prevFilters.featured !== initialFilters.featured ||
        prevFilters.new !== initialFilters.new ||
        prevFilters.sale !== initialFilters.sale
      ) {
        logDebug('从URL更新filters状态', initialFilters);
        needsUpdate = true;
        // Attempt conversion for category if it looks like a number
        const categoryValue = initialFilters.category && !isNaN(Number(initialFilters.category))
                              ? Number(initialFilters.category)
                              : initialFilters.category;
        return { ...initialFilters, category: categoryValue };
      }
      return prevFilters; // Keep existing state if no change
    });

    // Update search input value if different from URL
    if (searchFromUrl !== null && searchInputValue !== searchFromUrl) {
      logDebug('从URL更新搜索输入值', searchFromUrl);
      setSearchInputValue(searchFromUrl);
      needsUpdate = true; 
    }

    // Reset to page 1 if filters or search were updated from URL
    // But only if the current page is not already 1, to avoid loops on initial load
    if (needsUpdate && currentPage !== 1) {
      logDebug('检测到URL筛选条件或搜索词更新，且当前页不是1，重置为1');
      setCurrentPage(1);
    }

  }, [location.search, currentPage, searchInputValue]); // Add dependencies

  return (
    // Wrap the main container for entry animation
    <motion.div 
      className="min-h-screen bg-gray-100"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.4, ease: "easeOut" }}
    >
      {/* 页面标题区域 - Enhanced Style */}
      <div className="py-20 bg-gradient-to-br from-indigo-50 via-white to-indigo-50 border-b border-gray-200 relative overflow-hidden">
        {/* Subtle background shapes (optional) */}
        <div className="absolute top-0 left-0 w-64 h-64 bg-indigo-100 rounded-full opacity-30 -translate-x-16 -translate-y-16 pointer-events-none"></div>
        <div className="absolute bottom-0 right-0 w-48 h-48 bg-purple-100 rounded-lg opacity-20 translate-x-10 translate-y-10 rotate-12 pointer-events-none"></div>

        <div className="container mx-auto px-4 relative z-10">
          <motion.h1
            className="text-4xl font-bold text-gray-900 mb-4 tracking-tight"
            initial={{ opacity: 0, y: -15 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, ease: "easeOut" }}
          >
            Explore Our Collection
          </motion.h1>
          <motion.p
            className="text-lg text-gray-600 max-w-2xl leading-relaxed"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.5, delay: 0.1, ease: "easeOut" }}
          >
            Discover our latest premium footwear. Use the filters to find the perfect style, size, and price for your next pair.
          </motion.p>
        </div>
      </div>

      {/* 主要内容区域 - Apply ref and animation */}
      <motion.div 
        ref={contentRef} 
        className="container mx-auto px-4 py-8"
        initial={{ opacity: 0, y: 20 }} // Start faded out and slightly lower
        animate={{ opacity: contentInView ? 1 : 0, y: contentInView ? 0 : 20 }} // Animate based on inView state
        transition={{ duration: 0.6, ease: "easeOut" }}
      >
        {/* Apply md breakpoint for flex-row and spacing - Increase spacing */}
        <div className="flex flex-col md:flex-row md:space-x-12">
          {/* Sidebar Column: Adjust width for md */}
          <div className="md:w-1/3 lg:w-1/4 xl:w-1/5 mb-8 md:mb-0">
             {/* Desktop Filter Sidebar: Hide below md */}
            <div className="max-md:hidden">
              <FilterSidebar 
                externalFilters={{
                  category: String(filters.category),
                  minPrice: filters.minPrice,
                  maxPrice: filters.maxPrice,
                  sort: filters.sort,
                  searchQuery: searchQuery,
                  featured: filters.featured,
                  new: filters.new,
                  sale: filters.sale
                }}
                onCategoryChange={(categoryId) => {
                  setFilters(prev => ({ ...prev, category: categoryId }));
                  setCurrentPage(1);
                }}
                onPriceChange={(min, max) => {
                  setFilters(prev => ({ ...prev, minPrice: String(min), maxPrice: String(max) }));
                  setCurrentPage(1);
                }}
                onSortChange={(sortValue) => {
                  logDebug('Sort filter changed (desktop)', { sortValue });
                  setFilters(prev => ({ ...prev, sort: sortValue }));
                  setCurrentPage(1);
                }}
                onSearchChange={(query) => {
                  setSearchInputValue(query);
                  // No need to reset page here, effect hook handles it
                }}
                onReset={() => {
                  setFilters({
                    category: '',
                    minPrice: '',
                    maxPrice: '',
                    sort: 'newest',
                    // Reset boolean filters
                    featured: false,
                    new: false,
                    sale: false
                  });
                  setSearchInputValue('');
                  setCurrentPage(1);
                }}
                searchInputValue={searchInputValue}
                isSearching={isSearching}
              />
            </div>
            {/* Mobile Filter Button: Show below md */}
            <div className="md:hidden mb-4">
              <button 
                onClick={toggleMobileFilter}
                className="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                <FiSliders size={18} className="mr-2"/>
                Filters
              </button>
            </div>
          </div>

          {/* Product Grid and Pagination Column */}
          <div className="flex-1">
            {/* Toolbar (Sorting, View Mode) - Add later if needed */}
            <div className="mb-6"> 
              {/* Placeholder for sorting/view controls */} 
            </div>
            
            {/* Product Grid */} 
            <div ref={productsContainerRef} className="min-h-[400px]">
              {renderProductGrid()}
            </div>
            
            {/* Pagination */} 
            <div className="mt-8">
              {renderPagination()}
            </div>
          </div>
        </div>
      </motion.div>

      {/* Mobile Filter Off-canvas */}
      <AnimatePresence>
        {isMobileFilterOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={toggleMobileFilter}
            className="fixed inset-0 bg-black bg-opacity-50 z-40 flex justify-end"
          >
            <motion.div
              initial={{ x: '100%' }}
              animate={{ x: 0 }}
              exit={{ x: '100%' }}
              transition={{ type: 'tween', ease: 'easeInOut', duration: 0.3 }}
              className="w-full max-w-sm bg-white h-full shadow-xl overflow-y-auto"
              onClick={e => e.stopPropagation()}
            >
              {/* Close button inside sidebar */} 
              <div className="p-4 border-b flex justify-between items-center">
                <h2 className="text-lg font-semibold">Filters</h2>
                <button onClick={toggleMobileFilter} className="p-1 rounded text-gray-500 hover:text-gray-800">
                  <FiX size={20} />
                </button>
              </div>
              <FilterSidebar 
                className="shadow-none rounded-none p-4"
                externalFilters={{
                  category: String(filters.category),
                  minPrice: filters.minPrice,
                  maxPrice: filters.maxPrice,
                  sort: filters.sort,
                  searchQuery: searchQuery,
                  featured: filters.featured,
                  new: filters.new,
                  sale: filters.sale
                }}
                onCategoryChange={(categoryId) => {
                  logDebug('Category filter changed', { categoryId });
                  setFilters(prev => {
                    const newFilters = { ...prev, category: categoryId };
                    logDebug('Setting filters state (category)', newFilters);
                    return newFilters;
                  });
                  setCurrentPage(1);
                  toggleMobileFilter(); // Close after selection
                }}
                onPriceChange={(min, max) => {
                  logDebug('Price filter changed (handler input)', { min, max });
                  const validMin = min !== undefined && min !== '' && !isNaN(Number(min));
                  const validMax = max !== undefined && max !== '' && !isNaN(Number(max));
                  setFilters(prev => {
                    const newFilters = { ...prev, minPrice: validMin ? String(min) : '', maxPrice: validMax ? String(max) : '' };
                    logDebug('Setting filters state (price)', newFilters);
                    return newFilters;
                  });
                  setCurrentPage(1);
                  toggleMobileFilter(); // Close after selection
                }}
                onSortChange={(sortValue) => {
                  logDebug('Sort filter changed', { sortValue });
                  setFilters(prev => {
                    const newFilters = { ...prev, sort: sortValue };
                    logDebug('Setting filters state (sort)', newFilters);
                    return newFilters;
                  });
                  setCurrentPage(1);
                  toggleMobileFilter(); // Close after selection
                }}
                onSearchChange={(query) => {
                  setSearchInputValue(query);
                  // Close only if search is submitted? Or keep open?
                }}
                onReset={() => {
                  logDebug('Reset filters triggered');
                  setFilters({
                    category: '',
                    minPrice: '',
                    maxPrice: '',
                    sort: 'newest',
                    // Reset boolean filters
                    featured: false,
                    new: false,
                    sale: false
                  });
                  setSearchInputValue('');
                  setCurrentPage(1);
                  toggleMobileFilter(); // Close after reset
                }}
                searchInputValue={searchInputValue}
                isSearching={isSearching}
              />
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Scroll to Top Button */}
      <AnimatePresence>
        {showScrollTop && (
          <motion.button
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 20 }}
            onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
            className="fixed bottom-6 right-6 p-3 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition-colors z-30"
            aria-label="Scroll to top"
          >
            <FiArrowUp size={20} />
          </motion.button>
        )}
      </AnimatePresence>

    </motion.div>
  );
};

export default ProductsPage;

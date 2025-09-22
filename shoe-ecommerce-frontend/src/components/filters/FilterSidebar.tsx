import React, { useEffect, useState, useCallback, useMemo } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { apiService } from '../../services/apiService';
import { FiFilter, FiX, FiChevronDown, FiSearch, FiRefreshCw } from 'react-icons/fi';
import { AnimatePresence, motion } from 'framer-motion';
import { Link, useSearchParams } from 'react-router-dom';
import { Listbox, Transition } from '@headlessui/react';
import { Fragment } from 'react';
import PriceRangeSlider from './PriceRangeSlider';
import { useAppDispatch, useAppSelector } from '../../store';
import { fetchCategories } from '../../store/slices/productSlice';
import { toast } from 'react-toastify';
import homepageService from '../../services/homepageService';
import { ProductTemplate } from '../../types/apiTypes';
import LoadingSpinner from '../LoadingSpinner';
import useRipple from '../../hooks/useRipple';
import { 
  ChevronDownIcon, 
  ChevronUpIcon,
  FunnelIcon,
  ArrowPathIcon,
  XMarkIcon,
  MagnifyingGlassIcon
} from '@heroicons/react/24/outline';

// 添加调试日志函数
const debug = (message: string, data?: any) => {
  console.log(`[FilterSidebar Debug] ${message}`, data || '');
};

interface Category {
  id: number;
  name: string;
  slug?: string;
  count?: number;
}

interface ExternalFilters {
    category: string;
    minPrice: string;
    maxPrice: string;
    sort: string;
    searchQuery?: string;
    featured?: boolean;
    new?: boolean;
    sale?: boolean;
}

interface FilterSidebarProps {
  externalFilters?: ExternalFilters;
  onCategoryChange?: (categoryId: string) => void;
  onPriceChange?: (min: string | number, max: string | number) => void;
  onSortChange?: (sort: string) => void;
  onSearchChange?: (query: string) => void;
  onReset?: () => void;
  searchInputValue?: string;
  isSearching?: boolean;
  className?: string;
}

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

// Define sorting options
const sortOptions = [
  { value: 'newest', label: 'Newest' },
  { value: 'price_asc', label: 'Price: Low to High' },
  { value: 'price_desc', label: 'Price: High to Low' },
  { value: 'name_asc', label: 'Name: A-Z' }, 
  { value: 'name_desc', label: 'Name: Z-A' },
  // { value: 'popularity', label: 'Popularity' }, // Keep popularity if needed, otherwise remove
];

const FilterSidebar: React.FC<FilterSidebarProps> = ({
  externalFilters,
  onCategoryChange,
  onPriceChange,
  onSortChange,
  onSearchChange,
  onReset,
  searchInputValue = '',
  isSearching = false,
  className = ''
}) => {
  debug('组件初始化', { externalFilters, onSortChange: !!onSortChange });
  
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
    price: true,
  });
  const [isSortDropdownOpen, setIsSortDropdownOpen] = useState(false);
  const [searchValue, setSearchValue] = useState(searchInputValue);
  const [fetchError, setFetchError] = useState<string | null>(null);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isSearchFocused, setIsSearchFocused] = useState(false);
  
  const [collections, setCollections] = useState<{
    featured: ProductTemplate[],
    newArrival: ProductTemplate[],
    sale: ProductTemplate[]
  }>({
    featured: [],
    newArrival: [],
    sale: []
  });
  const [loadingCollections, setLoadingCollections] = useState(false);
  const [collectionsError, setCollectionsError] = useState<string | null>(null);
  
  const location = useLocation();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const searchParams = new URLSearchParams(location.search);
  
  const reduxCategories = useAppSelector(state => state.products.categories);
  
  const activeCategory = externalFilters?.category || searchParams.get('category') || '';
  const isFeatured = searchParams.get('featured') === 'true';
  const isNew = searchParams.get('new') === 'true';
  const isSale = searchParams.get('sale') === 'true';
  const priceMin = externalFilters?.minPrice || searchParams.get('price_min') || '';
  const priceMax = externalFilters?.maxPrice || searchParams.get('price_max') || '';
  const currentSort = externalFilters?.sort || searchParams.get('sort') || 'newest';
  
  const activeTemplateIds = searchParams.get('template_ids')?.split(',').filter(Boolean) || [];
  
  const { addRipple, RippleContainer } = useRipple();
  
  // 检查排序选项
  debug('Sorting options', { sortOptions, currentSort });
  const currentSortOption = sortOptions.find(option => option.value === currentSort) || sortOptions[0];
  debug('Current sort option', currentSortOption);
  
  useEffect(() => {
    setSearchValue(searchInputValue);
  }, [searchInputValue]);
  
  const fetchCategoriesData = useCallback(async (forceRefresh = false) => {
    debug('获取分类数据开始', { forceRefresh });
    setLoading(true);
    setFetchError(null);
    try {
      if (!forceRefresh && reduxCategories && Array.isArray(reduxCategories) && reduxCategories.length > 0) {
        setCategories(reduxCategories);
        setLoading(false);
        debug('使用Redux缓存的分类数据', { count: reduxCategories.length });
        return;
      }
      
      debug('从API获取分类数据');
      const resultAction = await dispatch(fetchCategories());
      
      if (fetchCategories.fulfilled.match(resultAction)) {
         if (Array.isArray(resultAction.payload)) {
            setCategories(resultAction.payload);
            debug('分类数据获取成功', { count: resultAction.payload.length });
         } else {
            console.warn('Fetched categories payload is not an array:', resultAction.payload);
            throw new Error('Received invalid category data format'); 
         }
      } else if (fetchCategories.rejected.match(resultAction)) {
         console.error('Fetch categories rejected:', resultAction.error);
         throw new Error(resultAction.error.message || 'Failed to fetch categories via Redux');
      } else {
        throw new Error('Unknown error fetching categories');
      }
    } catch (error: any) {
      console.error('Failed to fetch categories:', error);
      setFetchError(`Failed to fetch categories: ${error.message || 'Unknown error'}`);
      setCategories([]);
      debug('分类数据获取失败', { error: error.message });
    } finally {
      setLoading(false);
    }
  }, [dispatch, reduxCategories]);
  
  useEffect(() => {
    fetchCategoriesData();
  }, [fetchCategoriesData]);
  
  useEffect(() => {
    if (reduxCategories && Array.isArray(reduxCategories) && reduxCategories.length > 0 && categories.length === 0) {
      debug('从Redux更新分类', { count: reduxCategories.length });
      setCategories(reduxCategories);
      if(loading) setLoading(false);
    }
  }, [reduxCategories, categories, loading]);
  
  const handleRefreshCategories = async () => {
    setIsRefreshing(true);
    await fetchCategoriesData(true);
    setIsRefreshing(false);
    toast.info('Categories refreshed');
  };
  
  const updateFilters = (newFilters: Partial<ExternalFilters>) => {
    debug('更新筛选条件', newFilters);
    let changed = false;
    const currentExternal = externalFilters ?? { category: '', minPrice: '', maxPrice: '', sort: '', searchQuery: '' };

    try {
    if (newFilters?.category !== undefined && newFilters.category !== currentExternal.category) {
        if (onCategoryChange) {
          debug('使用外部分类变更处理', { category: newFilters.category });
          onCategoryChange(newFilters.category);
        } else {
        if (newFilters.category) searchParams.set('category', newFilters.category); else searchParams.delete('category');
      }
      changed = true;
    }
    
    if ((newFilters?.minPrice !== undefined && newFilters.minPrice !== currentExternal.minPrice) || 
        (newFilters?.maxPrice !== undefined && newFilters.maxPrice !== currentExternal.maxPrice)) {
      const min = newFilters?.minPrice ?? '';
      const max = newFilters?.maxPrice ?? '';
        if (onPriceChange) {
          debug('使用外部价格变更处理', { min, max });
          onPriceChange(min, max);
        } else {
        if (min) searchParams.set('price_min', min); else searchParams.delete('price_min');
        if (max) searchParams.set('price_max', max); else searchParams.delete('price_max');
      }
      changed = true;
    }
    
    if (newFilters?.sort !== undefined && newFilters.sort !== currentExternal.sort) {
        if (onSortChange) {
          debug('使用外部排序变更处理', { sort: newFilters.sort });
          onSortChange(newFilters.sort);
        } else {
        if (newFilters.sort) searchParams.set('sort', newFilters.sort); else searchParams.delete('sort');
      }
      changed = true;
    }
    
    if (changed && !externalFilters) {
    searchParams.delete('page');
        debug('通过URL导航更新筛选', searchParams.toString());
      navigate(`${location.pathname}?${searchParams.toString()}`);
      }
    } catch (error) {
      console.error('Filter update error:', error);
      debug('更新筛选条件失败', { error });
    }
  };

  const handleCategoryClick = (categoryId: number) => {
    debug('分类点击', { categoryId, activeCategory });
    const newCategory = String(categoryId);
    const valueToSet = activeCategory === newCategory ? '' : newCategory;
    updateFilters({ category: valueToSet });
  };

  const handlePriceChangeInternal = (min: string | number, max: string | number) => {
    debug('价格变更', { min, max });
    updateFilters({ minPrice: String(min), maxPrice: String(max) });
  };
  
  const handleSortChange = (selectedOption: { value: string; label: string }) => {
    debug('排序变更', selectedOption);
    updateFilters({ sort: selectedOption.value });
  };

  const clearAllFilters = () => {
    debug('清除所有筛选');
    if (onReset) {
      onReset();
    } else {
      searchParams.delete('category');
      searchParams.delete('price_min');
      searchParams.delete('price_max');
      searchParams.delete('sort');
      searchParams.delete('search');
      searchParams.delete('page');
      navigate(`${location.pathname}?${searchParams.toString()}`);
    }
    if (!externalFilters) {
      setSearchValue('');
    }
  };

  const handleSearchInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const query = e.target.value;
    debug('搜索输入变更', { query });
    setSearchValue(query);
    if (onSearchChange) {
      onSearchChange(query);
    }
  };

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    debug('搜索提交', { searchValue });
    if (!onSearchChange) {
      if (searchValue.trim()) {
        searchParams.set('search', searchValue.trim());
      } else {
        searchParams.delete('search');
      }
      searchParams.delete('page');
      navigate(`${location.pathname}?${searchParams.toString()}`);
    }
  };
  
  const fetchCollections = useCallback(async (forceRefresh = false) => {
    setLoadingCollections(true);
    setCollectionsError(null);
    
    try {
      const homepageData = await homepageService.getHomePageData(forceRefresh);
      
      if (homepageData && homepageData.templates) {
        setCollections({
          featured: homepageData.templates.featured || [],
          newArrival: homepageData.templates.newArrival || [],
          sale: homepageData.templates.sale || []
        });
      } else {
        setCollectionsError('无法获取Collections数据');
        setCollections({
          featured: [],
          newArrival: [],
          sale: []
        });
      }
    } catch (error) {
      console.error('获取Collections失败:', error);
      setCollectionsError('获取Collections失败');
      setCollections({
        featured: [],
        newArrival: [],
        sale: []
      });
    } finally {
      setLoadingCollections(false);
    }
  }, []);
  
  useEffect(() => {
    fetchCollections();
  }, [fetchCollections]);
  
  const handleRefreshCollections = async () => {
    setIsRefreshing(true);
    await fetchCollections(true);
    setIsRefreshing(false);
    toast.success('Collections refreshed');
  };
  
  const hasActiveFilters = () => {
    return !!activeCategory || !!priceMin || !!priceMax || (externalFilters?.searchQuery?.trim() !== '') || (searchValue.trim() !== '');
  };
  
  const toggleSection = (section: string) => {
    debug('折叠切换', { section });
    setExpandedSections(prev => ({ ...prev, [section]: !prev[section] }));
  };
  
  const refreshIconVariants = {
    animate: {
      rotate: 360,
      transition: {
        duration: 1,
        ease: "linear",
        repeat: Infinity
      }
    }
  };

  const renderFilterSection = (title: string, sectionKey: string, content: React.ReactNode, disabled: boolean = false) => {
    const isCollapsible = sectionKey === 'price';
    const isExpanded = isCollapsible ? expandedSections[sectionKey] : true;

    return (
      <div className={`border-b border-gray-200 py-4 ${disabled ? 'opacity-60 pointer-events-none' : ''}`} key={`section-${sectionKey}`}>
        {isCollapsible ? (
          <motion.button
            onClick={(e) => {
              if (!disabled) {
                addRipple(e);
                toggleSection(sectionKey);
              }
            }}
            disabled={disabled}
            className="relative overflow-hidden flex justify-between items-center w-full text-left text-sm font-medium text-gray-700 hover:text-gray-900 disabled:text-gray-400 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-opacity-75 rounded-md px-1 py-0.5"
            whileHover={!disabled ? { backgroundColor: 'rgba(243, 244, 246, 0.6)' } : {}}
            whileTap={!disabled ? { scale: 0.99 } : {}}
            transition={{ duration: 0.15 }}
          >
            {title}
            <FiChevronDown
              className={`transform transition-transform duration-300 text-gray-500 ${isExpanded ? 'rotate-180' : ''}`}
            />
            <RippleContainer />
          </motion.button>
        ) : (
          <h3 className="text-sm font-medium text-gray-900 px-1 mb-3">{title}</h3>
        )}

        {isCollapsible ? (
          <AnimatePresence initial={false}>
            {isExpanded && (
              <motion.div
                initial="collapsed"
                animate="open"
                exit="collapsed"
                variants={{
                  open: { opacity: 1, height: 'auto', marginTop: '1rem' },
                  collapsed: { opacity: 0, height: 0, marginTop: 0 }
                }}
                transition={{ duration: 0.3, ease: [0.04, 0.62, 0.23, 0.98] }}
                className="overflow-hidden pl-3 pr-3 pt-3"
              >
                {content}
              </motion.div>
            )}
          </AnimatePresence>
        ) : (
          <div className="pl-1 pr-1 pt-1">
             {content}
          </div>
        )}
      </div>
    );
  };
  
  // 渲染排序下拉菜单
  const renderSortDropdown = () => {
    debug('渲染排序下拉菜单', { isOpen: isSortDropdownOpen, currentSort });
    
    try {
      return (
        <div className="relative mt-1">
          <motion.button
            onClick={() => !(loading || isRefreshing) && setIsSortDropdownOpen(prev => !prev)}
            disabled={loading || isRefreshing}
            className="relative w-full cursor-default rounded-md bg-white py-2 pl-3 pr-10 text-left border border-gray-300 focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75 focus-visible:ring-offset-2 focus-visible:ring-offset-indigo-300 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed flex justify-between items-center"
            variants={buttonHoverTapVariants}
            whileHover="hover"
            whileTap="tap"
          >
            <span className="block truncate">{currentSortOption.label}</span>
            <motion.span
              className="pointer-events-none flex items-center pr-2"
              animate={{ rotate: isSortDropdownOpen ? 180 : 0 }}
              transition={{ duration: 0.2 }}
            >
              <FiChevronDown
                className="h-5 w-5 text-gray-400"
                aria-hidden="true"
              />
            </motion.span>
            <RippleContainer />
          </motion.button>

          <AnimatePresence>
            {isSortDropdownOpen && (
              <motion.ul
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.95 }}
                transition={{ duration: 0.1, ease: 'easeOut' }}
                className="absolute origin-top z-50 mt-1 w-full max-h-60 overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
              >
                {sortOptions.map((option, index) => {
                  debug(`渲染排序选项 ${index}`, option);
                  return (
                    <motion.li
                      key={option.value}
                      className={`relative cursor-default select-none py-2 pl-10 pr-4 text-gray-900 hover:bg-indigo-100 hover:text-indigo-900 focus:outline-none focus:bg-indigo-100 focus:text-indigo-900`}
                      onClick={() => {
                        debug('排序选项点击', option);
                        handleSortChange(option);
                        setIsSortDropdownOpen(false);
                      }}
                      variants={iconButtonHoverTapVariants}
                      whileHover="hover"
                      role="option"
                      aria-selected={currentSortOption.value === option.value}
                    >
                      <span className={`block truncate ${currentSortOption.value === option.value ? 'font-medium text-indigo-600' : 'font-normal'}`}>
                        {option.label}
                      </span>
                      {currentSortOption.value === option.value ? (
                        <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600">
                          <svg className="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                          </svg>
                        </span>
                      ) : null}
                    </motion.li>
                  );
                })}
              </motion.ul>
            )}
          </AnimatePresence>
        </div>
      );
    } catch (error) {
      console.error('排序下拉菜单渲染错误:', error);
      debug('排序下拉菜单渲染失败', { error });
      return <div className="text-red-500">排序下拉菜单加载失败</div>;
    }
  };
  
  // 渲染分类列表
  const renderCategories = () => {
    debug('渲染分类列表', { count: categories.length, loading, error: fetchError });
    
    try {
      if (loading) {
        return (
          <div className="flex items-center justify-center py-4">
            <LoadingSpinner size="medium" />
          </div>
        );
      }
      
      if (fetchError) {
        return (
          <div className='text-red-600 text-sm py-2'>
            {fetchError}
            <motion.button
              onClick={(e) => { addRipple(e); handleRefreshCategories(); }}
              disabled={isRefreshing}
              className='relative overflow-hidden ml-2 text-xs text-blue-600 hover:underline disabled:opacity-50 disabled:cursor-not-allowed'
              variants={iconButtonHoverTapVariants}
              whileHover="hover"
              whileTap="tap"
            >
              {isRefreshing ? 'Refreshing...' : 'Retry'}
              <RippleContainer />
            </motion.button>
          </div>
        );
      }
      
      if (categories.length === 0) {
        return <p className="text-sm text-gray-500 py-2 pl-2">No categories found.</p>;
      }
      
      return (
        <>
          {categories.map(category => (
            <motion.button
              key={category.id}
              onClick={(e) => { addRipple(e); handleCategoryClick(category.id); }}
              disabled={isRefreshing}
              className={`relative overflow-hidden w-full text-left px-2.5 py-1.5 rounded-md text-sm transition-colors flex justify-between items-center disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500 focus-visible:bg-indigo-50 ${ 
                activeCategory === String(category.id)
                  ? 'bg-indigo-100 text-indigo-700 font-medium'
                  : 'text-gray-600 hover:text-gray-900'
              }`}
              whileHover={!isRefreshing && activeCategory !== String(category.id) ? {
                backgroundColor: '#f3f4f6',
                x: 2,
                transition: { duration: 0.15 } 
              } : {}}
              whileTap={!isRefreshing ? { scale: 0.97, x: 1 } : {}}
            >
              {category.name}
              {activeCategory === String(category.id) && <span className="w-1.5 h-1.5 bg-indigo-600 rounded-full ml-2"></span>}
              <RippleContainer />
            </motion.button>
          ))}
          
          {!loading && !fetchError && (
            <motion.button
              onClick={(e) => { addRipple(e); handleRefreshCategories(); }}
              disabled={isRefreshing}
              className='relative overflow-hidden text-xs text-gray-500 hover:text-black flex items-center mt-3 disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500 rounded px-1 py-0.5'
              variants={iconButtonHoverTapVariants}
              whileHover="hover"
              whileTap="tap"
            >
              <FiRefreshCw className={`mr-1 ${isRefreshing ? 'animate-spin' : ''}`} /> Refresh Categories
              <RippleContainer />
            </motion.button>
          )}
        </>
      );
    } catch (error) {
      console.error('分类列表渲染错误:', error);
      debug('分类列表渲染失败', { error });
      return <div className="text-red-500">分类列表加载失败</div>;
    }
  };
  
  debug('渲染FilterSidebar组件', { 
    hasCategories: categories.length > 0,
    activeSort: currentSort, 
    hasSortChange: !!onSortChange
  });
  
  return (
    <aside className={`bg-white rounded-lg shadow-sm border border-gray-200 p-5 ${className}`}>
      <div className="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
        <h2 className="text-lg font-semibold text-gray-900 flex items-center">
          <FiFilter className="mr-2 text-indigo-600" /> Filters
        </h2>
        {hasActiveFilters() && (
        <motion.button 
            onClick={(e) => { addRipple(e); clearAllFilters(); }}
            className="relative overflow-hidden text-xs font-medium text-gray-600 hover:text-indigo-600 flex items-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500 rounded px-1.5 py-0.5"
            disabled={loading || isRefreshing}
            variants={iconButtonHoverTapVariants}
          whileHover="hover"
          whileTap="tap"
          >
            <FiX className="mr-1" /> Clear All
            <RippleContainer />
        </motion.button>
        )}
      </div>
      
      {renderFilterSection('Search Products', 'search', (
            <form onSubmit={handleSearchSubmit} className="relative">
              <div className="relative">
                <motion.input
                type="text"
                  placeholder="Search..."
                value={searchValue}
                onChange={handleSearchInputChange}
                  disabled={!!onSearchChange && isSearching}
                  className="w-full pl-8 pr-8 py-2 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-300 focus:outline-none text-sm disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed peer"
                  onFocus={() => setIsSearchFocused(true)}
                  onBlur={() => setIsSearchFocused(false)}
                />
                <motion.div 
                  className="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 origin-center"
                  initial={{ scaleX: 0 }}
                  animate={{ scaleX: isSearchFocused ? 1 : 0 }}
                  transition={{ duration: 0.3, ease: 'easeOut' }}
                />
                <FiSearch className="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" />
              </div>
              {!!onSearchChange && isSearching && (
                <div className="absolute right-2 top-1/2 transform -translate-y-1/2">
                  <LoadingSpinner size="small" />
                </div>
              )}
            </form>
      ), !!onSearchChange && isSearching)}
      
      {renderFilterSection('Sort By', 'sort', renderSortDropdown(), loading || isRefreshing)}
          
      {renderFilterSection('Category', 'category', (
        <div className="space-y-1.5">
          {renderCategories()}
        </div>
      ), loading)}

      {renderFilterSection('Price Range', 'price', (
          <PriceRangeSlider
          min={0}
          max={1000}
          step={10}
          minValue={priceMin ? Number(priceMin) : undefined}
          maxValue={priceMax ? Number(priceMax) : undefined}
          onChange={handlePriceChangeInternal}
          disabled={loading || isRefreshing}
        />
      ), loading || isRefreshing)}
    </aside>
  );
};

export default FilterSidebar; 
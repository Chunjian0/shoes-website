# 产品筛选组件实现分析

## 组件概述

鞋类电商前端应用中的产品筛选组件是一个完整的、可交互的过滤系统，允许用户按照多种条件查找和排序产品。筛选功能分布在几个关键组件中，主要包括：

- `ProductsPage.tsx` - 包含主要的过滤逻辑和状态管理
- `SortSelect.tsx` - 专门的排序下拉组件
- 产品展示组件 (`EnhancedProductCard.tsx` 和 `MinimalProductCard.tsx`)

## 筛选状态管理

在 `ProductsPage.tsx` 中，筛选状态通过以下 React state 管理：

```typescript
// Filter states
const [filters, setFilters] = useState({
  category: '',
  minPrice: '',
  maxPrice: '',
  sort: 'newest'
});

// Search state
const [searchQuery, setSearchQuery] = useState('');
```

## 筛选功能实现

### 1. 前端筛选逻辑

`ProductsPage.tsx` 中使用 `useEffect` 钩子在本地对从 API 获取的产品数据进行筛选：

```typescript
// Apply frontend filtering when templates or filter criteria change
useEffect(() => {
  if (!templates || templates.length === 0) {
    setFilteredTemplates([]);
    return;
  }
  
  let result = [...templates];
  
  // Apply search filter
  if (searchQuery.trim() !== '') {
    const query = searchQuery.toLowerCase();
    result = result.filter(template => 
      (template.title && template.title.toLowerCase().includes(query)) ||
      (template.description && template.description.toLowerCase().includes(query)) ||
      (template.category && template.category.name && 
       template.category.name.toLowerCase().includes(query))
    );
  }
  
  // Apply category filter
  if (filters.category) {
    result = result.filter(template => 
      template.category && 
      (template.category.id === Number(filters.category) || 
       template.category.id === filters.category)
    );
  }
  
  // Apply price filters
  if (filters.minPrice) {
    const minPrice = Number(filters.minPrice);
    result = result.filter(template => {
      // Find minimum price from related products
      const templatePrice = template.price || 
        (template.related_products?.length > 0 ? 
          Math.min(...template.related_products
            .filter(p => p.price && typeof p.price === 'number')
            .map(p => p.price)) : 
          0);
      return templatePrice >= minPrice;
    });
  }
  
  if (filters.maxPrice) {
    const maxPrice = Number(filters.maxPrice);
    result = result.filter(template => {
      // Find minimum price from related products
      const templatePrice = template.price || 
        (template.related_products?.length > 0 ? 
          Math.min(...template.related_products
            .filter(p => p.price && typeof p.price === 'number')
            .map(p => p.price)) : 
          0);
      return templatePrice <= maxPrice;
    });
  }
  
  // Apply sorting
  switch (filters.sort) {
    case 'price_asc':
      result.sort((a, b) => {
        const priceA = a.price || (a.related_products?.length > 0 ? 
          Math.min(...a.related_products
            .filter(p => p.price && typeof p.price === 'number')
            .map(p => p.price)) : 0);
        const priceB = b.price || (b.related_products?.length > 0 ? 
          Math.min(...b.related_products
            .filter(p => p.price && typeof p.price === 'number')
            .map(p => p.price)) : 0);
        return priceA - priceB;
      });
      break;
    case 'price_desc':
      result.sort((a, b) => {
        const priceA = a.price || (a.related_products?.length > 0 ? 
          Math.min(...a.related_products
            .filter(p => p.price && typeof p.price === 'number')
            .map(p => p.price)) : 0);
        const priceB = b.price || (b.related_products?.length > 0 ? 
          Math.min(...b.related_products
            .filter(p => p.price && typeof p.price === 'number')
            .map(p => p.price)) : 0);
        return priceB - priceA;
      });
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
  
  setFilteredTemplates(result);
}, [templates, searchQuery, filters]);
```

### 2. API 请求筛选

除了前端筛选，系统也支持通过 API 请求进行筛选：

```typescript
useEffect(() => {
  const loadPageData = async () => {
    // ...
    
    // 构建参数
    const params: any = {
      page: currentPage,
      per_page: 12,
    };
    
    // 添加筛选条件
    if (filters.category) params.category = filters.category;
    if (filters.minPrice) params.min_price = filters.minPrice;
    if (filters.maxPrice) params.max_price = filters.maxPrice;
    if (filters.sort) params.sort = filters.sort;
    
    // 如果有搜索关键词，将其添加到请求参数中
    if (searchQuery.trim() !== '') {
      params.search = searchQuery;
    }
    
    logDebug('发送产品模板查询请求', params);
    const resultAction = await dispatch(fetchTemplates(params));
    
    // ...
  };
  
  loadPageData();
}, [dispatch, filters, searchQuery, currentPage, categories, useCache]);
```

### 3. 筛选器 UI 实现

筛选器用户界面在 `ProductsPage.tsx` 的侧边栏中实现，包括以下元素：

```jsx
<div className="p-4">
  {/* Search box */}
  <form onSubmit={handleSearch} className="mb-6">
    <label className="block text-sm font-medium text-gray-700 mb-2">
      Search Products
    </label>
    <div className="relative">
      <input
        type="text"
        placeholder="Enter keywords..."
        value={searchQuery}
        onChange={(e) => setSearchQuery(e.target.value)}
        className="w-full px-4 py-2 pr-10 border border-gray-300 rounded-sm focus:ring-1 focus:ring-black focus:border-black transition-all"
      />
      <button 
        type="submit" 
        className="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
      >
        <FiSearch size={16} />
      </button>
    </div>
  </form>
  
  {/* Category filter */}
  <div className="mb-6">
    <label className="block text-sm font-medium text-gray-700 mb-2">
      Category
    </label>
    <select
      name="category"
      value={filters.category}
      onChange={handleFilterChange}
      className="w-full px-4 py-2 border border-gray-300 rounded-sm focus:ring-1 focus:ring-black focus:border-black bg-white transition-all"
    >
      <option value="">All Categories</option>
      {categories && categories.map((category) => (
        <option key={category.id} value={category.id}>
          {category.name}
        </option>
      ))}
    </select>
  </div>
  
  {/* Price range */}
  <div className="mb-6">
    <label className="block text-sm font-medium text-gray-700 mb-2">
      Price Range
    </label>
    <div className="flex items-center gap-2">
      <input
        type="number"
        name="minPrice"
        placeholder="Min"
        value={filters.minPrice}
        onChange={handleFilterChange}
        className="w-full px-4 py-2 border border-gray-300 rounded-sm focus:ring-1 focus:ring-black focus:border-black transition-all"
      />
      <span className="text-gray-500">-</span>
      <input
        type="number"
        name="maxPrice"
        placeholder="Max"
        value={filters.maxPrice}
        onChange={handleFilterChange}
        className="w-full px-4 py-2 border border-gray-300 rounded-sm focus:ring-1 focus:ring-black focus:border-black transition-all"
      />
    </div>
  </div>
  
  {/* Sort by */}
  <div className="mb-6">
    <label className="block text-sm font-medium text-gray-700 mb-2">
      Sort By
    </label>
    <select
      name="sort"
      value={filters.sort}
      onChange={handleFilterChange}
      className="w-full px-4 py-2 border border-gray-300 rounded-sm focus:ring-1 focus:ring-black focus:border-black bg-white transition-all"
    >
      <option value="newest">Newest First</option>
      <option value="price_asc">Price: Low to High</option>
      <option value="price_desc">Price: High to Low</option>
      <option value="popularity">Popularity</option>
    </select>
  </div>
</div>
```

## 4. 排序组件 (SortSelect.tsx)

专门的排序组件 `SortSelect.tsx` 提供了更具样式化的排序选项：

```jsx
const SortSelect: React.FC<SortSelectProps> = ({ currentSort, onChange }) => {
  return (
    <div className="relative inline-block min-w-[180px]">
      <select
        className="appearance-none w-full px-4 py-2 pr-8 bg-white border border-gray-300 hover:border-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
        value={currentSort}
        onChange={(e) => onChange(e.target.value)}
      >
        {sortOptions.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
        <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
          <path
            fillRule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clipRule="evenodd"
          />
        </svg>
      </div>
    </div>
  );
};
```

## 5. 响应式实现与动画效果

筛选组件在大屏和小屏幕上都有良好的适配：

- 在移动设备上，筛选器以抽屉式侧边栏展示，通过动画呈现
- 在桌面设备上，筛选器永久显示在左侧
- 使用 Framer Motion 实现平滑动画，提升用户体验

```jsx
<AnimatePresence>
  {(!isMobileFilterOpen && typeof window !== 'undefined' && window.innerWidth < 768) ? null : (
    <motion.div 
      className={`${isMobileFilterOpen ? 'fixed inset-0 z-50 bg-black bg-opacity-30 flex items-center justify-center p-4' : ''} md:static md:block md:w-64 md:flex-shrink-0 md:z-auto`}
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      transition={{ duration: 0.2 }}
    >
      {/* 筛选区内容 */}
    </motion.div>
  )}
</AnimatePresence>
```

## 6. API 与 Redux 集成

筛选功能与 Redux 状态管理系统集成，通过 `productSlice.ts` 中定义的 actions 获取筛选后的产品：

```typescript
// 异步Action: 获取产品列表
export const fetchProducts = createAsyncThunk(
  'product/fetchProducts',
  async ({ 
    page = 1, 
    per_page = 12, 
    category, 
    min_price, 
    max_price, 
    sort
  }: { 
    page?: number; 
    per_page?: number; 
    category?: string;
    min_price?: string;
    max_price?: string;
    sort?: string;
  }, { rejectWithValue }) => {
    try {
      // 构建过滤参数
      const params: any = { page, per_page };
      if (category) params.category = category;
      if (min_price) params.min_price = min_price;
      if (max_price) params.max_price = max_price;
      if (sort) params.sort = sort;

      const response = await apiService.products.getAll(params);
      return response;
    } catch (error: any) {
      return rejectWithValue(error.message || '获取产品列表失败');
    }
  }
);
```

## 7. 性能优化

为了提高性能，系统实现了数据缓存：

```typescript
// 尝试从缓存获取数据
if (useCache) {
  const cacheKey = `templates_page:${currentPage}:${filters.category}:${filters.minPrice}:${filters.maxPrice}:${filters.sort}:${searchQuery}`;
  const cachedData = await redisService.get(cacheKey);
  
  if (cachedData) {
    logDebug('从缓存加载模板数据', cachedData);
    return; // 如果有缓存数据，不需要发送API请求
  }
}
```

## 总结

产品筛选组件的实现是一个全面的、性能优化的系统，它：

1. 支持多种筛选条件（类别、价格范围、关键词搜索）
2. 提供多种排序选项（最新、价格升序/降序、热门程度）
3. 在前端和API层面同时实现筛选逻辑
4. 通过Redis缓存提高性能
5. 使用Framer Motion实现流畅的动画效果
6. 采用响应式设计适应不同屏幕尺寸
7. 与Redux状态管理系统集成

这种实现方式为用户提供了强大而灵活的产品筛选体验，同时保持了良好的性能和用户界面。 
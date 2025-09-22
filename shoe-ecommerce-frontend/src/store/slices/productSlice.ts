import { createSlice, createAsyncThunk, PayloadAction, isRejectedWithValue, isPending, isFulfilled, AnyAction } from '@reduxjs/toolkit';
import { apiService } from '../../services/api';
import { toast } from 'react-toastify';
import { Product, ProductCategory as Category } from '../../types';
import { ProductTemplate } from '../../types/apiTypes';

// 定义通用 API 响应结构
interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  [key: string]: any; // 其他可能的属性
}

// --- 重新添加内部定义的 ProductReview 和 ProductStock ---
export interface ProductReview {
  id: number;
  product_id: number;
  user_id?: number;
  user_name: string;
  rating: number;
  title?: string;
  comment: string;
  images?: Array<{ url: string }>;
  created_at: string;
  updated_at?: string;
}

export interface ProductStock {
  in_stock: boolean;
  quantity: number;
  available_sizes?: string[];
  available_colors?: string[];
  [key: string]: any; // 允许其他可能的属性
}
// --- End 内部定义 ---

// Define types or use any
type ProductFilters = Record<string, string | number | boolean | undefined>;

// Pagination structure (adjust based on actual API response)
export interface Pagination {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
  from?: number;
  to?: number;
}

// Expected shape for fetchProducts response payload
export interface FetchProductsResponse {
  products: Product[];
  pagination: Pagination;
}

// Expected shape for fetchProductReviews response payload
export interface FetchReviewsResponse {
  data: ProductReview[];
  total: number;
  average_rating: number;
}

// --- New Interfaces for Templates ---
export interface TemplatePaginationMeta {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

export interface FetchTemplatesApiResponse {
  data: ProductTemplate[];
  meta: TemplatePaginationMeta;
}

export interface FetchTemplateDetailsApiResponse {
  data: ProductTemplate;
}
// --- End New Interfaces ---

// --- API Response Wrappers (Assuming a standard wrapper) ---
export interface ApiProductListData {
  products: Product[];
  pagination: Pagination;
}

export interface ApiReviewListData {
  data: ProductReview[];
  total: number;
  average_rating: number;
}

export interface ApiTemplateListData {
  data: ProductTemplate[];
  meta: TemplatePaginationMeta;
}
// --- End API Response Wrappers ---

export interface ProductState {
  products: Product[];
  templates: ProductTemplate[];
  featuredProducts: Product[];
  newProducts: Product[];
  saleProducts: Product[];
  popularProducts: Product[];
  relatedProducts: Product[];
  categories: Category[];
  currentProduct: Product | null;
  currentTemplate: ProductTemplate | null;
  productStock: ProductStock[];
  productReviews: {
    data: ProductReview[];
    total: number;
    average_rating: number;
  };
  loading: boolean; // For general product/stock/review loading
  templatesLoading: boolean; // For template list/details loading
  categoriesLoading: boolean; // For category loading
  error: string | null;
  filters: ProductFilters;
  totalProducts: number;
  totalTemplates: number;
  currentPage: number;
  totalPages: number;
  perPage: number;
}

// 调试日志函数 (Improved typing)
const logDebug = <T = unknown>(message: string, data?: T) => {
  console.log(`[ProductSlice] ${message}`, data ? data : '');
};

// 初始状态
const initialState: ProductState = {
  products: [],
  templates: [],
  featuredProducts: [],
  newProducts: [],
  saleProducts: [],
  popularProducts: [],
  relatedProducts: [],
  categories: [],
  currentProduct: null,
  currentTemplate: null,
  productStock: [],
  productReviews: {
    data: [],
    total: 0,
    average_rating: 0
  },
  loading: false, // Initialize combined loading state
  templatesLoading: false,
  categoriesLoading: false,
  error: null,
  filters: {},
  totalProducts: 0,
  totalTemplates: 0,
  currentPage: 1,
  totalPages: 1,
  perPage: 12
};

// Type for fetchProducts/fetchTemplates payload (Updated to include all filters)
interface FetchListPayload {
  page?: number;
  per_page?: number;
  category?: string;
  min_price?: string; // Added
  max_price?: string; // Added
  sort?: string;
  search?: string;    // Added
  featured?: boolean; // Added
  new?: boolean;      // Added
  sale?: boolean;     // Added
}

// 获取产品列表
export const fetchProducts = createAsyncThunk<
  ApiProductListData, // Return type on success
  FetchListPayload, // Argument type
  { rejectValue: string } // Type for rejectWithValue
>(
  'product/fetchProducts',
  async ({ 
    page = 1, 
    per_page = 12, 
    category, 
    min_price, 
    max_price, 
    sort
  } = {}, { rejectWithValue }) => {
    try {
      const params: FetchListPayload = { page, per_page }; // Use defined type
      if (category) params.category = category;
      if (min_price) params.min_price = min_price;
      if (max_price) params.max_price = max_price;
      if (sort) params.sort = sort;
      logDebug<FetchListPayload>('获取产品列表', params);

      const response = await apiService.products.getList(params);
      logDebug<ApiResponse<ApiProductListData>>('获取产品列表 响应', response);

      if (response && response.success && response.data && response.data.products && response.data.pagination) {
        // Validate structure more deeply if needed
        if (!Array.isArray(response.data.products)) {
           throw new Error('Products data is not an array');
        }
        return response.data; // Return only the data part on success
      } else {
        throw new Error(response?.message || 'Failed to fetch products: Invalid response structure');
      }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取产品列表失败';
      logDebug('获取产品列表失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取特色产品
export const fetchFeaturedProducts = createAsyncThunk<
  Product[], // Success return type
  number | void, // Argument type
  { rejectValue: string } // Reject type
>(
  'product/fetchFeaturedProducts',
  async (limit = 8, { rejectWithValue }) => {
    try {
      logDebug<object>('获取特色产品', { limit });
      const response = await apiService.products.getFeatured({ limit });
      logDebug<ApiResponse<Product[]>>('获取特色产品 响应', response);
      if (response && response.success && Array.isArray(response.data)) {
        return response.data;
      } else {
        throw new Error(response?.message || 'Invalid API response structure for featured products');
      }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取特色产品失败';
      logDebug('获取特色产品失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取新品
export const fetchNewProducts = createAsyncThunk<
  Product[], 
  number | void, 
  { rejectValue: string }
>(
  'product/fetchNewProducts',
  async (limit = 8, { rejectWithValue }) => {
    try {
      logDebug<object>('获取新品', { limit });
      const response = await apiService.products.getNewArrivals({ limit });
      logDebug<ApiResponse<Product[]>>('获取新品 响应', response);
      if (response && response.success && Array.isArray(response.data)) {
         return response.data;
       } else {
         throw new Error(response?.message || 'Invalid API response structure for new products');
       }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取新品失败';
      logDebug('获取新品失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取促销产品
export const fetchSaleProducts = createAsyncThunk<
  Product[], 
  number | void, 
  { rejectValue: string }
>(
  'product/fetchSaleProducts',
  async (limit = 8, { rejectWithValue }) => {
    try {
      const params = { is_sale: true, limit };
      logDebug<object>('获取促销产品', params);
      const response = await apiService.products.getList({
        is_sale: true,
        limit
      });
      logDebug<ApiResponse<ApiProductListData>>('获取促销产品 响应', response);
       // Note: getList returns pagination, but this thunk expects only products
      if (response && response.success && response.data && Array.isArray(response.data.products)) {
         return response.data.products; // Return only products array
       } else {
         throw new Error(response?.message || 'Invalid API response structure for sale products');
       }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取促销产品失败';
      logDebug('获取促销产品失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取热门产品
export const fetchPopularProducts = createAsyncThunk<
  Product[], 
  number | undefined,
  { rejectValue: string }
>(
  'product/fetchPopularProducts',
  async (limit: number = 8, { rejectWithValue }) => {
    try {
      logDebug<object>('获取热门产品', { limit });
      const response = await apiService.products.getPopular(limit);
      logDebug<ApiResponse<Product[]>>('获取热门产品 响应', response);
      if (response && response.success && Array.isArray(response.data)) {
        return response.data;
      } else {
        throw new Error(response?.message || 'Invalid API response structure for popular products');
      }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取热门产品失败';
      logDebug('获取热门产品失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取相关产品
export const fetchRelatedProducts = createAsyncThunk<
  Product[], 
  { productId: number | string; limit?: number }, 
  { rejectValue: string }
>(
  'product/fetchRelatedProducts',
  async ({ productId, limit = 4 }, { rejectWithValue }) => {
    try {
      logDebug<object>('获取相关产品', { productId, limit });
      const response = await apiService.products.getRelated(Number(productId), limit);
      logDebug<ApiResponse<Product[]>>('获取相关产品 响应', response);
      if (response && response.success && Array.isArray(response.data)) {
         return response.data;
       } else {
         throw new Error(response?.message || 'Invalid API response structure for related products');
       }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取相关产品失败';
      logDebug('获取相关产品失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取产品详情
export const fetchProductDetails = createAsyncThunk<
  Product, 
  number | string, 
  { rejectValue: string }
>(
  'product/fetchProductDetails',
  async (id, { rejectWithValue }) => {
    try {
      logDebug<object>('获取产品详情', { id });
      const response = await apiService.products.getById(id);
      logDebug<ApiResponse<{ product: Product } | Product>>('获取产品详情 响应', response);
      if (response && response.success && response.data) {
        // Handle potential nesting (e.g., { data: { product: ... } } or { data: ... })
        const productData = (response.data as any).product ?? response.data;
        if (productData && typeof productData === 'object') {
          return productData as Product;
        } 
      }
      throw new Error(response?.message || 'Invalid API response structure for product details');
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取产品详情失败';
      logDebug('获取产品详情失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取产品库存
export const fetchProductStock = createAsyncThunk<
  ProductStock[], 
  number | string, 
  { rejectValue: string }
>(
  'product/fetchProductStock',
  async (id, { rejectWithValue }) => {
    try {
      logDebug<object>('获取产品库存', { id });
      const response = await apiService.products.getStock(Number(id));
      logDebug<ApiResponse<ProductStock | ProductStock[]>>('获取产品库存 响应', response);
      if (response && response.success && response.data) {
        // Handle cases where API might return single object or array
        if (!Array.isArray(response.data)) {
          console.warn('[ProductSlice] Product stock response data is not an array, wrapping it.');
          return [response.data] as ProductStock[];
        }
        return response.data as ProductStock[];
      } else {
         throw new Error(response?.message || 'Invalid API response structure for product stock');
      }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取产品库存失败';
      logDebug('获取产品库存失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取产品评论
export const fetchProductReviews = createAsyncThunk<
  ApiReviewListData, // Use the specific data structure
  number | string, 
  { rejectValue: string }
>(
  'product/fetchProductReviews',
  async (id, { rejectWithValue }) => {
    try {
      logDebug<object>('获取产品评论', { id });
      const response = await apiService.products.getReviews(Number(id));
      logDebug<ApiResponse<ApiReviewListData>>('获取产品评论 响应', response);
       if (response && response.success && response.data && 
           Array.isArray(response.data.data) && 
           typeof response.data.total === 'number' && 
           typeof response.data.average_rating === 'number') {
          return response.data; // Return the data part
        } else {
           throw new Error(response?.message || 'Invalid API response structure for product reviews');
        }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取产品评论失败';
      logDebug('获取产品评论失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取产品模板列表
export const fetchTemplates = createAsyncThunk<
  ApiTemplateListData, // Use the specific data structure { data: [], meta: {} }
  FetchListPayload, 
  { rejectValue: string }
>(
  'product/fetchTemplates',
  async ({ 
    page = 1, 
    per_page = 12, 
    category, 
    min_price, // Destructure added filters
    max_price,
    sort,
    search,   
    featured, 
    new: isNew, // Use alias for 'new' keyword clash
    sale      
  } = {}, { rejectWithValue }) => {
    try {
      // Include all potential params in the log and API call
      const params: FetchListPayload = { page, per_page }; 
      if (category) params.category = category;
      if (min_price) params.min_price = min_price;
      if (max_price) params.max_price = max_price;
      if (sort) params.sort = sort;
      if (search) params.search = search;
      if (featured) params.featured = featured;
      if (isNew) params.new = isNew;
      if (sale) params.sale = sale;
      
      logDebug<FetchListPayload>('获取产品模板列表', params);

      const response = await apiService.templates.getAll(params);
      logDebug<ApiResponse<any>>('获取产品模板列表 原始响应', response); // Log raw response

      // --- Updated Logic to handle {success: true, data: [...], meta: {...}} ---
      if (response && response.success && 
          Array.isArray(response.data) && // Check if response.data is the array
          response.meta &&              // Check if response.meta exists
          typeof response.meta.total === 'number') { 
          
          logDebug('模板列表数据提取成功', { data: response.data, meta: response.meta });
          // Construct the expected return structure
          return { 
              data: response.data as ProductTemplate[], 
              meta: response.meta as TemplatePaginationMeta 
          };
      } else {
          // Handle other potential valid structures, e.g., nested { data: { data: [], meta: {} } }
          if (response && response.success && response.data &&
              Array.isArray(response.data.data) && response.data.meta) {
              logDebug('模板列表数据提取成功 (nested structure)', response.data);
              return response.data as ApiTemplateListData; // Return nested data directly
          }
          // If none match, structure is invalid
          console.warn('Thunk received invalid structure for templates:', response);
          throw new Error(response?.message || 'Invalid API response structure for templates');
      }
      // --- End Updated Logic ---

    } catch (error: any) {
      const message = error?.message || '获取产品模板列表失败';
      logDebug('获取产品模板列表失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取产品模板详情
export const fetchTemplateDetails = createAsyncThunk<
  ProductTemplate, 
  number | string, 
  { rejectValue: string }
>(
  'product/fetchTemplateDetails',
  async (id, { rejectWithValue }) => {
    try {
      logDebug<object>('获取产品模板详情', { id });
      const response = await apiService.templates.getById(id);
      logDebug<ApiResponse<ProductTemplate | { data: ProductTemplate }>>('获取产品模板详情 响应', response);
      if (response && response.success && response.data) {
        // Handle potential nesting { data: { data: ... } } or { data: ... }
        const templateData = (response.data as any).data ?? response.data;
         if (templateData && typeof templateData === 'object') {
          return templateData as ProductTemplate;
        } else {
          throw new Error('Template data is not an object');
        }
      } else {
          throw new Error(response?.message || 'Invalid API response structure for template details');
      }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '获取产品模板详情失败';
      logDebug('获取产品模板详情失败', message);
      return rejectWithValue(message);
    }
  }
);

// 获取分类列表
export const fetchCategories = createAsyncThunk<
  Category[], // Return type on success (should be the array of categories)
  void, // No arguments needed
  { rejectValue: string } // Type for rejectWithValue
>(
  'product/fetchCategories',
  async (_, { rejectWithValue }) => {
    try {
      logDebug('获取分类列表');
      // Assuming apiService.categories.getAll() eventually calls the corrected productService.getCategories
      // or directly returns the API response object {success: true, categories: [...]}
      const response = await apiService.categories.getAll();
      logDebug('获取分类列表 响应', response);

      // --- Updated Logic --- 
      // Check the structure received by the thunk
      let categoriesData: Category[] | undefined;
      if (response && typeof response === 'object') {
          if (Array.isArray((response as any).categories)) {
              // If response is { success: true, categories: [...] }
              categoriesData = (response as any).categories;
          } else if (Array.isArray((response as any).data?.categories)) {
              // If response is { success: true, data: { categories: [...] } }
              categoriesData = (response as any).data.categories;
       } else if (Array.isArray(response)) {
              // If response is already the array [...] (less likely but possible)
              categoriesData = response;
          }
      }
      
      // Validate that we extracted the array successfully
      if (categoriesData && Array.isArray(categoriesData)) {
        // Perform any necessary mapping/validation if structure differs from Category[]
        // For now, assume the structure matches Category[] or productService did the mapping
        logDebug('分类列表数据提取成功', categoriesData);
        return categoriesData; // Return the extracted array
       } else {
        // If we couldn't find the array, the structure is invalid
        console.warn('Thunk received invalid structure for categories:', response);
            throw new Error('Invalid API response structure for categories');
        }
      // --- End Updated Logic ---

    } catch (error: any) {
      // Handle errors from the API call itself or the structure check above
      const message = error?.message || '获取分类列表失败';
      logDebug('获取分类列表失败', message);
      // Propagate the specific error message
      return rejectWithValue(message);
    }
  }
);

// --- 添加 searchProducts Thunk ---
interface SearchProductsPayload extends FetchListPayload {
  query: string;
}

export const searchProducts = createAsyncThunk<
  ApiProductListData, // Return type
  SearchProductsPayload, // Argument type
  { rejectValue: string } // Reject type
>(
  'product/searchProducts',
  async ({ query, page = 1, per_page = 12, ...otherFilters }, { rejectWithValue }) => {
    try {
      // 移除 otherFilters 假设 search 只接受 query, page, per_page
      const params = { query, page, per_page }; 
      logDebug<typeof params>('搜索产品', params);
      const response = await apiService.products.search(query, page, per_page); 
      logDebug<ApiResponse<ApiProductListData>>('搜索产品 响应', response);

      if (response && response.success && response.data && response.data.products && response.data.pagination) {
        if (!Array.isArray(response.data.products)) {
          throw new Error('Search results data is not an array');
        }
        return response.data;
      } else {
        throw new Error(response?.message || 'Failed to search products: Invalid response structure');
      }
    } catch (error: any) {
      const message = error?.response?.data?.message || error.message || '搜索产品失败';
      logDebug('搜索产品失败', message);
      return rejectWithValue(message);
    }
  }
);

// --- 修改 addProductReview Thunk 定义 ---
interface AddReviewPayload {
  productId: number | string;
  reviewData: Partial<ProductReview>;
}

export const addProductReview = createAsyncThunk<
  ProductReview, 
  AddReviewPayload,
  { rejectValue: string }
>(
  'product/addProductReview',
  async ({ productId, reviewData }, { rejectWithValue }) => {
    try {
      logDebug<AddReviewPayload>('尝试添加产品评论', { productId, reviewData });
      
      // --- 暂时注释掉 API 调用，因为方法不存在 ---
      console.warn('addProductReview API call is commented out/not implemented yet in apiService.products');
      throw new Error('Add review functionality is not implemented in the API service yet.');
      /*
      // Assuming apiService.products.addReview exists
      const response = await apiService.products.addReview(Number(productId), reviewData);
      logDebug<ApiResponse<ProductReview>>('添加产品评论 响应', response);

      if (response && response.success && response.data) {
        return response.data as ProductReview;
      } else {
        throw new Error(response?.message || 'Failed to add review: Invalid response structure');
      }
      */
     // --- 结束注释 ---

    } catch (error: any) {
      const message = error?.message || '添加评论失败'; // 使用 error.message
      logDebug('添加评论失败', message);
      toast.error(`Failed to add review: ${message}`);
      return rejectWithValue(message);
    }
  }
);

// Helper type predicate to check for rejected actions with payload
function isRejectedAction(action: AnyAction): action is { type: string; payload: any; error: any } {
  return action.type.endsWith('/rejected') && action.payload !== undefined;
}

// Helper type predicate to check for rejected actions without payload (generic error)
function isRejectedActionWithError(action: AnyAction): action is { type: string; error: { message?: string } } {
   return action.type.endsWith('/rejected') && action.error !== undefined;
}

// 产品Slice定义
const productSlice = createSlice({
  name: 'product',
  initialState,
  reducers: {
    // 设置过滤器
    setFilters(state, action: PayloadAction<Partial<ProductFilters>>) {
      state.filters = { ...state.filters, ...action.payload };
      state.currentPage = 1; // Reset page when filters change
      logDebug('过滤器已设置', state.filters);
    },
    // 清除过滤器
    clearFilters(state) {
      state.filters = {};
      state.currentPage = 1;
      logDebug('过滤器已清除');
    },
    // 设置当前页码
    setCurrentPage(state, action: PayloadAction<number>) {
      state.currentPage = action.payload;
      logDebug('当前页码已设置', state.currentPage);
    },
    // 清除当前产品详情
    clearCurrentProduct(state) {
      state.currentProduct = null;
      state.productStock = [];
      state.productReviews = { data: [], total: 0, average_rating: 0 };
      logDebug('当前产品详情已清除');
    },
    // 清除当前模板详情
    clearCurrentTemplate(state) {
      state.currentTemplate = null;
       logDebug('当前模板详情已清除');
    },
    // 设置错误信息
    setError(state, action: PayloadAction<string | null>) {
      state.error = action.payload;
    },
    // --- 添加 setPerPage 和 clearProductError --- 
    setPerPage(state, action: PayloadAction<number>) {
      state.perPage = action.payload;
      state.currentPage = 1; // Reset page when perPage changes
      logDebug('每页数量已设置', state.perPage);
    },
    clearProductError(state) {
      state.error = null;
      logDebug('产品错误已清除');
    }
    // --- 结束添加 ---
  },
  extraReducers: (builder) => {
    builder
      // --- .addCase calls --- 
      // Fetch Products / Search Products (uses general loading)
      .addCase(fetchProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProducts.fulfilled, (state, action: PayloadAction<ApiProductListData>) => {
        state.loading = false;
        state.products = action.payload.products;
        state.totalProducts = action.payload.pagination.total;
        state.currentPage = action.payload.pagination.current_page;
        state.totalPages = action.payload.pagination.last_page;
        state.perPage = action.payload.pagination.per_page;
      })
      .addCase(searchProducts.pending, (state) => {
        state.loading = true; // Use general loading for search as well
        state.error = null;
      })
      .addCase(searchProducts.fulfilled, (state, action: PayloadAction<ApiProductListData>) => {
        state.loading = false;
        state.products = action.payload.products; // Update main products list with search results
        state.totalProducts = action.payload.pagination.total;
        state.currentPage = action.payload.pagination.current_page;
        state.totalPages = action.payload.pagination.last_page;
        state.perPage = action.payload.pagination.per_page;
      })
      // Featured, New, Sale, Popular, Related (Use general loading)
      .addCase(fetchFeaturedProducts.pending, (state) => {
        state.loading = true; 
        state.error = null;
      })
      .addCase(fetchFeaturedProducts.fulfilled, (state, action: PayloadAction<Product[]>) => {
        state.featuredProducts = action.payload;
        state.loading = false; // Turn off general loading
      })
      .addCase(fetchNewProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchNewProducts.fulfilled, (state, action: PayloadAction<Product[]>) => {
        state.newProducts = action.payload;
        state.loading = false;
      })
      .addCase(fetchSaleProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchSaleProducts.fulfilled, (state, action: PayloadAction<Product[]>) => {
        state.saleProducts = action.payload;
        state.loading = false;
      })
      .addCase(fetchPopularProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPopularProducts.fulfilled, (state, action: PayloadAction<Product[]>) => {
        state.popularProducts = action.payload;
        state.loading = false;
      })
      .addCase(fetchRelatedProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchRelatedProducts.fulfilled, (state, action: PayloadAction<Product[]>) => {
        state.relatedProducts = action.payload;
        state.loading = false;
      })
      // Product Details, Stock, Reviews (Use general loading)
      .addCase(fetchProductDetails.pending, (state) => {
        state.loading = true;
        state.currentProduct = null;
        state.error = null;
      })
      .addCase(fetchProductDetails.fulfilled, (state, action: PayloadAction<Product>) => {
        state.currentProduct = action.payload;
        state.loading = false;
      })
      .addCase(fetchProductStock.pending, (state) => {
        state.loading = true;
        state.productStock = [];
        state.error = null;
      })
      .addCase(fetchProductStock.fulfilled, (state, action: PayloadAction<ProductStock[]>) => {
        state.productStock = action.payload;
        state.loading = false;
      })
      .addCase(fetchProductReviews.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProductReviews.fulfilled, (state, action: PayloadAction<ApiReviewListData>) => {
        state.productReviews = action.payload;
        state.loading = false;
      })
      // Categories (uses categoriesLoading)
      .addCase(fetchCategories.pending, (state) => {
        state.categoriesLoading = true;
        state.error = null;
      })
      .addCase(fetchCategories.fulfilled, (state, action: PayloadAction<Category[]>) => {
            state.categories = action.payload;
        state.categoriesLoading = false;
      })
      // Templates (uses templatesLoading)
      .addCase(fetchTemplates.pending, (state) => {
        state.templatesLoading = true;
        state.error = null;
      })
      .addCase(fetchTemplates.fulfilled, (state, action: PayloadAction<FetchTemplatesApiResponse>) => {
        state.templatesLoading = false;
        state.templates = action.payload.data;
        state.totalTemplates = action.payload.meta.total;
        state.currentPage = action.payload.meta.current_page;
        state.totalPages = action.payload.meta.last_page;
        state.perPage = action.payload.meta.per_page;
      })
      .addCase(fetchTemplateDetails.pending, (state) => {
        state.templatesLoading = true;
        state.currentTemplate = null;
        state.error = null;
      })
      .addCase(fetchTemplateDetails.fulfilled, (state, action: PayloadAction<ProductTemplate>) => { 
        state.templatesLoading = false;
        state.currentTemplate = action.payload; 
      })

      // --- .addMatcher calls --- 
      .addMatcher(
        isPending,
        (state, action) => { // Check action type to set correct loading state
          if (action.type.startsWith('product/fetchCategories')) {
             // Already handled by addCase
          } else if (action.type.startsWith('product/fetchTemplate')) {
             // Already handled by addCase
          } else if (action.type.startsWith('product/')) {
             // Set general loading for other product thunks if not already loading
             if (!state.loading) state.loading = true;
          }
          state.error = null; 
        }
      )
      .addMatcher(
        isRejectedWithValue,
        (state, action) => {
          // Turn off all loading flags on rejection
          state.loading = false;
          state.templatesLoading = false;
          state.categoriesLoading = false;
          const errorMessage = typeof action.payload === 'string' 
              ? action.payload 
              : action.error?.message || 'An unknown error occurred';
          state.error = errorMessage;
          logDebug('Async Thunk Rejected', { type: action.type, error: state.error, payload: action.payload });
          toast.error(errorMessage); 
        }
      )
      .addMatcher(
        isFulfilled,
        (state, action) => { // Turn off relevant loading state
            if (action.type.startsWith('product/fetchCategories')) {
                 // Already handled by addCase
            } else if (action.type.startsWith('product/fetchTemplate')) {
                 // Already handled by addCase
            } else if (action.type.startsWith('product/')){
                 state.loading = false; // Turn off general loading on fulfillment for other product actions
            }
          state.error = null; 
        }
      );
  },
});

// 导出 actions 和 reducer
export const {
  setFilters,
  clearFilters,
  setCurrentPage,
  setPerPage,
  clearCurrentProduct,
    clearCurrentTemplate,
    setError,
  clearProductError
} = productSlice.actions;

export default productSlice.reducer; 
import { apiService } from './api';
import { Product, ProductCategory as Category } from '../types';
import { ProductReview } from '../types/product';
import { ProductTemplate } from '../types/apiTypes';
import { ProductItem } from '../types/homepage';
import { ProductStock } from '../store/slices/productSlice';

// 调试日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[ProductService] ${message}`, data ? data : '');
};

// 产品评价摘要类型定义
export interface ReviewSummary {
  average_rating: number;
  total_reviews: number;
  rating_distribution: Record<string, number>;
}

// 分页类型定义
export interface Pagination {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
  from?: number;
  to?: number;
}

// 产品列表响应类型定义
export interface ProductListResponse {
  products: Product[];
  pagination: Pagination;
}

// 产品评价列表响应类型定义
export interface ProductReviewsResponse {
  reviews: ProductReview[];
  summary: ReviewSummary;
  pagination: Pagination;
}

// 产品服务
const productService = {
  // 获取产品列表
  async getProducts(params?: Record<string, any>): Promise<ProductListResponse> {
    try {
      const response = await apiService.products.getList(params);
      const responseData = response.data as any; 
      if (!responseData?.products || !responseData?.pagination) {
        throw new Error('Invalid API response structure for products');
      }
      const mappedProducts = responseData.products.map((p: any): Product => ({
        id: p.id,
        name: p.name,
        price: p.selling_price ?? p.price ?? 0,
        sale_price: p.original_price && (p.selling_price ?? p.price ?? 0) < p.original_price ? p.original_price : undefined,
        description: p.description ?? '',
        images: Array.isArray(p.images) ? p.images.map((img: any) => typeof img === 'string' ? img : img?.url || '').filter(Boolean) : [],
        main_image: Array.isArray(p.images) && p.images.length > 0 ? (typeof p.images[0] === 'string' ? p.images[0] : p.images[0]?.url || '') : '',
        sku: p.sku,
        category_id: p.category_id,
        brand: p.brand,
        is_active: p.is_active,
        created_at: p.created_at,
        updated_at: p.updated_at,
      }));
      return {
        products: mappedProducts,
        pagination: responseData.pagination as Pagination,
      };
    } catch (error) {
      logDebug('获取产品列表失败', error);
      return {
        products: [], 
        pagination: { total: 0, per_page: 10, current_page: 1, last_page: 1 } 
      };
    }
  },

  // 获取产品详情
  async getProduct(id: number): Promise<Product> {
    try {
      const response = await apiService.products.getById(id);
      const productData = (response?.data?.product ?? response?.data) as any;
      if (!productData || typeof productData !== 'object') {
        throw new Error('Invalid API response structure for product details');
      }
      return { 
        ...productData,
        price: productData.selling_price ?? productData.price ?? 0,
        description: productData.description ?? productData.short_description ?? '',
        images: Array.isArray(productData.images) ? productData.images.map((img: any) => typeof img === 'string' ? img : img?.url || '').filter(Boolean) : [],
        main_image: (Array.isArray(productData.images) && productData.images.length > 0) ? (typeof productData.images[0] === 'string' ? productData.images[0] : productData.images[0]?.url || '') : '',
      } as Product;
    } catch (error) {
      logDebug('获取产品详情失败', error);
      throw new Error(`Failed to fetch product ${id}: ${error instanceof Error ? error.message : String(error)}`); 
    }
  },

  // 获取产品分类
  async getCategories(): Promise<Category[]> {
    try {
      const response = await apiService.categories.getAll(); 
      
      let categoriesData: any[] = [];
      if (response && typeof response === 'object') {
        if (Array.isArray((response as any).categories)) {
          categoriesData = (response as any).categories;
        } else if (Array.isArray((response as any).data?.categories)) {
          categoriesData = (response as any).data.categories;
        } else if (Array.isArray(response)) {
           categoriesData = response;
        }
      }

      if (!Array.isArray(categoriesData)) {
        console.warn('Invalid API response structure for categories. Could not find categories array.', response);
        throw new Error('Invalid API response structure for categories');
      }

      logDebug('Extracted categories data:', categoriesData);

      return categoriesData.map((cat: any): Category => ({ 
        id: cat.id, 
        name: cat.name, 
        code: cat.code || cat.name.toLowerCase().replace(/\s+/g, '-') 
      }));
    } catch (error) {
      logDebug('获取产品分类失败', error);
      throw error;
    }
  },

  // 获取分类参数 - TODO: Verify/Implement API endpoint
  async getCategoryParameters(categoryId: number): Promise<any> {
    try {
      console.warn('getCategoryParameters API call is commented out/not implemented');
      return [];
    } catch (error) {
      logDebug('获取分类参数失败', error);
      return [];
    }
  },

  // 搜索产品
  async searchProducts(query: string, page: number = 1, perPage: number = 10): Promise<ProductListResponse> {
    const params = { query, page, per_page: perPage };
    try {
      const response = await apiService.products.search(query, page, perPage);
      const responseData = response.data as any;
      if (!responseData?.products || !responseData?.pagination) { 
           throw new Error('Invalid API response structure for search');
      }
      const mappedProducts = responseData.products.map((p: any): Product => ({ 
        id: p.id,
        name: p.name,
        price: p.selling_price ?? p.price ?? 0,
        sale_price: p.original_price && (p.selling_price ?? p.price ?? 0) < p.original_price ? p.original_price : undefined,
        description: p.description ?? '',
        images: Array.isArray(p.images) ? p.images.map((img: any) => typeof img === 'string' ? img : img?.url || '').filter(Boolean) : [],
        main_image: Array.isArray(p.images) && p.images.length > 0 ? (typeof p.images[0] === 'string' ? p.images[0] : p.images[0]?.url || '') : '',
        sku: p.sku,
        category_id: p.category_id,
        brand: p.brand,
        is_active: p.is_active,
        created_at: p.created_at,
        updated_at: p.updated_at,
      }));
      return {
          products: mappedProducts,
          pagination: responseData.pagination as Pagination
      };
    } catch (error) {
      logDebug('搜索产品失败', error);
      return {
          products: [], 
          pagination: { total: 0, per_page: perPage, current_page: page, last_page: 1 } 
      };
    }
  },

  // 获取产品库存 (Removed caching)
  async getProductStock(id: number): Promise<ProductStock[]> {
    try {
      const response = await apiService.products.getStock(id);
      logDebug('API返回库存数据', response.data);
      return (response.data || []) as ProductStock[];
    } catch (error) {
      logDebug('获取产品库存失败', error);
      return [];
    }
  },

  // 获取产品评价
  async getProductReviews(id: number, params?: Record<string, any>): Promise<ProductReviewsResponse> {
    try {
      const response = await apiService.products.getReviews(id /*, params */);
      const responseData = response.data as any; 
      if (!responseData?.reviews || !responseData?.summary || !responseData?.pagination) {
           throw new Error('Invalid API response structure for reviews');
      }
      const mappedReviews = responseData.reviews.map((r: any): ProductReview => ({
        id: r.id,
        product_id: r.product_id,
        user_id: r.user_id,
        rating: r.rating,
        review: r.comment || r.review || '',
        user_name: r.user_name || 'Anonymous',
        created_at: r.created_at,
        updated_at: r.updated_at,
      }));
      return { 
          reviews: mappedReviews,
          summary: responseData.summary as ReviewSummary,
          pagination: responseData.pagination as Pagination
      };
    } catch (error) {
      logDebug('获取产品评价失败', error);
      return {
          reviews: [], 
          summary: { average_rating: 0, total_reviews: 0, rating_distribution: {} }, 
          pagination: { total: 0, per_page: 10, current_page: 1, last_page: 1 } 
      };
    }
  },

  // 添加产品评价 - TODO: Verify/Implement API endpoint
  async addProductReview(id: number, data: Partial<ProductReview>): Promise<ProductReview> {
    try {
      console.warn('addProductReview API call is commented out/not implemented');
      throw new Error('Add review functionality not implemented yet.');
    } catch (error) {
      logDebug('添加产品评价失败', error);
      throw error;
    }
  },
};

// Helper function to safely parse price
const parsePrice = (price: any): number => {
  if (typeof price === 'number') return price;
  if (typeof price === 'string') {
    const parsed = parseFloat(price);
    return isNaN(parsed) ? 0 : parsed;
  }
  return 0;
};

// Helper function to get stock status string
const getStockStatus = (stockQuantity: number | undefined | null): 'in_stock' | 'low_stock' | 'out_of_stock' => {
  const quantity = stockQuantity ?? 0;
  if (quantity > 10) return 'in_stock';
  if (quantity > 0) return 'low_stock';
  return 'out_of_stock';
};

/**
 * 将ProductItem转换为Product类型
 * 
 * @param item 从模板获取的ProductItem
 * @returns 转换后的Product对象
 */
export const convertProductItemToProduct = (item: ProductItem): Product => {
  return {
    id: item.id,
    name: item.name,
    price: item.price ?? 0,
    sale_price: item.discount ? (item.price * (1 - item.discount / 100)) : undefined,
    description: item.description ?? '',
    main_image: item.imageUrl,
    images: item.images ? item.images.map((img: any) => img?.url || '').filter(Boolean) : [item.imageUrl].filter(Boolean),
    category_name: item.category,
    brand: 'Unknown',
    rating: item.averageRating,
  } as Product;
};

/**
 * 从产品模板中提取并转换产品
 * 
 * @param template 产品模板
 * @returns 转换后的Product数组
 */
export const convertTemplateToProducts = (template: ProductTemplate): Product[] => {
  if (!template.linked_products || template.linked_products.length === 0) {
    return [];
  }
  
  return template.linked_products.map((linkedProduct: any): Product => { 
    let mainImageUrl = '';
    let allImageUrls: string[] = [];
    
    const linkedImages = Array.isArray(linkedProduct.images) ? linkedProduct.images : [];
    if (linkedImages.length > 0) {
        const firstImage = linkedImages[0];
        mainImageUrl = typeof firstImage === 'string' ? firstImage : firstImage?.url || '';
        allImageUrls = linkedImages.map((img: any) => typeof img === 'string' ? img : img?.url || '').filter(Boolean);
    }
    else if (Array.isArray(template.images) && template.images.length > 0) {
        const firstTemplateImage = template.images[0];
        mainImageUrl = typeof firstTemplateImage === 'string' ? firstTemplateImage : firstTemplateImage?.url || '';
        allImageUrls = template.images.map((img: any) => typeof img === 'string' ? img : img?.url || '').filter(Boolean);
    }
    
    const stockQuantity = linkedProduct.stock_quantity ?? linkedProduct.stock ?? 0;

    return {
      id: linkedProduct.id,
      name: linkedProduct.name ?? template.title ?? 'Product Variant',
      sku: linkedProduct.sku ?? `${template.sku ?? 'TPL'}-${linkedProduct.id}`,
      category_id: template.category?.id ?? 0,
      category_name: template.category?.name,
      brand: (template as any).brand ?? 'Unknown Brand',
      price: parsePrice(linkedProduct.price ?? template.price ?? 0),
      selling_price: parsePrice(linkedProduct.price ?? template.price ?? 0),
      stock: stockQuantity,
      stock_status: getStockStatus(stockQuantity),
      description: template.description ?? '',
      images: allImageUrls,
      main_image: mainImageUrl,
      is_active: true,
      created_at: template.created_at || new Date().toISOString(),
      updated_at: new Date().toISOString(),
      is_featured: template.is_featured,
      is_new: template.is_new_arrival,
      is_sale: template.is_sale,
    } as Product;
  });
};

const API_BASE_URL = '/api'; // Using relative path for Vite proxy

/**
 * Fetches product details by ID from the backend.
 * 
 * @param productId The ID of the product to fetch.
 * @returns A promise that resolves to the Product object.
 * @throws Will throw an error if the fetch request fails.
 */
export const fetchProductById = async (productId: string | number): Promise<Product> => {
  return productService.getProduct(Number(productId));
};

export default productService; 
import { apiService } from './apiService';
// import redisService from './redisService'; // Removed import
import { ProductTemplate, TemplateApiResponse } from '../types/apiTypes';

// 调试日志函数
const logDebug = (message: string, data?: any) => {
  console.log(`[PageDataService] ${message}`, data || '');
};

// 缓存时间（毫秒） - Constants can be removed if no longer used elsewhere
// const CACHE_TIMES = { ... };

// 页面数据服务
const pageDataService = {
  // 获取首页数据 (Removed Cache)
  async getHomePageData() {
    // const cacheKey = 'homepage_data'; // Removed cacheKey

    try {
      // Removed redisService.cacheGetOrSet wrapper
      // Directly execute the fetcher logic
      logDebug('加载首页数据');
      
      // 并行请求所有首页需要的数据
      const [banners, featuredTemplates, newArrivals, saleTemplates, popularCategories] = 
        await Promise.all([
          apiService.homepage.getBanners(),
          apiService.homepage.getFeaturedTemplates(),
          apiService.homepage.getNewArrivalTemplates(),
          apiService.homepage.getSaleTemplates(),
          apiService.categories.getAll()
        ]);
      
      return {
        banners: banners.data,
        featuredProducts: featuredTemplates.data,
        newArrivals: newArrivals.data,
        saleProducts: saleTemplates.data,
        categories: popularCategories.data
      };
      // Removed cache duration argument
    } catch (error) {
      logDebug('获取首页数据失败', error);
      throw error;
    }
  },
  
  // 获取产品列表页数据 (Removed Cache)
  async getProductsPageData(params: any = {}) {
    // const queryString = JSON.stringify(params); // Removed cache related vars
    // const cacheKey = `products_page_${queryString}`;

    try {
      // Removed redisService.cacheGetOrSet wrapper
      // Directly execute the fetcher logic
      logDebug('加载产品列表页数据', params);
      
      // 并行请求产品列表和分类
      const [templates, categories] = await Promise.all([
        apiService.templates.getAll(params),
        apiService.categories.getAll()
      ]);
      
      return {
        templates: templates.data.templates,
        pagination: templates.data.pagination,
        categories: categories.data
      };
      // Removed cache duration argument
    } catch (error) {
      logDebug('获取产品列表页数据失败', error);
      throw error;
    }
  },
  
  // 获取产品详情页数据 (Removed Cache)
  async getProductDetailPageData(id: number) {
    // const cacheKey = `product_detail_${id}`; // Removed cacheKey

    try {
      // Removed redisService.cacheGetOrSet wrapper
      // Directly execute the fetcher logic
      logDebug('加载产品详情页数据', id);
      
      // 并行请求产品详情和相关产品
      const [template, relatedTemplates] = await Promise.all([
        apiService.templates.getById(id),
        apiService.templates.getRelated(id)
      ]);
      
      return {
        product: template.data,
        relatedProducts: relatedTemplates.data
      };
      // Removed cache duration argument
    } catch (error) {
      logDebug('获取产品详情页数据失败', error);
      throw error;
    }
  },
  
  // 获取搜索页数据 (Removed Cache)
  async getSearchPageData(query: string, params: any = {}): Promise<{ results: ProductTemplate[], pagination: any }> {
    const queryParams = { ...params, search: query };
    // const queryString = JSON.stringify(queryParams); // Removed cache related vars
    // const cacheKey = `search_results_${queryString}`;

    try {
      // Removed redisService.cacheGetOrSet wrapper
      // Directly execute the fetcher logic
      logDebug('加载搜索结果', { query, params });
      
      const templates = await apiService.templates.getAll(queryParams);
      
      return {
        results: templates.data.templates,
        pagination: templates.data.pagination
      };
      // Removed cache duration argument
    } catch (error) {
      logDebug('获取搜索结果失败', error);
      throw error;
    }
  },
  
  // 获取购物车页数据 (Removed Cache)
  async getCartPageData() {
    // const cacheKey = 'cart_page_data'; // Removed cacheKey

    try {
      // Removed redisService.cacheGetOrSet wrapper
      // Directly execute the fetcher logic
      logDebug('加载购物车页数据');
      
      // 这里我们不并行请求，因为可能需要根据购物车内容加载推荐商品
      const cartResponse = await apiService.cart.getCart();
      
      // 如果购物车有商品，获取推荐商品
      let recommendedProducts = [];
      if (cartResponse.data && cartResponse.data.items && cartResponse.data.items.length > 0) {
        const recommendedResponse = await apiService.products.getRecommended({
          cart_items: cartResponse.data.items.map((item: any) => item.product_id)
        });
        recommendedProducts = recommendedResponse.data || [];
      }
      
      return {
        cart: cartResponse.data,
        recommendedProducts
      };
      // Removed cache duration argument
    } catch (error) {
      logDebug('获取购物车页数据失败', error);
      throw error;
    }
  },
  
  // 清除页面缓存 (Functionality removed as cache is gone)
  async clearPageCache(pageName: string, params?: any) {
    // Removed cacheKey logic
    logDebug(`清除页面缓存请求 (已移除缓存): ${pageName}`, params);
    // Removed redisService.delete call
    // Function body is now empty, only logging remains
  },
  
  // 清除所有页面缓存 (Functionality removed as cache is gone)
  async clearAllPageCache() {
    logDebug('清除所有页面缓存请求 (已移除缓存)');
    // Removed cacheKeys and loop
    // Removed redisService.delete calls
    // Function body is now empty, only logging remains
  },

  // 获取所有模板（分页）- This function didn't use cache
  async getAllTemplates(params?: any): Promise<TemplateApiResponse> {
    try {
      const response = await apiService.templates.getAll(params);
      // Ensure data exists and has the expected structure
      if (response && response.data && response.data.templates && response.data.pagination) {
        return response; // Return the full response if valid
      } else {
        console.warn('Invalid API response structure for getAllTemplates', response);
        // Return a default structure or throw an error
        return {
          success: false,
          data: {
            templates: [],
            pagination: {
              total: 0,
              per_page: params?.per_page || 10,
              current_page: 1,
              last_page: 1,
              from: 0,
              to: 0,
            },
          },
        };
      }
    } catch (error) {
      console.error('Error fetching all templates in pageDataService:', error);
      throw error; // Re-throw after logging
    }
  },

  // 获取模板详情 - Assuming this is similar, remains unchanged if no cache
  async getTemplateById(id: number): Promise<ProductTemplate | null> {
     try {
       const response = await apiService.templates.getById(id);
       if (response && response.data) {
         // Assuming the template data is directly in response.data
         return response.data as ProductTemplate; 
       } else {
         console.warn(`Template with ID ${id} not found or invalid response`, response);
         return null;
       }
     } catch (error) {
       console.error(`Error fetching template ${id} in pageDataService:`, error);
       throw error; 
     }
  },

  // 获取相关模板 - Assuming this is similar, remains unchanged if no cache
  async getRelatedTemplates(id: number): Promise<ProductTemplate[]> {
    try {
      const response = await apiService.templates.getRelated(id);
       if (response && response.data && Array.isArray(response.data)) {
         return response.data as ProductTemplate[];
       } else {
         console.warn(`No related templates found for ${id} or invalid response`, response);
         return [];
       }
    } catch (error) {
       console.error(`Error fetching related templates for ${id}:`, error);
       throw error;
    }
  },
  
  // --- Example function remains unchanged ---
  async exampleFunctionUsingPagination() {
    const params = { page: 1 };
    const response = await this.getAllTemplates(params);
    
    const totalPages = response.data?.pagination?.last_page || 1; 
    const currentPage = response.data?.pagination?.current_page || 1;
    
    console.log(`Current Page: ${currentPage}, Total Pages: ${totalPages}`);
  },

   // 获取特色模板 (New function - assuming no cache needed)
  async getFeaturedTemplates(params?: any): Promise<TemplateApiResponse> {
    try {
      const response = await apiService.templates.getAll({ ...params, featured: true });
      if (response && response.data && response.data.templates && response.data.pagination) {
        return response;
      } else {
        console.warn('Invalid API response structure for getFeaturedTemplates', response);
        return { success: false, data: { templates: [], pagination: { total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 } } };
      }
    } catch (error) {
      console.error('Error fetching featured templates in pageDataService:', error);
      throw error;
    }
  },

  // 获取最新模板 (New function - assuming no cache needed)
  async getNewArrivalTemplates(params?: any): Promise<TemplateApiResponse> {
     try {
      const response = await apiService.templates.getAll({ ...params, sort: 'newest' }); // Example: assuming 'newest' sort fetches new arrivals
      if (response && response.data && response.data.templates && response.data.pagination) {
        return response;
      } else {
        console.warn('Invalid API response structure for getNewArrivalTemplates', response);
        return { success: false, data: { templates: [], pagination: { total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 } } };
      }
    } catch (error) {
      console.error('Error fetching new arrival templates in pageDataService:', error);
      throw error;
    }
  },

  // 获取促销模板 (New function - assuming no cache needed)
  async getSaleTemplates(params?: any): Promise<TemplateApiResponse> {
    try {
      const response = await apiService.templates.getAll({ ...params, sale: true }); 
       if (response && response.data && response.data.templates && response.data.pagination) {
        return response;
      } else {
        console.warn('Invalid API response structure for getSaleTemplates', response);
        return { success: false, data: { templates: [], pagination: { total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 } } };
      }
    } catch (error) {
      console.error('Error fetching sale templates in pageDataService:', error);
      throw error;
    }
  },

};

export default pageDataService; 
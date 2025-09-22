import { api } from './api';
import { ProductTemplate, TemplateApiResponse } from '../types/apiTypes';

interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  error?: string;
}

const apiService = {
  homepage: {
    getData: async (): Promise<any> => {
      try {
        const response = await api.get('/homepage');
        return response.data;
      } catch (error) {
        console.error('获取首页数据失败:', error);
        throw error;
      }
    },
    getFeaturedTemplates: async (): Promise<ApiResponse<ProductTemplate[]>> => {
      try {
        const response = await api.get('/templates/featured');
        return response.data;
      } catch (error) {
        console.error('获取特色模板失败:', error);
        throw error;
      }
    },
    getNewArrivalTemplates: async (): Promise<ApiResponse<ProductTemplate[]>> => {
      try {
        const response = await api.get('/templates/new-arrivals');
        return response.data;
      } catch (error) {
        console.error('获取新品模板失败:', error);
        throw error;
      }
    },
    getSaleTemplates: async (): Promise<ApiResponse<ProductTemplate[]>> => {
      try {
        const response = await api.get('/templates/sale');
        return response.data;
      } catch (error) {
        console.error('获取特价模板失败:', error);
        throw error;
      }
    },
    getSettings: async (): Promise<ApiResponse<any>> => {
      try {
        const response = await api.get('/homepage/settings');
        return response.data;
      } catch (error) {
        console.error('获取首页设置失败:', error);
        throw error;
      }
    },
    getBanners: async (): Promise<ApiResponse<any>> => {
      try {
        const response = await api.get('/homepage/banners');
        return response.data;
      } catch (error) {
        console.error('获取轮播图数据失败:', error);
        throw error;
      }
    }
  },
  
  templates: {
    getAll: async (params?: any): Promise<TemplateApiResponse> => {
      try {
        const response = await api.get('/templates', { params });
        return response.data;
      } catch (error) {
        console.error('获取所有模板失败:', error);
        throw error;
      }
    },
    getById: async (id: number): Promise<ApiResponse<ProductTemplate>> => {
      try {
        const response = await api.get(`/templates/${id}`);
        return response.data;
      } catch (error) {
        console.error(`获取模板#${id}失败:`, error);
        throw error;
      }
    },
    getRelated: async (id: number): Promise<ApiResponse<ProductTemplate[]>> => {
      try {
        const response = await api.get(`/templates/${id}/related`);
        return response.data;
      } catch (error) {
        console.error(`获取相关模板失败:`, error);
        throw error;
      }
    }
  },
  
  products: {
    getAll: async (params?: any): Promise<any> => {
      try {
        const response = await api.get('/products', { params });
        return response.data;
      } catch (error) {
        console.error('获取所有产品失败:', error);
        throw error;
      }
    },
    getFeatured: async (params?: any): Promise<any> => {
      try {
        const response = await api.get('/products/featured', { params });
        return response.data;
      } catch (error) {
        console.error('获取特色产品失败:', error);
        throw error;
      }
    },
    getNewArrivals: async (params?: any): Promise<any> => {
      try {
        const response = await api.get('/products/new-arrivals', { params });
        return response.data;
      } catch (error) {
        console.error('获取新品失败:', error);
        throw error;
      }
    },
    getList: async (params?: any): Promise<any> => {
      try {
        const response = await api.get('/products/list', { params });
        return response.data;
      } catch (error) {
        console.error('获取产品列表失败:', error);
        throw error;
      }
    },
    getPopular: async (limit: number = 8): Promise<any> => {
      try {
        const response = await api.get('/products/popular', { params: { limit } });
        return response.data;
      } catch (error) {
        console.error('获取热门产品失败:', error);
        throw error;
      }
    },
    getRecommended: async (params?: any): Promise<any> => {
      try {
        const response = await api.get('/products/recommended', { params });
        return response.data;
      } catch (error) {
        console.error('获取推荐产品失败:', error);
        throw error;
      }
    }
  },
  
  categories: {
    getAll: async (): Promise<ApiResponse<any[]>> => {
      try {
        const response = await api.get('/categories');
        return response.data;
      } catch (error) {
        console.error('获取所有分类失败:', error);
        throw error;
      }
    }
  },
  
  batch: {
    getProductsData: async (ids: number[]): Promise<ProductTemplate[]> => {
      if (!ids || ids.length === 0) return [];
      
      try {
        const chunks = [];
        for (let i = 0; i < ids.length; i += 10) {
          chunks.push(ids.slice(i, i + 10));
        }
        
        const promises = chunks.map(chunk => 
          api.get('/templates/batch', { params: { ids: chunk.join(',') } })
        );
        
        const responses = await Promise.all(promises);
        
        const templates: ProductTemplate[] = [];
        responses.forEach((response: import('axios').AxiosResponse) => {
          if (response.data?.data?.templates) {
            templates.push(...response.data.data.templates);
          }
        });
        
        return templates;
      } catch (error) {
        console.error('批量获取产品数据失败:', error);
        throw error;
      }
    },
    
    checkProductsStock: async (ids: number[]): Promise<Record<number, boolean>> => {
      if (!ids || ids.length === 0) return {};
      
      try {
        const response = await api.get('/products/stock/batch', { 
          params: { ids: ids.join(',') } 
        });
        
        return response.data?.data || {};
      } catch (error) {
        console.error('批量检查产品库存失败:', error);
        throw error;
      }
    }
  },
  
  cart: {
    getCart: async (): Promise<any> => {
      try {
        const response = await api.get('/cart');
        return response.data;
      } catch (error) {
        console.error('获取购物车失败:', error);
        throw error;
      }
    }
  }
};

export { apiService };
export default api; 
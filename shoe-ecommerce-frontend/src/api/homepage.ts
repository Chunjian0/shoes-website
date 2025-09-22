import { api } from '../services/api';
import { HomepageSettings, LayoutTemplate } from '../types/homepage';
import { HomepageApiResponse, TemplateApiResponse, ProductTemplate } from '../types/apiTypes';
import { logger } from '../utils/logger';

// API 基础URL
const API_BASE_URL = '/api';

/**
 * Fetches the consolidated homepage data from the backend API.
 * Uses the configured api instance to ensure interceptors (CSRF, auth, performance) are applied.
 */
export const fetchHomepageData = async (): Promise<HomepageApiResponse | null> => {
  try {
    logger.info('[API Call] Fetching /api/homepage/data');
    // Use the configured api instance which returns response.data directly
    const response = await api.get<HomepageApiResponse>('/homepage/data');
    
    // The api instance interceptor handles response.data extraction
    // and basic success checks might already be done. 
    // We might just return the data directly if the structure is guaranteed by apiService.
    // Assuming api.get returns the data payload directly on success.
    if (response && response.data) { // Check if response and response.data are not null/undefined
      logger.info('[API Success] Received data from /api/homepage/data');
      return response.data; // 返回response.data而不是response
    } else {
      // This case might occur if the interceptor handled an error and returned null/undefined
      logger.warn('[API Warning] /api/homepage/data call returned null/undefined response.');
      return null;
    }

  } catch (error) {
    // Error handling might be partially done by interceptors, 
    // but log specific context here.
    logger.error('Error fetching homepage data:', error);
    // Consider re-throwing or returning null based on desired app behavior
    return null; 
  }
};

/**
 * 获取首页设置
 */
export const fetchHomepageSettings = async (): Promise<HomepageSettings> => {
  try {
    const response = await api.get(`${API_BASE_URL}/homepage/settings`);
    return response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching homepage settings:', error);
    throw error;
  }
};

/**
 * 保存首页设置
 */
export const saveHomepageSettings = async (settings: HomepageSettings): Promise<HomepageSettings> => {
  try {
    const response = await api.post(`${API_BASE_URL}/homepage/settings`, settings);
    return response.data;
  } catch (error) {
    console.error('Error saving homepage settings:', error);
    throw error;
  }
};

/**
 * 获取特色产品模板
 */
export const fetchFeaturedTemplates = async (page: number = 1, perPage: number = 10): Promise<TemplateApiResponse> => {
  try {
    const response = await api.get<TemplateApiResponse>(`${API_BASE_URL}/homepage/featured-templates`, {
      params: { page, per_page: perPage }
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching featured templates:', error);
    throw error;
  }
};

/**
 * 获取新品产品模板
 */
export const fetchNewArrivalTemplates = async (page: number = 1, perPage: number = 10): Promise<TemplateApiResponse> => {
  try {
    const response = await api.get<TemplateApiResponse>(`${API_BASE_URL}/homepage/new-arrival-templates`, {
      params: { page, per_page: perPage }
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching new arrival templates:', error);
    throw error;
  }
};

/**
 * 获取促销产品模板
 */
export const fetchSaleTemplates = async (page: number = 1, perPage: number = 10): Promise<TemplateApiResponse> => {
  try {
    const response = await api.get<TemplateApiResponse>(`${API_BASE_URL}/homepage/sale-templates`, {
      params: { page, per_page: perPage }
    });
    return response.data;
  } catch (error) {
    console.error('Error fetching sale templates:', error);
    throw error;
  }
};

/**
 * 获取首页轮播图
 */
export const fetchBanners = async () => {
  try {
    const response = await api.get(`${API_BASE_URL}/homepage/banners`);
    return response.data.data?.banners || [];
  } catch (error) {
    console.error('Error fetching banners:', error);
    throw error;
  }
};

/**
 * 获取不同类型的产品列表
 * @deprecated 使用新的模板API
 */
export const fetchProducts = async (type: 'featured' | 'new' | 'sale', limit: number = 8) => {
  try {
    const response = await api.get(`${API_BASE_URL}/products`, {
      params: { type, limit }
    });
    return response.data;
  } catch (error) {
    console.error(`Error fetching ${type} products:`, error);
    throw error;
  }
};

/**
 * 获取产品分类列表
 */
export const fetchCategories = async () => {
  try {
    const response = await api.get(`${API_BASE_URL}/product-categories`);
    return response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
    throw error;
  }
};

/**
 * 更新用户模板偏好
 */
export const updateUserTemplatePreference = async (template: LayoutTemplate): Promise<void> => {
  try {
    await api.post(`${API_BASE_URL}/user/template-preference`, { template });
    return;
  } catch (error) {
    console.error('Error updating template preference:', error);
    throw error;
  }
}; 
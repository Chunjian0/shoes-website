import { HomepageSettings, BannerSettings, SettingsUpdateEvent, SettingsUpdateEventType, CarouselSettings, ResponsiveSettings, ProductItem, FeaturedProductsSettings, NewProductsSettings, SaleProductsSettings, LayoutTemplate } from '../types/homepage';
import { api, apiService } from './api';
import axios from 'axios';
import { logger } from '../utils/logger';
import { ProductTemplate } from '../types/apiTypes';
import { formatApiDataToSettings, getDefaultSettings } from '../formatters/homepageFormatter';
import { fetchHomepageData } from '../api/homepage';
import { HomepageApiResponse, TemplateApiResponse } from '../types/apiTypes';

// Redis缓存键名
const REDIS_KEYS = {
  HOMEPAGE_SETTINGS: 'homepage:settings',
  BANNER_SETTINGS: 'homepage:banner',
  OFFER_SETTINGS: 'homepage:offer',
  FEATURED_PRODUCTS_SETTINGS: 'homepage:featured_products',
  CAROUSEL_SETTINGS: 'homepage:carousel',
  RESPONSIVE_SETTINGS: 'homepage:responsive',
  HOMEPAGE_API_DATA: 'homepage:api_data',
  HOMEPAGE_TEMPLATES: 'homepage:templates',
};

// 缓存过期时间
const CACHE_EXPIRY = {
  HOMEPAGE_SETTINGS: 5 * 60 * 1000, // 5分钟
  POLLING_INTERVAL: 30 * 1000, // 30秒
  DEFAULT: 3600, // 1小时
  SETTINGS: 86400, // 24小时
  HOMEPAGE_DATA: 5 * 60 * 1000, // 5分钟 (Corrected value: 300000)
  TEMPLATES: 600 // 10分钟
};

// 默认轮播设置
const DEFAULT_CAROUSEL_SETTINGS: CarouselSettings = {
  autoplay: true,
  delay: 5000,
  transition: 'slide',
  showNavigation: true,
  showIndicators: true
};

// 默认响应式设置
const DEFAULT_RESPONSIVE_SETTINGS: ResponsiveSettings = {
  mobile_layout: 'compact',
  tablet_columns: 2,
  desktop_columns: 4,
  show_categories_on_mobile: true,
  enable_touch_gestures: true,
  breakpoints: {
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280
  }
};

// 初始空设置
const EMPTY_SETTINGS: HomepageSettings = {
  active_template: LayoutTemplate.MODERN,
  site_name: 'Optic System',
  carousel: DEFAULT_CAROUSEL_SETTINGS,
  banners: [{
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
    imageUrl: ''
  }],
  featuredProducts: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
  },
  newProducts: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
  },
  saleProducts: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
  },
  offer: {
    title: '',
    subtitle: '',
    buttonText: '',
    buttonLink: '',
    imageUrl: '',
  },
  featured_products_count: 8,
  new_products_count: 8,
  sale_products_count: 8,
  showCategories: true,
  show_promotion: true,
  show_brands: true,
  templates: {
    featured: [],
    newArrival: [],
    sale: []
  }
};

// 添加Banner类型定义
interface Banner {
  id?: number;
  title: string;
  subtitle: string;
  buttonText?: string;
  buttonLink?: string;
  imageUrl?: string;
  button_text?: string;
  button_link?: string;
  image_url?: string;
  is_active?: boolean;
  order?: number;
}

// Define a specific type for the cleaned-up homepage API service methods
interface HomepageApiService {
  getData: () => Promise<any>; // Assuming return type is handled by interceptor/caller
  getBanners: () => Promise<any>;
  getFeaturedTemplates: () => Promise<any>;
  getNewArrivalTemplates: () => Promise<any>;
  getSaleTemplates: () => Promise<any>;
}

/**
 * 获取响应式设置 (Removed Redis)
 */
export const getResponsiveSettings = async (): Promise<ResponsiveSettings> => {
  try {
    // Removed cache check
    logger.info('[HomepageService] Returning default responsive settings');
    return DEFAULT_RESPONSIVE_SETTINGS;
  } catch (error) {
    logger.error('[HomepageService] Error in getResponsiveSettings (should be minimal now)', error);
    return DEFAULT_RESPONSIVE_SETTINGS; // Still return default on error
  }
};

/**
 * 获取首页所有数据 (Refactored, Removed Redis)
 * Fetches data primarily from the single /api/homepage/data endpoint
 * @param forceRefresh 是否强制刷新 (Parameter might be redundant now)
 * @returns 格式化的首页数据
 */
export const getHomePageData = async (forceRefresh: boolean = false): Promise<{
  settings: HomepageSettings,
  featuredProducts: ProductItem[],
  newProducts: ProductItem[],
  saleProducts: ProductItem[],
  templates: {
    featured: ProductTemplate[],
    newArrival: ProductTemplate[],
    sale: ProductTemplate[]
  }
}> => {
  // Removed cacheKey
  let rawApiData: any | null = null;
  let dataToProcess: HomepageApiResponse | null = null;

  try {
    // 1. Always Fetch from API (Removed Cache Check)
    logger.info(`获取首页数据 (/api/homepage/data)${forceRefresh ? ' (强制刷新请求)' : ''}`);
    rawApiData = await fetchHomepageData(); 
    
    logger.debug('[getHomePageData] Raw data received from fetchHomepageData:', rawApiData);
      
    // Check the structure of rawApiData directly
    if (rawApiData?.success && rawApiData.data) { 
      logger.info('从API获取到首页数据');
      dataToProcess = rawApiData; 
      // Removed redisService.set call
    } else {
      logger.error('[getHomePageData] API data structure invalid or success=false:', rawApiData);
      throw new Error('Failed to fetch homepage data from API or API returned error');
    }

    // 3. Format the data
    if (!dataToProcess || !dataToProcess.data) { 
      logger.error('[getHomePageData] Invalid data structure before formatting:', dataToProcess);
      throw new Error('Invalid homepage data received');
    }
    
    logger.info('格式化首页数据...');
    const settings = formatApiDataToSettings(dataToProcess.data);

    // Extract product items 
    const extractProductsFromTemplates = (templates: ProductTemplate[]): ProductItem[] => {
        // Placeholder: Needs proper conversion logic if ProductItem is different from ProductTemplate parts
        return templates as any[]; // TEMPORARY - Needs implementation
    };

    const featuredProducts = extractProductsFromTemplates(dataToProcess.data.featured_templates || []);
    const newProducts = extractProductsFromTemplates(dataToProcess.data.new_arrival_templates || []);
    const saleProducts = extractProductsFromTemplates(dataToProcess.data.sale_templates || []);

    logger.info('首页数据格式化完成');
    logger.debug('[getHomePageData] Returning formatted data:', {
      settings: settings,
      featuredCount: featuredProducts.length,
      newCount: newProducts.length,
      saleCount: saleProducts.length,
      featuredTemplatesCount: dataToProcess.data.featured_templates?.length ?? 0,
      newArrivalTemplatesCount: dataToProcess.data.new_arrival_templates?.length ?? 0,
      saleTemplatesCount: dataToProcess.data.sale_templates?.length ?? 0
    });

    return {
      settings,
      featuredProducts,
      newProducts,
      saleProducts,
      templates: {
        featured: dataToProcess.data.featured_templates || [],
        newArrival: dataToProcess.data.new_arrival_templates || [],
        sale: dataToProcess.data.sale_templates || []
      }
    };

  } catch (error) {
    logger.error('获取或处理首页数据失败:', error);
    return {
      settings: getDefaultSettings(), // Return default settings on error
      featuredProducts: [],
      newProducts: [],
      saleProducts: [],
      templates: { featured: [], newArrival: [], sale: [] }
    };
  }
};

// 将所有函数整合到一个默认导出对象中
const homepageService = {
  getResponsiveSettings,
  getHomePageData,
  // Removed REDIS_KEYS
  formatHomepageSettings: formatApiDataToSettings
};

export default homepageService; 
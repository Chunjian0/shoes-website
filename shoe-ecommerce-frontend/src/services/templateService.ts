import { api } from './api';
import { ProductTemplate } from '../types/apiTypes';
import { logger } from '../utils/logger'; // Use the shared logger

/**
 * 获取产品模板详情
 * @param id 模板ID
 * @returns 产品模板详情
 */
export const getTemplateDetails = async (id: number): Promise<ProductTemplate> => {
  try {
    logger.info(`[TemplateService] Starting to fetch template #${id} details (no cache)`);
    
    logger.info(`[TemplateService] Fetching template #${id} from API...`);
    
    const apiPaths = [
      `/product-templates/${id}`,
      `/homepage/template/${id}`,
      `/templates/${id}`
    ];
    
    let response = null;
    let errorDetails = [];
    
    for (const path of apiPaths) {
      try {
        logger.info(`[TemplateService] Trying API path: ${path}`);
        response = await api.get<{success: boolean, data: ProductTemplate}>(path);
        
        if (response && response.data?.success && response.data.data) {
          logger.info(`[TemplateService] Successfully fetched template data from API path ${path}`);
          
          if (response.data.data.linked_products && response.data.data.linked_products.length > 0) {
            logger.info(`[TemplateService] Enhancing ${response.data.data.linked_products.length} linked products`);
            const enhancedProducts = response.data.data.linked_products.map((product: any) => {
              return {
                ...product,
                stock_quantity: product.stock_quantity || 0,
                discount_percentage: product.discount_percentage || 0,
                price: product.price || 0,
                original_price: product.original_price || product.price || 0,
                parameters: product.parameters || {}
              };
            });
            logger.debug('[TemplateService] Enhanced linked products data:', enhancedProducts);
            response.data.data.linked_products = enhancedProducts;
          }
          
          return response.data.data;
        }
      } catch (err: any) {
        const status = err.response?.status;
        const message = err.message || 'Unknown error';
        logger.error(`[TemplateService] API path ${path} request failed: ${status} - ${message}`);
        errorDetails.push({ path, status, message });
      }
    }
    
    logger.info('[TemplateService] API attempts failed, trying to find in homepage data...');
    try {
      const homepageResponse = await api.get<{ success: boolean; data: { featured_templates?: ProductTemplate[], new_arrival_templates?: ProductTemplate[], sale_templates?: ProductTemplate[] } }>('/homepage/data');
      const homepageData = homepageResponse.data;
      logger.debug('[TemplateService] Successfully got homepage data', homepageData);
      
      if (homepageData?.success && homepageData?.data) {
        const allTemplates = [
          ...(homepageData.data.featured_templates || []),
          ...(homepageData.data.new_arrival_templates || []),
          ...(homepageData.data.sale_templates || [])
        ];
        const foundTemplate = allTemplates.find((template: any) => template.id === id);
        if (foundTemplate) {
          logger.info(`[TemplateService] Found template #${id} in aggregated homepage data`);
          return foundTemplate;
        }
      }
      
      logger.info('[TemplateService] Template not found in any API source.');
    } catch (err) {
      logger.error('[TemplateService] Failed to fetch or parse homepage template data', err);
    }
    
    logger.warn('[TemplateService] All API attempts failed, returning mock data as fallback');
    
    const mockTemplate: ProductTemplate = {
      id: id,
      title: `Template #${id}`,
      description: `<p>This is a product template description...</p>`,
      images: [
        { url: 'https://via.placeholder.com/600x400/cccccc/969696?text=Product+Image+1', thumbnail: 'https://via.placeholder.com/100x100/cccccc/969696?text=Thumb+1' },
      ],
      parameters: [
        { name: 'Color', values: ['Red', 'Blue', 'Black'] },
        { name: 'Size', values: ['M', 'L', 'XL'] }
      ],
      linked_products: [
        { id: 101, name: "Mock Product 1", sku: "MOCK-001", price: 99.99, stock_quantity: 10, images: [], discount_percentage: 0 },
      ],
      related_products: [],
      name: `Template #${id}`,
      is_featured: false,
      is_new_arrival: false,
      is_sale: false,
      category: { id: 1, name: "Mock Category" },
      created_at: new Date().toISOString(),
    };
    return mockTemplate;
  } catch (error) {
    logger.error(`[TemplateService] Failed to get template #${id} details:`, error);
    throw new Error('Failed to load template data');
  }
};

/**
 * 获取相关产品模板
 * @param templateId 当前模板ID
 * @param categoryName 可选的分类名
 * @returns 相关产品模板数组
 */
export const getRelatedTemplates = async (templateId: string | number, categoryName?: string): Promise<ProductTemplate[]> => {
  try {
    logger.info(`[TemplateService] Fetching related templates for #${templateId} from API (no cache)...`);
    
    const apiPath = `/templates/${templateId}/related`;
    
    const response = await api.get<{success: boolean, data: ProductTemplate[]}>(apiPath);
    
    if (response && response.data?.success && Array.isArray(response.data.data)) {
      logger.info(`[TemplateService] Successfully fetched ${response.data.data.length} related templates for #${templateId}`);
      return response.data.data;
    } else {
      logger.warn(`[TemplateService] No related templates found or invalid response structure for #${templateId}`, response?.data);
      return [];
    }
  } catch (error: any) {
    const status = error.response?.status;
    const message = error.message || 'Unknown error';
    logger.error(`[TemplateService] Failed to fetch related templates for #${templateId}: ${status} - ${message}`, error);
    return [];
  }
};

// 模板服务
const templateService = {
  getTemplateDetails,
  getRelatedTemplates
};

export default templateService; 
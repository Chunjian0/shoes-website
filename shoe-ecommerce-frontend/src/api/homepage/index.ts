/**
 * 首页相关API文档
 * 
 * 本文档详细介绍了与首页相关的所有API接口，包括首页数据、设置和轮播图等
 * 
 * API基础路径：/api/homepage
 * 
 * 主要API端点:
 * 
 * GET /homepage/data
 * - 描述: 获取首页所有数据，包括特色模板、新品模板、促销模板、轮播图和设置
 * - 响应格式:
 *   {
 *     "success": true,
 *     "data": {
 *       "featured_templates": [ ... 特色产品模板数组 ... ],
 *       "new_arrival_templates": [ ... 新品模板数组 ... ],
 *       "sale_templates": [ ... 促销模板数组 ... ],
 *       "banners": [ ... 轮播图数组 ... ],
 *       "settings": { ... 首页设置对象 ... }
 *     }
 *   }
 * 
 * GET /homepage/settings
 * - 描述: 获取首页设置
 * - 响应格式:
 *   {
 *     "success": true,
 *     "data": {
 *       "site_title": "Optic System",
 *       "site_description": "Your one-stop shop for quality optical products",
 *       "layout": "standard",
 *       "show_promotion": true,
 *       "show_brands": true,
 *       ...其他设置...
 *     }
 *   }
 * 
 * GET /homepage/featured-templates
 * - 描述: 获取特色产品模板
 * - 响应格式:
 *   {
 *     "success": true,
 *     "data": [ ... 特色产品模板数组 ... ]
 *   }
 * 
 * GET /homepage/new-arrival-templates
 * - 描述: 获取新品产品模板
 * - 响应格式:
 *   {
 *     "success": true,
 *     "data": [ ... 新品产品模板数组 ... ]
 *   }
 * 
 * GET /homepage/sale-templates
 * - 描述: 获取促销产品模板
 * - 响应格式:
 *   {
 *     "success": true,
 *     "data": [ ... 促销产品模板数组 ... ]
 *   }
 * 
 * GET /homepage/banners
 * - 描述: 获取轮播图列表
 * - 响应格式:
 *   {
 *     "success": true,
 *     "data": [ ... 轮播图数组 ... ]
 *   }
 * 
 * 产品模板对象结构:
 * {
 *   "id": 1,
 *   "name": "Classic Frames",
 *   "description": "Traditional eyeglass frames with timeless design",
 *   "category": {
 *     "id": 1,
 *     "name": "Eyeglasses"
 *   },
 *   "parameters": [ ... 参数数组 ... ],
 *   "images": [ ... 图片数组 ... ],
 *   "is_active": true,
 *   "is_featured": true,
 *   "is_new_arrival": false,
 *   "is_sale": false,
 *   "linked_products": [ ... 关联产品数组 ... ]
 * }
 * 
 * 轮播图对象结构:
 * {
 *   "id": 1,
 *   "title": "Summer Collection",
 *   "subtitle": "Discover our new summer styles",
 *   "button_text": "Shop Now",
 *   "button_link": "/products?category=sunglasses",
 *   "image_url": "/storage/banners/summer_collection.jpg",
 *   "order": 1,
 *   "is_active": true
 * }
 */

/**
 * 首页API
 * 
 * 可用API端点：
 * - GET /homepage/data - 获取所有首页数据
 * - GET /homepage/settings - 获取首页设置
 * - GET /homepage/banners - 获取横幅数据
 * - GET /homepage/featured-templates - 获取精选模板
 * - GET /homepage/new-arrival-templates - 获取新品模板
 * - GET /homepage/sale-templates - 获取特价模板
 * 
 * 响应数据结构：
 * {
 *   success: boolean,
 *   data: {
 *     settings: {...},
 *     banners: [...],
 *     featured_templates: [...],
 *     new_arrival_templates: [...],
 *     sale_templates: [...]
 *   }
 * }
 */

/**
 * 工具函数 - 格式化API返回数据
 */
export const formatApiResponse = (data: any) => {
  // 验证数据结构
  if (!data || typeof data !== 'object') {
    console.error('API响应格式无效', data);
    return null;
  }
  
  // 确保数据有正确的结构
  if (!data.success && !data.data) {
    // 尝试兼容不同的API响应格式
    if (data.status === 'success' && data.templates) {
      return {
        success: true,
        data: data.templates
      };
    }
    
    console.error('API响应缺少必要字段', data);
    return null;
  }
  
  return data;
}; 
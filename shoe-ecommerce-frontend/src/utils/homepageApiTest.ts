/**
 * 首页API测试工具
 * 用于验证API响应格式和解析数据
 */
import axios from 'axios';
import { logger } from './logger';
import { HomepageSettings, LayoutTemplate } from '../types/homepage';

/**
 * 测试获取首页数据API
 */
export const testHomepageDataApi = async () => {
  try {
    logger.info('测试首页数据API...');
    const response = await axios.get('/api/homepage/data');
    
    if (response.data && response.data.success) {
      logger.info('API响应成功', response.data);
      
      // 提取设置数据
      const settings = response.data.data?.settings || {};
      logger.info('首页设置数据:', settings);
      
      // 提取模板数据
      const featured = response.data.data?.featured_templates || [];
      const newArrivals = response.data.data?.new_arrival_templates || [];
      const sale = response.data.data?.sale_templates || [];
      
      logger.info(`获取到 ${featured.length} 个精选模板, ${newArrivals.length} 个新品模板, ${sale.length} 个特价模板`);
      
      // 检查布局设置
      const layout = settings.layout || 'standard';
      logger.info(`当前布局设置: ${layout}`);
      
      // 根据布局设置映射到LayoutTemplate枚举
      let templateType = LayoutTemplate.MODERN;
      switch (layout.toLowerCase()) {
        case 'classic':
          templateType = LayoutTemplate.CLASSIC;
          break;
        case 'minimal':
          templateType = LayoutTemplate.MINIMAL;
          break;
        case 'bold':
          templateType = LayoutTemplate.BOLD;
          break;
        case 'modern':
        case 'standard':
        default:
          templateType = LayoutTemplate.MODERN;
      }
      
      logger.info(`映射的模板类型: ${templateType}`);
      
      // 转换为前端使用的格式
      const homepageSettings: HomepageSettings = {
        active_template: templateType,
        site_name: settings.site_title || 'YCE Shoes',
        banners: [],
        carousel: {
          autoplay: true,
          delay: 5000,
          transition: 'slide',
          showNavigation: true,
          showIndicators: true
        },
        featuredProducts: {
          title: settings.featured_products_title || 'Featured Products',
          subtitle: settings.featured_products_subtitle || 'Our carefully selected premium products',
          buttonText: settings.featured_products_button_text || 'View All',
          buttonLink: settings.featured_products_button_link || '/products?featured=true'
        },
        newProducts: {
          title: settings.new_products_title || 'New Arrivals',
          subtitle: settings.new_products_subtitle || 'Check out our latest products',
          buttonText: settings.new_products_button_text || 'View All New',
          buttonLink: settings.new_products_button_link || '/products?new=true'
        },
        saleProducts: {
          title: settings.sale_products_title || 'On Sale',
          subtitle: settings.sale_products_subtitle || 'Great deals on quality products',
          buttonText: settings.sale_products_button_text || 'View All Sale',
          buttonLink: settings.sale_products_button_link || '/products?sale=true'
        },
        show_promotion: settings.show_promotion !== undefined ? !!settings.show_promotion : true,
        show_brands: settings.show_brands !== undefined ? !!settings.show_brands : true,
        featured_products_count: 8,
        new_products_count: 8,
        sale_products_count: 8,
        templates: {
          featured: featured,
          newArrival: newArrivals,
          sale: sale
        }
      };
      
      logger.info('转换后的首页设置:', homepageSettings);
      return homepageSettings;
    } else {
      logger.error('API响应失败或格式不正确', response.data);
      return null;
    }
  } catch (error) {
    logger.error('测试首页API时出错:', error);
    return null;
  }
};

/**
 * 运行所有测试
 */
export const runAllTests = async () => {
  logger.info('开始测试首页API...');
  await testHomepageDataApi();
  logger.info('首页API测试完成');
};

// 导出测试函数
export default {
  testHomepageDataApi,
  runAllTests
}; 
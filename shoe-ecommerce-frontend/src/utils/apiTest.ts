/**
 * API测试工具
 * 用于验证API路径和响应格式
 */
import axios from 'axios';
import { logger } from './logger';

/**
 * 测试首页API
 */
export const testHomepageAPI = async () => {
  try {
    logger.info('测试首页API路径...');
    
    // 测试主页面API
    try {
      const response = await axios.get('/api/homepage');
      logger.info('首页API响应:', response.data);
    } catch (error) {
      logger.error('首页API请求失败:', error);
    }
    
    // 测试首页设置API
    try {
      const response = await axios.get('/api/homepage/settings');
      logger.info('首页设置API响应:', response.data);
    } catch (error) {
      logger.error('首页设置API请求失败:', error);
    }
    
    // 测试轮播设置API
    try {
      const response = await axios.get('/api/homepage/settings');
      logger.info('轮播设置API响应:', response.data);
    } catch (error) {
      logger.error('轮播设置API请求失败:', error);
    }
    
    // 测试模板API
    try {
      const response = await axios.get('/api/homepage/templates');
      logger.info('模板API响应:', response.data);
    } catch (error) {
      logger.error('模板API请求失败:', error);
    }
    
    logger.info('API测试完成');
  } catch (error) {
    logger.error('API测试过程中发生错误:', error);
  }
};

/**
 * 获取当前后端API路径
 */
export const getAPIBasePath = () => {
  return axios.defaults.baseURL || window.location.origin;
};

// 导出测试工具
export default {
  testHomepageAPI,
  getAPIBasePath
}; 
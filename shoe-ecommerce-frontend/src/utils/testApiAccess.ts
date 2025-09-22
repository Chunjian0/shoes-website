/**
 * API访问测试工具
 * 用于检测和诊断API连接问题
 */
import axios from 'axios';
import { logger } from './logger';

// 测试配置
const TEST_CONFIG = {
  // 超时设置（毫秒）
  timeout: 30000,
  // 重试次数
  retries: 2,
  // 延迟时间（毫秒）
  retryDelay: 2000,
  // 测试的API端点
  endpoints: [
    '/api/homepage/data',
    '/api/homepage/settings',
    '/api/homepage/banners',
    '/api/homepage/featured-templates',
    '/api/homepage/new-arrival-templates',
    '/api/homepage/sale-templates'
  ]
};

/**
 * 测试单个API端点
 */
export const testEndpoint = async (endpoint: string): Promise<{
  success: boolean;
  endpoint: string;
  responseTime: number;
  status?: number;
  error?: string;
  data?: any;
}> => {
  const startTime = Date.now();
  let currentRetry = 0;
  let lastError: any = null;
  
  // 重试逻辑
  while (currentRetry <= TEST_CONFIG.retries) {
    try {
      logger.info(`[ApiTest] 测试端点: ${endpoint} (尝试 ${currentRetry + 1}/${TEST_CONFIG.retries + 1})`);
      
      // 发送请求
      const response = await axios.get(endpoint, {
        timeout: TEST_CONFIG.timeout,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Api-Test': 'true'
        },
        withCredentials: true
      });
      
      const responseTime = Date.now() - startTime;
      logger.info(`[ApiTest] 端点 ${endpoint} 响应成功 (${responseTime}ms)`, {
        status: response.status,
      });
      
      return {
        success: true,
        endpoint,
        responseTime,
        status: response.status,
        data: response.data
      };
    } catch (error) {
      lastError = error;
      const responseTime = Date.now() - startTime;
      logger.error(`[ApiTest] 端点 ${endpoint} 失败 (${responseTime}ms)`, error);
      
      // 检查是否需要重试
      if (currentRetry < TEST_CONFIG.retries) {
        logger.info(`[ApiTest] 将在 ${TEST_CONFIG.retryDelay}ms 后重试...`);
        await new Promise(resolve => setTimeout(resolve, TEST_CONFIG.retryDelay));
        currentRetry++;
      } else {
        break;
      }
    }
  }
  
  // 所有重试都失败了
  const totalTime = Date.now() - startTime;
  let errorMessage = '未知错误';
  let status = 0;
  
  if (axios.isAxiosError(lastError)) {
    errorMessage = lastError.message;
    if (lastError.response) {
      status = lastError.response.status;
    } else if (lastError.code === 'ECONNABORTED') {
      errorMessage = '请求超时';
    } else if (!lastError.response) {
      errorMessage = '网络错误或服务器未响应';
    }
  }
  
  return {
    success: false,
    endpoint,
    responseTime: totalTime,
    status,
    error: errorMessage
  };
};

/**
 * 测试所有API端点
 */
export const testAllEndpoints = async (): Promise<{
  results: Array<{
    success: boolean;
    endpoint: string;
    responseTime: number;
    status?: number;
    error?: string;
  }>;
  summary: {
    totalEndpoints: number;
    successCount: number;
    failCount: number;
    avgResponseTime: number;
    minResponseTime: number;
    maxResponseTime: number;
  };
}> => {
  logger.info('[ApiTest] 开始测试所有API端点');
  
  const results = [];
  let totalResponseTime = 0;
  let minResponseTime = Number.MAX_VALUE;
  let maxResponseTime = 0;
  let successCount = 0;
  
  // 测试所有端点
  for (const endpoint of TEST_CONFIG.endpoints) {
    const result = await testEndpoint(endpoint);
    results.push(result);
    
    // 更新统计数据
    totalResponseTime += result.responseTime;
    minResponseTime = Math.min(minResponseTime, result.responseTime);
    maxResponseTime = Math.max(maxResponseTime, result.responseTime);
    
    if (result.success) {
      successCount++;
    }
  }
  
  // 计算摘要
  const summary = {
    totalEndpoints: TEST_CONFIG.endpoints.length,
    successCount,
    failCount: TEST_CONFIG.endpoints.length - successCount,
    avgResponseTime: totalResponseTime / TEST_CONFIG.endpoints.length,
    minResponseTime,
    maxResponseTime
  };
  
  logger.info('[ApiTest] API测试完成', summary);
  return { results, summary };
};

/**
 * 生成诊断报告
 */
export const generateApiDiagnosticReport = async (): Promise<string> => {
  try {
    logger.info('[ApiTest] 生成API诊断报告');
    const { results, summary } = await testAllEndpoints();
    
    // 构建报告
    let report = 'API 诊断报告\n';
    report += '===================\n\n';
    report += `测试时间: ${new Date().toISOString()}\n`;
    report += `测试端点总数: ${summary.totalEndpoints}\n`;
    report += `成功: ${summary.successCount}\n`;
    report += `失败: ${summary.failCount}\n`;
    report += `平均响应时间: ${summary.avgResponseTime.toFixed(2)}ms\n`;
    report += `最小响应时间: ${summary.minResponseTime}ms\n`;
    report += `最大响应时间: ${summary.maxResponseTime}ms\n\n`;
    
    // 详细结果
    report += '详细测试结果\n';
    report += '-----------------\n\n';
    
    results.forEach(result => {
      report += `端点: ${result.endpoint}\n`;
      report += `状态: ${result.success ? '成功' : '失败'}\n`;
      report += `响应时间: ${result.responseTime}ms\n`;
      
      if (result.success) {
        report += `HTTP状态: ${result.status}\n`;
      } else {
        report += `错误: ${result.error}\n`;
        if (result.status) {
          report += `HTTP状态: ${result.status}\n`;
        }
      }
      
      report += '\n';
    });
    
    // 网络环境诊断
    report += '网络环境诊断\n';
    report += '-----------------\n\n';
    
    // API基础URL
    const baseUrl = axios.defaults.baseURL || window.location.origin;
    report += `API基础URL: ${baseUrl}\n`;
    
    // 浏览器信息
    report += `浏览器: ${navigator.userAgent}\n`;
    
    // 连接类型 (如果可用)
    if ('connection' in navigator && (navigator as any).connection) {
      const conn = (navigator as any).connection;
      report += `连接类型: ${conn.effectiveType}\n`;
      report += `下行速度: ${conn.downlink}Mbps\n`;
      report += `RTT: ${conn.rtt}ms\n`;
    }
    
    logger.info('[ApiTest] 诊断报告生成完成');
    return report;
  } catch (error) {
    logger.error('[ApiTest] 生成诊断报告失败', error);
    return `错误: 无法生成诊断报告 - ${error}`;
  }
};

// 导出测试工具
export default {
  testEndpoint,
  testAllEndpoints,
  generateApiDiagnosticReport
}; 
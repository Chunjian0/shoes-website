/**
 * 性能优化配置和监控工具
 * 提供全局性能设置、网络优化和监控功能
 */

import { setApiDebugLevel } from '../services/api';

// 性能配置默认值
const DEFAULT_CONFIG = {
  // 调试级别: 0=无调试, 1=仅错误, 2=详细
  debugLevel: process.env.NODE_ENV === 'development' ? 1 : 0,
  
  // 是否启用性能监控
  enableMonitoring: false,
  
  // 页面加载相关
  lazyLoadImages: true,        // 延迟加载图片
  lazyLoadComponents: true,    // 延迟加载组件
  prefetchAssets: true,        // 预加载资源
  compressionLevel: 'auto',    // 图片压缩级别: 'none', 'low', 'medium', 'high', 'auto'
  
  // 网络相关
  optimizeNetworkCalls: true,  // 优化网络请求
  cacheApiResponses: true,     // 缓存API响应
  offlineSupport: false,       // 离线支持
  
  // 节流和防抖
  scrollThrottleMs: 100,       // 滚动事件节流(毫秒)
  resizeThrottleMs: 200,       // 调整大小事件节流(毫秒)
  searchDebounceMs: 300,       // 搜索输入防抖(毫秒)
  apiCallLimiterEnabled: true, // 启用API调用限制器
  
  // 动画和渲染
  enableAnimations: true,      // 启用页面动画
  reducedMotion: false,        // 减少动画效果(可访问性选项)
  preferReducedMotion: false,  // 尊重用户的减少动画设置
  
  // 会话管理
  sessionCheckFrequencyMs: 5 * 60 * 1000,  // 会话检查频率(毫秒) (5分钟)
  sessionPingFrequencyMs: 15 * 60 * 1000,  // 会话ping频率(毫秒) (15分钟)
  
  // 错误处理和恢复
  autoRetryFailedRequests: true,  // 自动重试失败的请求
  maxRetryAttempts: 3,            // 最大重试次数
  retryBackoffMultiplier: 1.5,    // 重试间隔增长因子
  
  // 缓存控制
  cacheMaxAge: 5 * 60 * 1000,     // 缓存最大存活时间(毫秒) (5分钟)
  staticCacheMaxAge: 24 * 60 * 60 * 1000,  // 静态资源缓存时间(毫秒) (24小时)
  clearCacheOnLogout: true,       // 登出时清除缓存
  
  // 性能监控
  monitorApiResponseTime: true,   // 监控API响应时间
  monitorRendering: false,        // 监控组件渲染时间
  logSlowResponses: true,         // 记录慢响应
  slowResponseThresholdMs: 2000,  // 慢响应阈值(毫秒)
};

// 当前配置
let currentConfig = { ...DEFAULT_CONFIG };

// 性能指标收集
const performanceMetrics = {
  apiCalls: 0,
  apiResponseTimes: [] as number[],
  slowResponses: [] as { url: string, time: number, timestamp: Date }[],
  pageLoads: [] as { page: string, time: number, timestamp: Date }[],
  errors: [] as { type: string, message: string, timestamp: Date }[],
};

/**
 * 初始化性能配置
 * @param overrides 要覆盖的配置项
 */
export function initPerformance(overrides = {}) {
  // 合并默认配置与覆盖项
  currentConfig = { ...DEFAULT_CONFIG, ...overrides };
  
  // 应用配置到其他服务
  setApiDebugLevel(currentConfig.debugLevel);
  
  // 设置减少动画的偏好
  if (currentConfig.preferReducedMotion) {
    checkReducedMotionPreference();
  }
  
  console.log('性能配置已初始化', currentConfig);
  return currentConfig;
}

/**
 * 更新性能配置
 * @param updates 要更新的配置项
 */
export function updatePerformanceConfig(updates = {}) {
  // 更新当前配置
  currentConfig = { ...currentConfig, ...updates };
  
  // 应用更新到其他服务
  setApiDebugLevel(currentConfig.debugLevel);
  
  console.log('性能配置已更新', updates);
  return currentConfig;
}

/**
 * 获取当前性能配置
 */
export function getPerformanceConfig() {
  return { ...currentConfig };
}

/**
 * 重置性能配置为默认值
 */
export function resetPerformanceConfig() {
  currentConfig = { ...DEFAULT_CONFIG };
  setApiDebugLevel(currentConfig.debugLevel);
  return currentConfig;
}

/**
 * 记录API调用性能
 * @param url API URL
 * @param startTime 开始时间
 * @param success 是否成功
 */
export function recordApiPerformance(url: string, startTime: number, success: boolean) {
  if (!currentConfig.enableMonitoring) return;
  
  console.log(`[recordApiPerformance] Recording for: ${url}, StartTime: ${startTime}, Success: ${success}`);
  
  const endTime = performance.now();
  const responseTime = endTime - startTime;
  
  console.log(`[recordApiPerformance] Calculated Response Time: ${responseTime}ms`);
  
  performanceMetrics.apiCalls++;
  performanceMetrics.apiResponseTimes.push(responseTime);
  
  // 记录慢响应
  if (currentConfig.logSlowResponses && responseTime > currentConfig.slowResponseThresholdMs) {
    performanceMetrics.slowResponses.push({
      url,
      time: responseTime,
      timestamp: new Date()
    });
    
    if (currentConfig.debugLevel > 0) {
      console.warn(`慢API响应: ${url} 花费了 ${responseTime.toFixed(0)}ms`);
    }
  }
}

/**
 * 记录页面加载性能
 * @param page 页面名称或路径
 * @param loadTime 加载时间(毫秒)
 */
export function recordPageLoad(page: string, loadTime: number) {
  if (!currentConfig.enableMonitoring) return;
  
  performanceMetrics.pageLoads.push({
    page,
    time: loadTime,
    timestamp: new Date()
  });
  
  if (currentConfig.debugLevel > 1) {
    console.log(`页面加载: ${page} 花费了 ${loadTime.toFixed(0)}ms`);
  }
}

/**
 * 记录错误
 * @param type 错误类型
 * @param message 错误消息
 */
export function recordError(type: string, message: string) {
  if (!currentConfig.enableMonitoring) return;
  
  performanceMetrics.errors.push({
    type,
    message,
    timestamp: new Date()
  });
  
  if (currentConfig.debugLevel > 0) {
    console.error(`性能错误 [${type}]: ${message}`);
  }
}

/**
 * 获取当前性能指标
 */
export function getPerformanceMetrics() {
  // 计算API响应时间的平均值和最大值
  const apiResponseTimes = performanceMetrics.apiResponseTimes;
  const avgResponseTime = apiResponseTimes.length 
    ? apiResponseTimes.reduce((sum, time) => sum + time, 0) / apiResponseTimes.length 
    : 0;
  const maxResponseTime = apiResponseTimes.length 
    ? Math.max(...apiResponseTimes) 
    : 0;
  
  return {
    ...performanceMetrics,
    summary: {
      totalApiCalls: performanceMetrics.apiCalls,
      averageResponseTime: avgResponseTime,
      maxResponseTime: maxResponseTime,
      totalSlowResponses: performanceMetrics.slowResponses.length,
      totalErrors: performanceMetrics.errors.length
    }
  };
}

/**
 * 清除缓存
 * @param type 缓存类型: 'all', 'api', 'static'
 */
export function clearPerformanceCache(type: 'all' | 'api' | 'static' = 'all') {
  if (type === 'all' || type === 'api') {
    console.warn('[Performance] clearApiCache was called but is not available in api.ts');
  }
  
  if (type === 'all' || type === 'static') {
    // 清除本地存储的静态数据缓存
    const keysToRemove: string[] = [];
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && key.startsWith('cache_')) {
        keysToRemove.push(key);
      }
    }
    
    keysToRemove.forEach(key => localStorage.removeItem(key));
    
    if (currentConfig.debugLevel > 0) {
      console.log(`已清除${keysToRemove.length}项静态缓存`);
    }
  }
}

/**
 * 检查用户是否偏好减少动画
 */
function checkReducedMotionPreference() {
  // 检查用户的系统设置是否偏好减少动画
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (prefersReducedMotion) {
    currentConfig.reducedMotion = true;
    document.documentElement.classList.add('reduced-motion');
    
    if (currentConfig.debugLevel > 0) {
      console.log('检测到减少动画偏好，已启用减少动画模式');
    }
  }
}

/**
 * 创建防抖函数
 * @param func 要防抖的函数
 * @param wait 等待时间(毫秒)
 */
export function createDebounce<T extends (...args: any[]) => any>(
  func: T, 
  wait: number = currentConfig.searchDebounceMs
): (...args: Parameters<T>) => void {
  let timeout: ReturnType<typeof setTimeout> | null = null;
  return function(...args: Parameters<T>) {
    if (timeout) {
      clearTimeout(timeout);
    }
    timeout = setTimeout(() => {
      func(...args);
    }, wait);
  };
}

/**
 * 创建节流函数
 * @param func 要节流的函数
 * @param limit 限制时间(毫秒)
 */
export function createThrottle<T extends (...args: any[]) => any>(
  func: T, 
  limit: number = currentConfig.scrollThrottleMs
): (...args: Parameters<T>) => void {
  let inThrottle = false;
  return function(...args: Parameters<T>) {
    if (!inThrottle) {
      func(...args);
      inThrottle = true;
      setTimeout(() => {
        inThrottle = false;
      }, limit);
    }
  };
}

// 导出性能工具
export const performanceUtils = {
  initPerformance,
  updatePerformanceConfig,
  getPerformanceConfig,
  resetPerformanceConfig,
  recordApiPerformance,
  recordPageLoad,
  recordError,
  getPerformanceMetrics,
  clearPerformanceCache,
  createDebounce,
  createThrottle
};

// 默认导出
export default performanceUtils; 
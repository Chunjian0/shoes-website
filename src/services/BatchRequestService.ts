/**
 * BatchRequestService - API批量请求服务
 * 
 * 这个服务提供了API请求批处理功能，可以合并多个API请求为单个HTTP请求，
 * 减少网络开销，提高应用性能。
 */

import { performanceMonitor, MarkType } from './PerformanceMonitor';

// 单个批量请求项定义
interface BatchItem<T = any> {
  id: string; // 唯一标识
  path: string; // API路径
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH'; // HTTP方法
  params?: Record<string, any>; // 查询参数（GET）或请求体（其他）
  headers?: Record<string, any>; // 请求头
  resolve: (value: T) => void; // 成功回调
  reject: (error: any) => void; // 失败回调
  timestamp: number; // 添加时间戳
  priority: number; // 优先级（越低越高）
  timeout?: number; // 请求超时 (ms)
}

// 批量请求配置
interface BatchConfig {
  endpoint: string; // 批量请求API端点
  maxBatchSize: number; // 单个批次最大请求数
  delayTime: number; // 合并延迟时间 (ms)
  retryCount: number; // 重试次数
  retryDelay: number; // 重试延迟 (ms)
  enableRequestPriority: boolean; // 是否启用请求优先级
  timeout: number; // 默认请求超时 (ms)
  headers: Record<string, string>; // 默认请求头
  useCredentials: boolean; // 是否发送凭证
}

// 批量请求响应格式
interface BatchResponse {
  results: {
    id: string;
    statusCode: number;
    success: boolean;
    data?: any;
    error?: {
      message: string;
      code?: string;
      details?: any;
    };
  }[];
}

class BatchRequestService {
  private config: BatchConfig;
  private batchQueue: BatchItem[] = [];
  private timeoutId: number | null = null;
  private processingBatch: boolean = false;
  private static instance: BatchRequestService;

  constructor(config?: Partial<BatchConfig>) {
    this.config = {
      endpoint: '/api/batch', // 默认批量请求端点
      maxBatchSize: 10, // 默认最多10个请求一批
      delayTime: 50, // 默认50ms延迟
      retryCount: 1, // 默认重试1次
      retryDelay: 1000, // 默认重试延迟1秒
      enableRequestPriority: true, // 默认启用请求优先级
      timeout: 10000, // 默认10秒超时
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'BatchRequest'
      },
      useCredentials: true, // 默认发送凭证
      ...config
    };
  }

  /**
   * 获取单例实例
   */
  public static getInstance(config?: Partial<BatchConfig>): BatchRequestService {
    if (!BatchRequestService.instance) {
      BatchRequestService.instance = new BatchRequestService(config);
    }
    return BatchRequestService.instance;
  }

  /**
   * 添加请求到批量队列
   * @returns Promise 返回请求结果的Promise
   */
  public add<T = any>(
    path: string,
    method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH',
    params?: Record<string, any>,
    options: {
      headers?: Record<string, any>;
      priority?: number;
      timeout?: number;
      skipBatch?: boolean; // 跳过批处理，直接执行
    } = {}
  ): Promise<T> {
    // 如果请求标记为跳过批处理，直接发送单个请求
    if (options.skipBatch) {
      return this.sendSingleRequest<T>(path, method, params, options);
    }

    return new Promise<T>((resolve, reject) => {
      const id = this.generateRequestId();
      
      // 创建批处理项
      const batchItem: BatchItem<T> = {
        id,
        path,
        method,
        params,
        headers: options.headers,
        resolve,
        reject,
        timestamp: Date.now(),
        priority: options.priority ?? 5, // 默认优先级中等
        timeout: options.timeout
      };
      
      // 添加到队列
      this.batchQueue.push(batchItem);
      
      // 根据优先级排序队列
      if (this.config.enableRequestPriority) {
        this.batchQueue.sort((a, b) => a.priority - b.priority);
      }
      
      // 安排批处理
      this.scheduleBatch();
      
      // 设置单个请求超时
      const timeout = options.timeout || this.config.timeout;
      setTimeout(() => {
        // 检查请求是否仍在队列中
        const index = this.batchQueue.findIndex(item => item.id === id);
        if (index >= 0) {
          // 从队列中移除
          this.batchQueue.splice(index, 1);
          reject(new Error(`Request timeout after ${timeout}ms`));
        }
      }, timeout);
    });
  }

  /**
   * 安排批处理
   */
  private scheduleBatch(): void {
    // 如果已经有计划的批处理，不再创建新的
    if (this.timeoutId !== null) {
      return;
    }
    
    // 设置延迟执行批处理
    this.timeoutId = window.setTimeout(() => {
      this.timeoutId = null;
      this.processBatch();
    }, this.config.delayTime);
  }

  /**
   * 处理批量请求
   */
  private async processBatch(): Promise<void> {
    // 如果正在处理批处理或队列为空，返回
    if (this.processingBatch || this.batchQueue.length === 0) {
      return;
    }
    
    this.processingBatch = true;
    
    try {
      // 取出最大批量大小的请求
      const currentBatch = this.batchQueue.splice(0, this.config.maxBatchSize);
      
      if (currentBatch.length === 1) {
        // 如果只有一个请求，直接发送单个请求
        const item = currentBatch[0];
        try {
          const result = await this.sendSingleRequest(
            item.path, 
            item.method, 
            item.params, 
            { headers: item.headers, timeout: item.timeout }
          );
          item.resolve(result);
        } catch (error) {
          item.reject(error);
        }
      } else {
        // 发送批量请求
        await this.sendBatchRequest(currentBatch);
      }
    } catch (error) {
      console.error('Error processing batch:', error);
    } finally {
      this.processingBatch = false;
      
      // 如果队列中还有请求，继续处理
      if (this.batchQueue.length > 0) {
        this.scheduleBatch();
      }
    }
  }

  /**
   * 发送单个请求（非批量）
   */
  private sendSingleRequest<T = any>(
    path: string,
    method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH',
    params?: Record<string, any>,
    options: {
      headers?: Record<string, any>;
      timeout?: number;
    } = {}
  ): Promise<T> {
    const url = new URL(path, window.location.origin);
    const headers = {
      ...this.config.headers,
      ...options.headers
    };
    
    // 对于GET请求，将参数添加到URL
    if (method === 'GET' && params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          url.searchParams.append(key, value.toString());
        }
      });
    }
    
    // 性能监控开始
    const performanceId = performanceMonitor.markStart(MarkType.API_REQUEST, `api:${path}`, {
      url: url.toString(),
      method,
      params
    });
    
    return new Promise<T>((resolve, reject) => {
      const abortController = new AbortController();
      const timeout = options.timeout || this.config.timeout;
      
      // 设置请求超时
      const timeoutId = window.setTimeout(() => {
        abortController.abort();
        performanceMonitor.markEnd(performanceId, { success: false, error: 'timeout' });
        reject(new Error(`Request timeout after ${timeout}ms`));
      }, timeout);
      
      fetch(url.toString(), {
        method,
        headers,
        body: method !== 'GET' && params ? JSON.stringify(params) : undefined,
        credentials: this.config.useCredentials ? 'include' : 'same-origin',
        signal: abortController.signal
      })
      .then(async response => {
        clearTimeout(timeoutId);
        
        const contentType = response.headers.get('content-type');
        let data: any;
        
        if (contentType && contentType.includes('application/json')) {
          data = await response.json();
        } else {
          data = await response.text();
        }
        
        if (!response.ok) {
          performanceMonitor.markEnd(performanceId, { 
            success: false, 
            statusCode: response.status,
            error: data
          });
          reject({
            status: response.status,
            statusText: response.statusText,
            data
          });
          return;
        }
        
        performanceMonitor.markEnd(performanceId, { 
          success: true, 
          statusCode: response.status
        });
        resolve(data);
      })
      .catch(error => {
        clearTimeout(timeoutId);
        performanceMonitor.markEnd(performanceId, { 
          success: false, 
          error: error.message
        });
        reject(error);
      });
    });
  }

  /**
   * 发送批量请求
   */
  private async sendBatchRequest(batch: BatchItem[]): Promise<void> {
    const batchId = this.generateRequestId();
    
    // 准备批量请求体
    const batchPayload = batch.map(item => ({
      id: item.id,
      path: item.path,
      method: item.method,
      params: item.params,
      headers: item.headers
    }));
    
    // 性能监控开始
    const performanceId = performanceMonitor.markStart(MarkType.API_REQUEST, `batch:${batchId}`, {
      url: this.config.endpoint,
      method: 'POST',
      batchSize: batch.length
    });
    
    try {
      // 发送批量请求
      const response = await fetch(this.config.endpoint, {
        method: 'POST',
        headers: this.config.headers,
        body: JSON.stringify(batchPayload),
        credentials: this.config.useCredentials ? 'include' : 'same-origin'
      });
      
      if (!response.ok) {
        throw new Error(`Batch request failed with status ${response.status}`);
      }
      
      const batchResponse: BatchResponse = await response.json();
      
      // 处理批量响应
      batchResponse.results.forEach(result => {
        const batchItem = batch.find(item => item.id === result.id);
        
        if (!batchItem) {
          console.error(`Batch item with id ${result.id} not found in batch`);
          return;
        }
        
        if (result.success) {
          batchItem.resolve(result.data);
        } else {
          batchItem.reject(result.error || { message: 'Unknown error' });
        }
      });
      
      // 检查是否有未返回结果的请求
      const unresolved = batch.filter(item => 
        !batchResponse.results.some(result => result.id === item.id)
      );
      
      // 处理未解析的请求
      if (unresolved.length > 0) {
        console.warn(`${unresolved.length} requests in batch had no response`);
        unresolved.forEach(item => {
          item.reject({ message: 'No response from batch request' });
        });
      }
      
      performanceMonitor.markEnd(performanceId, {
        success: true,
        batchSize: batch.length,
        successCount: batchResponse.results.filter(r => r.success).length,
        failureCount: batchResponse.results.filter(r => !r.success).length
      });
    } catch (error) {
      // 批量请求失败，回退到单个请求
      console.error('Batch request failed, falling back to individual requests', error);
      performanceMonitor.markEnd(performanceId, {
        success: false,
        error: error.message,
        fallback: 'individual'
      });
      
      // 对每个请求单独发送
      await Promise.allSettled(
        batch.map(async item => {
          try {
            const result = await this.sendSingleRequest(
              item.path, 
              item.method, 
              item.params, 
              { headers: item.headers, timeout: item.timeout }
            );
            item.resolve(result);
          } catch (error) {
            item.reject(error);
          }
        })
      );
    }
  }

  /**
   * 生成唯一请求ID
   */
  private generateRequestId(): string {
    return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * 清空请求队列
   */
  public clearQueue(): void {
    const pendingRequests = [...this.batchQueue];
    this.batchQueue = [];
    
    // 拒绝所有待处理的请求
    pendingRequests.forEach(item => {
      item.reject(new Error('Request cancelled: batch queue cleared'));
    });
    
    console.log(`Cleared ${pendingRequests.length} pending requests from batch queue`);
  }

  /**
   * 获取当前队列状态
   */
  public getQueueStatus(): {
    queueLength: number;
    processing: boolean;
    oldestRequest: number | null;
  } {
    const oldestRequest = this.batchQueue.length > 0
      ? Math.min(...this.batchQueue.map(item => item.timestamp))
      : null;
    
    return {
      queueLength: this.batchQueue.length,
      processing: this.processingBatch,
      oldestRequest
    };
  }

  /**
   * 更新服务配置
   */
  public updateConfig(config: Partial<BatchConfig>): void {
    this.config = {
      ...this.config,
      ...config
    };
  }
}

// 创建单例实例
export const batchRequestService = BatchRequestService.getInstance();

// 便捷的API封装
export const batchApi = {
  /**
   * 发送GET请求
   */
  get: <T = any>(
    path: string, 
    params?: Record<string, any>, 
    options?: {
      headers?: Record<string, any>;
      priority?: number;
      timeout?: number;
      skipBatch?: boolean;
    }
  ): Promise<T> => {
    return batchRequestService.add<T>(path, 'GET', params, options);
  },
  
  /**
   * 发送POST请求
   */
  post: <T = any>(
    path: string, 
    data?: Record<string, any>, 
    options?: {
      headers?: Record<string, any>;
      priority?: number;
      timeout?: number;
      skipBatch?: boolean;
    }
  ): Promise<T> => {
    return batchRequestService.add<T>(path, 'POST', data, options);
  },
  
  /**
   * 发送PUT请求
   */
  put: <T = any>(
    path: string, 
    data?: Record<string, any>, 
    options?: {
      headers?: Record<string, any>;
      priority?: number;
      timeout?: number;
      skipBatch?: boolean;
    }
  ): Promise<T> => {
    return batchRequestService.add<T>(path, 'PUT', data, options);
  },
  
  /**
   * 发送DELETE请求
   */
  delete: <T = any>(
    path: string, 
    data?: Record<string, any>, 
    options?: {
      headers?: Record<string, any>;
      priority?: number;
      timeout?: number;
      skipBatch?: boolean;
    }
  ): Promise<T> => {
    return batchRequestService.add<T>(path, 'DELETE', data, options);
  },
  
  /**
   * 发送PATCH请求
   */
  patch: <T = any>(
    path: string, 
    data?: Record<string, any>, 
    options?: {
      headers?: Record<string, any>;
      priority?: number;
      timeout?: number;
      skipBatch?: boolean;
    }
  ): Promise<T> => {
    return batchRequestService.add<T>(path, 'PATCH', data, options);
  },
  
  /**
   * 批量发送多个请求
   */
  batch: <T = any[]>(
    requests: {
      path: string;
      method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
      params?: Record<string, any>;
      headers?: Record<string, any>;
      priority?: number;
    }[]
  ): Promise<T[]> => {
    return Promise.all(
      requests.map(req => 
        batchRequestService.add(
          req.path, 
          req.method, 
          req.params, 
          { headers: req.headers, priority: req.priority }
        )
      )
    );
  },
  
  // 直接访问服务实例
  clearQueue: () => batchRequestService.clearQueue(),
  getQueueStatus: () => batchRequestService.getQueueStatus(),
  updateConfig: (config: Partial<BatchConfig>) => batchRequestService.updateConfig(config)
};

export default batchRequestService; 
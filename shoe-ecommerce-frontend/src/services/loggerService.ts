/**
 * 前端日志收集服务
 * 用于在前端环境中收集和保存日志，以便诊断问题
 */

// 日志最大条目数
const MAX_LOG_ENTRIES = 1000;

// 日志级别
export enum LogLevel {
  DEBUG = 'DEBUG',
  INFO = 'INFO',
  WARN = 'WARN',
  ERROR = 'ERROR'
}

// 日志条目接口
interface LogEntry {
  timestamp: string;
  level: LogLevel;
  category: string;
  message: string;
  data?: any;
}

// 日志服务类
class LoggerService {
  private logs: LogEntry[] = [];
  private enabled: boolean = true;
  
  // 初始化时从localStorage恢复日志
  constructor() {
    try {
      const savedLogs = localStorage.getItem('app_logs');
      if (savedLogs) {
        this.logs = JSON.parse(savedLogs);
        // 确保日志不超过最大条目数
        if (this.logs.length > MAX_LOG_ENTRIES) {
          this.logs = this.logs.slice(-MAX_LOG_ENTRIES);
        }
      }
    } catch (error) {
      console.error('恢复日志失败:', error);
      this.logs = [];
    }
    
    // 每分钟保存一次日志到localStorage
    setInterval(() => this.saveLogsToStorage(), 60000);
  }
  
  // 保存日志到localStorage
  private saveLogsToStorage(): void {
    if (this.logs.length === 0) return;
    
    try {
      localStorage.setItem('app_logs', JSON.stringify(this.logs));
    } catch (error) {
      console.error('保存日志失败:', error);
    }
  }
  
  // 添加日志
  private addLog(level: LogLevel, category: string, message: string, data?: any): void {
    if (!this.enabled) return;
    
    const logEntry: LogEntry = {
      timestamp: new Date().toISOString(),
      level,
      category,
      message,
      data: data ? this.sanitizeData(data) : undefined
    };
    
    this.logs.push(logEntry);
    
    // 如果日志过多，删除最早的
    if (this.logs.length > MAX_LOG_ENTRIES) {
      this.logs.shift();
    }
    
    // 在控制台显示日志
    this.printToConsole(logEntry);
  }
  
  // 清理日志数据，移除敏感信息
  private sanitizeData(data: any): any {
    if (!data) return data;
    
    try {
      // 创建一个深拷贝
      const copy = JSON.parse(JSON.stringify(data));
      
      // 可以在这里添加敏感字段处理逻辑
      // 例如隐藏密码、令牌等
      
      return copy;
    } catch (error) {
      return '[无法序列化的数据]';
    }
  }
  
  // 在控制台显示日志
  private printToConsole(log: LogEntry): void {
    const prefix = `[${log.timestamp.split('T')[1].split('.')[0]}] [${log.level}] [${log.category}]`;
    
    switch (log.level) {
      case LogLevel.ERROR:
        console.error(prefix, log.message, log.data || '');
        break;
      case LogLevel.WARN:
        console.warn(prefix, log.message, log.data || '');
        break;
      case LogLevel.INFO:
        console.info(prefix, log.message, log.data || '');
        break;
      default:
        console.log(prefix, log.message, log.data || '');
    }
  }
  
  // 获取所有日志
  public getLogs(): LogEntry[] {
    return [...this.logs];
  }
  
  // 清空日志
  public clearLogs(): void {
    this.logs = [];
    localStorage.removeItem('app_logs');
  }
  
  // 启用/禁用日志
  public setEnabled(enabled: boolean): void {
    this.enabled = enabled;
  }
  
  // 保存日志到文件
  public exportLogs(): string {
    const logText = this.logs.map(log => 
      `[${log.timestamp}] [${log.level}] [${log.category}] ${log.message} ${log.data ? JSON.stringify(log.data) : ''}`
    ).join('\n');
    
    return logText;
  }
  
  // 发送日志到服务器
  public async sendLogsToServer(): Promise<boolean> {
    try {
      const response = await fetch('/api/logs', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          logs: this.logs
        }),
      });
      
      if (response.ok) {
        return true;
      }
      return false;
    } catch (error) {
      console.error('发送日志失败:', error);
      return false;
    }
  }
  
  // 快捷方法 - 调试日志
  public debug(category: string, message: string, data?: any): void {
    this.addLog(LogLevel.DEBUG, category, message, data);
  }
  
  // 快捷方法 - 信息日志
  public info(category: string, message: string, data?: any): void {
    this.addLog(LogLevel.INFO, category, message, data);
  }
  
  // 快捷方法 - 警告日志
  public warn(category: string, message: string, data?: any): void {
    this.addLog(LogLevel.WARN, category, message, data);
  }
  
  // 快捷方法 - 错误日志
  public error(category: string, message: string, data?: any): void {
    this.addLog(LogLevel.ERROR, category, message, data);
  }
  
  // 记录购物车操作
  public logCartOperation(action: string, details: any): void {
    this.info('CART', `${action}操作`, details);
  }
  
  // 记录API请求
  public logApiRequest(endpoint: string, method: string, params: any): void {
    this.debug('API', `请求 ${method.toUpperCase()} ${endpoint}`, params);
  }
  
  // 记录API响应
  public logApiResponse(endpoint: string, status: number, data: any): void {
    if (status >= 400) {
      this.error('API', `响应错误 ${status} ${endpoint}`, data);
    } else {
      this.debug('API', `响应成功 ${status} ${endpoint}`, data);
    }
  }
  
  // 记录产品选择
  public logProductSelection(productId: number, templateId: number, details: any): void {
    this.info('PRODUCT', `选择产品 ID=${productId}, 模板ID=${templateId}`, details);
  }
}

// 创建单例实例
const loggerService = new LoggerService();

export default loggerService; 
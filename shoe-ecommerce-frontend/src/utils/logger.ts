/**
 * 简单的日志工具
 * 在开发环境下输出详细日志，在生产环境下可以配置为只输出重要日志或发送到日志服务
 */

// 确定当前环境
const isDevelopment = process.env.NODE_ENV !== 'production';

// 日志级别
enum LogLevel {
  INFO = 'info',
  WARN = 'warn',
  ERROR = 'error',
  DEBUG = 'debug'
}

// 日志工具对象
export const logger = {
  /**
   * 输出信息日志
   * @param message 日志消息
   * @param data 附加数据
   */
  info(message: string, data?: any): void {
    this.log(LogLevel.INFO, message, data);
  },

  /**
   * 输出警告日志
   * @param message 日志消息
   * @param data 附加数据
   */
  warn(message: string, data?: any): void {
    this.log(LogLevel.WARN, message, data);
  },

  /**
   * 输出错误日志
   * @param message 日志消息
   * @param data 附加数据
   */
  error(message: string, data?: any): void {
    this.log(LogLevel.ERROR, message, data);
  },

  /**
   * 输出调试日志，仅在开发环境中显示
   * @param message 日志消息
   * @param data 附加数据
   */
  debug(message: string, data?: any): void {
    if (isDevelopment) {
      this.log(LogLevel.DEBUG, message, data);
    }
  },

  /**
   * 基础日志输出方法
   * @param level 日志级别
   * @param message 日志消息
   * @param data 附加数据
   */
  log(level: LogLevel, message: string, data?: any): void {
    const timestamp = new Date().toISOString();
    const prefix = `[${timestamp}] [${level.toUpperCase()}]`;
    
    switch (level) {
      case LogLevel.INFO:
        console.info(`${prefix} ${message}`, data ? data : '');
        break;
      case LogLevel.WARN:
        console.warn(`${prefix} ${message}`, data ? data : '');
        break;
      case LogLevel.ERROR:
        console.error(`${prefix} ${message}`, data ? data : '');
        break;
      case LogLevel.DEBUG:
        console.debug(`${prefix} ${message}`, data ? data : '');
        break;
    }
    
    // 在这里可以添加将日志发送到服务器或其他日志服务的逻辑
    // 例如：在生产环境中，可能希望将错误日志发送到监控服务
  }
};

export default logger; 
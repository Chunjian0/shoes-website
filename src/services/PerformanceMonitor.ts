/**
 * PerformanceMonitor - API和前端性能监控服务
 * 
 * 这个服务提供API响应时间监控、慢请求记录和性能分析功能。
 * 可以用于识别性能瓶颈，并提供数据支持优化决策。
 */

// 性能标记类型
export enum MarkType {
  API_REQUEST = 'api_request',
  COMPONENT_RENDER = 'component_render',
  PAGE_LOAD = 'page_load',
  RESOURCE_LOAD = 'resource_load',
  CUSTOM = 'custom'
}

// 性能记录项
interface PerformanceRecord {
  id: string;
  type: MarkType;
  name: string;
  startTime: number;
  endTime: number | null;
  duration: number | null;
  metadata: Record<string, any>;
  success: boolean | null;
}

// 慢请求配置
interface SlowThresholds {
  [MarkType.API_REQUEST]: number;
  [MarkType.COMPONENT_RENDER]: number;
  [MarkType.PAGE_LOAD]: number;
  [MarkType.RESOURCE_LOAD]: number;
  [MarkType.CUSTOM]: number;
}

// 监控服务配置
interface MonitorConfig {
  enabled: boolean;
  maxRecords: number;
  slowThresholds: SlowThresholds;
  persistentStorage: boolean;
  debugLog: boolean;
  logToServer: boolean;
  serverEndpoint?: string;
  samplingRate: number; // 0-1, percentage of requests to monitor
}

class PerformanceMonitor {
  private records: Map<string, PerformanceRecord>;
  private config: MonitorConfig;
  private lastReportTime: number = 0;
  private isReportingInProgress: boolean = false;
  private apiEndpoints: Set<string> = new Set();
  private static instance: PerformanceMonitor;

  constructor(config?: Partial<MonitorConfig>) {
    this.config = {
      enabled: process.env.NODE_ENV === 'development',
      maxRecords: 100,
      slowThresholds: {
        [MarkType.API_REQUEST]: 500, // ms
        [MarkType.COMPONENT_RENDER]: 50, // ms
        [MarkType.PAGE_LOAD]: 3000, // ms
        [MarkType.RESOURCE_LOAD]: 1000, // ms
        [MarkType.CUSTOM]: 100 // ms
      },
      persistentStorage: true,
      debugLog: process.env.NODE_ENV === 'development',
      logToServer: process.env.NODE_ENV === 'production',
      serverEndpoint: '/api/performance-log',
      samplingRate: process.env.NODE_ENV === 'production' ? 0.1 : 1.0,
      ...config
    };

    this.records = new Map<string, PerformanceRecord>();
    this.loadFromStorage();

    // Automatically report performance data every 5 minutes
    if (this.config.logToServer) {
      setInterval(() => this.reportToServer(), 5 * 60 * 1000);
    }
    
    // Clean up old records every minute
    setInterval(() => this.cleanup(), 60 * 1000);
    
    // Setup performance observer for web vitals
    this.setupPerformanceObserver();
    
    // Record initial page load
    this.markStart(MarkType.PAGE_LOAD, 'initial_page_load', { url: window.location.href });
    window.addEventListener('load', () => {
      this.markEnd('initial_page_load', { success: true });
    });
  }

  /**
   * Get singleton instance
   */
  public static getInstance(config?: Partial<MonitorConfig>): PerformanceMonitor {
    if (!PerformanceMonitor.instance) {
      PerformanceMonitor.instance = new PerformanceMonitor(config);
    }
    return PerformanceMonitor.instance;
  }

  /**
   * Mark the start of a performance measurement
   */
  markStart(type: MarkType, name: string, metadata: Record<string, any> = {}): string {
    if (!this.shouldRecord()) {
      return name;
    }

    const id = `${type}:${name}:${Date.now()}`;
    
    const record: PerformanceRecord = {
      id,
      type,
      name,
      startTime: performance.now(),
      endTime: null,
      duration: null,
      metadata,
      success: null
    };

    this.records.set(id, record);
    this.debugLog(`Started: ${type} - ${name}`);
    
    // Manage records limit
    if (this.records.size > this.config.maxRecords) {
      this.removeOldestRecord();
    }

    return id;
  }

  /**
   * Mark the end of a performance measurement
   */
  markEnd(idOrName: string, metadata: Record<string, any> = {}): number | null {
    if (!this.config.enabled) {
      return null;
    }

    // Find the record - either by ID or by name
    let record: PerformanceRecord | undefined;
    let recordId = idOrName;

    if (!this.records.has(idOrName)) {
      // Try to find by name (most recent first)
      const records = Array.from(this.records.values())
        .filter(r => r.name === idOrName && r.endTime === null)
        .sort((a, b) => b.startTime - a.startTime);
      
      if (records.length > 0) {
        record = records[0];
        recordId = record.id;
      }
    } else {
      record = this.records.get(idOrName);
    }

    if (!record) {
      this.debugLog(`Warning: No record found for ${idOrName}`);
      return null;
    }

    // Update record
    record.endTime = performance.now();
    record.duration = record.endTime - record.startTime;
    record.metadata = { ...record.metadata, ...metadata };
    record.success = metadata.success !== undefined ? metadata.success : true;

    this.records.set(recordId, record);
    
    // Log slow operations
    if (record.duration > this.config.slowThresholds[record.type]) {
      this.logSlowOperation(record);
    }

    this.debugLog(`Ended: ${record.type} - ${record.name} (${record.duration.toFixed(2)}ms)`);
    this.saveToStorage();
    
    // If it's an API request, keep track of the endpoint
    if (record.type === MarkType.API_REQUEST && record.metadata.url) {
      this.trackApiEndpoint(record.metadata.url, record.duration);
    }
    
    return record.duration;
  }

  /**
   * Measure API request performance
   */
  measureApiRequest<T>(url: string, requestFn: () => Promise<T>, metadata: Record<string, any> = {}): Promise<T> {
    const id = this.markStart(MarkType.API_REQUEST, `api:${this.getApiEndpointName(url)}`, {
      url,
      method: metadata.method || 'GET',
      ...metadata
    });

    return requestFn()
      .then(response => {
        this.markEnd(id, { 
          success: true,
          status: response && (response as any).status,
          responseData: this.sanitizeResponseForLogging(response)
        });
        return response;
      })
      .catch(error => {
        this.markEnd(id, { 
          success: false, 
          error: error.message || 'Unknown error',
          status: error.status || error.statusCode || 0
        });
        throw error;
      });
  }

  /**
   * Measure component rendering performance
   */
  startComponentRender(componentName: string, props: Record<string, any> = {}): string {
    return this.markStart(MarkType.COMPONENT_RENDER, `component:${componentName}`, {
      component: componentName,
      props: this.sanitizePropsForLogging(props)
    });
  }

  /**
   * End component rendering measurement
   */
  endComponentRender(id: string, metadata: Record<string, any> = {}): number | null {
    return this.markEnd(id, metadata);
  }

  /**
   * Get performance records
   */
  getRecords(): PerformanceRecord[] {
    return Array.from(this.records.values());
  }
  
  /**
   * Get slow operations
   */
  getSlowOperations(): PerformanceRecord[] {
    return this.getRecords().filter(record => 
      record.duration !== null && 
      record.duration > this.config.slowThresholds[record.type]
    );
  }
  
  /**
   * Get API performance metrics
   */
  getApiMetrics(): Record<string, { count: number, avgTime: number, slowCount: number, failCount: number }> {
    const apiRecords = this.getRecords().filter(r => r.type === MarkType.API_REQUEST);
    const metrics: Record<string, { 
      count: number, 
      totalTime: number, 
      avgTime: number, 
      slowCount: number, 
      failCount: number,
      min: number,
      max: number
    }> = {};
    
    apiRecords.forEach(record => {
      if (!record.duration) return;
      
      const endpoint = record.metadata.url 
        ? this.getApiEndpointName(record.metadata.url)
        : record.name;
      
      if (!metrics[endpoint]) {
        metrics[endpoint] = { 
          count: 0, 
          totalTime: 0, 
          avgTime: 0, 
          slowCount: 0, 
          failCount: 0,
          min: Number.MAX_VALUE,
          max: 0
        };
      }
      
      metrics[endpoint].count++;
      metrics[endpoint].totalTime += record.duration;
      metrics[endpoint].avgTime = metrics[endpoint].totalTime / metrics[endpoint].count;
      
      if (record.duration > this.config.slowThresholds[MarkType.API_REQUEST]) {
        metrics[endpoint].slowCount++;
      }
      
      if (record.success === false) {
        metrics[endpoint].failCount++;
      }
      
      metrics[endpoint].min = Math.min(metrics[endpoint].min, record.duration);
      metrics[endpoint].max = Math.max(metrics[endpoint].max, record.duration);
    });
    
    return metrics;
  }
  
  /**
   * Clear all performance records
   */
  clearRecords(): void {
    this.records.clear();
    this.saveToStorage();
    this.debugLog('All performance records cleared');
  }

  /**
   * Report performance data to server
   */
  reportToServer(): Promise<void> {
    if (!this.config.logToServer || this.isReportingInProgress || !this.config.serverEndpoint) {
      return Promise.resolve();
    }
    
    // Only report once every 5 minutes
    const now = Date.now();
    if (now - this.lastReportTime < 5 * 60 * 1000) {
      return Promise.resolve();
    }
    
    this.isReportingInProgress = true;
    this.lastReportTime = now;
    
    const recordsToReport = this.getRecords().filter(r => r.endTime !== null);
    if (recordsToReport.length === 0) {
      this.isReportingInProgress = false;
      return Promise.resolve();
    }
    
    // Get browser and environment info
    const reportData = {
      timestamp: now,
      userAgent: navigator.userAgent,
      url: window.location.href,
      screenSize: `${window.innerWidth}x${window.innerHeight}`,
      records: recordsToReport,
      apiMetrics: this.getApiMetrics(),
      webVitals: this.getWebVitals()
    };
    
    return fetch(this.config.serverEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(reportData),
      // Don't monitor this request to avoid infinite loops
      credentials: 'same-origin'
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`Failed to report performance data: ${response.status}`);
      }
      this.debugLog(`Reported ${recordsToReport.length} performance records to server`);
    })
    .catch(error => {
      console.error('Error reporting performance data:', error);
    })
    .finally(() => {
      this.isReportingInProgress = false;
    });
  }

  /**
   * Get web vitals metrics
   */
  private getWebVitals(): Record<string, any> {
    // This would normally use the web-vitals library
    // For now we'll return basic metrics from Performance API
    const navigationEntry = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
    
    if (!navigationEntry) {
      return {};
    }
    
    return {
      // First Contentful Paint
      FCP: this.getFCP(),
      
      // DNS lookup time
      DNS: navigationEntry.domainLookupEnd - navigationEntry.domainLookupStart,
      
      // Connection time
      Connect: navigationEntry.connectEnd - navigationEntry.connectStart,
      
      // Time to First Byte
      TTFB: navigationEntry.responseStart - navigationEntry.requestStart,
      
      // DOM Content Loaded
      DCL: navigationEntry.domContentLoadedEventEnd - navigationEntry.navigationStart,
      
      // Time to Interactive (approximation)
      TTI: navigationEntry.domInteractive - navigationEntry.navigationStart,
      
      // Load Event
      Load: navigationEntry.loadEventEnd - navigationEntry.navigationStart
    };
  }

  /**
   * Get First Contentful Paint from Performance API
   */
  private getFCP(): number | null {
    const paintEntries = performance.getEntriesByType('paint');
    const fcpEntry = paintEntries.find(entry => entry.name === 'first-contentful-paint');
    return fcpEntry ? fcpEntry.startTime : null;
  }

  /**
   * Set up Performance Observer for web vitals
   */
  private setupPerformanceObserver(): void {
    if (!window.PerformanceObserver) {
      return;
    }
    
    try {
      // Observe paint timing events (FP, FCP)
      const paintObserver = new PerformanceObserver(entries => {
        entries.getEntries().forEach(entry => {
          this.debugLog(`Paint metric: ${entry.name} = ${entry.startTime.toFixed(2)}ms`);
        });
      });
      paintObserver.observe({ entryTypes: ['paint'] });
      
      // Observe long tasks
      const longTaskObserver = new PerformanceObserver(entries => {
        entries.getEntries().forEach(entry => {
          this.markStart(MarkType.CUSTOM, 'long_task', {
            duration: entry.duration,
            startTime: entry.startTime,
            name: 'Long Task'
          });
          this.debugLog(`Long task detected: ${entry.duration.toFixed(2)}ms`);
        });
      });
      longTaskObserver.observe({ entryTypes: ['longtask'] });
      
      // Observe resource timing
      const resourceObserver = new PerformanceObserver(entries => {
        entries.getEntries().forEach(entry => {
          if ((entry as PerformanceResourceTiming).initiatorType === 'fetch' ||
              (entry as PerformanceResourceTiming).initiatorType === 'xmlhttprequest') {
            // Skip - these are handled by our API monitoring
            return;
          }
          
          const resourceName = entry.name.split('/').pop() || entry.name;
          const entryType = (entry as PerformanceResourceTiming).initiatorType || 'resource';
          
          this.markStart(MarkType.RESOURCE_LOAD, `resource:${entryType}:${resourceName}`, {
            url: entry.name,
            entryType: entryType,
            startTime: entry.startTime,
            duration: entry.duration
          });
          
          this.markEnd(`resource:${entryType}:${resourceName}`, { 
            success: true,
            size: (entry as PerformanceResourceTiming).transferSize || 0,
            compressedSize: (entry as PerformanceResourceTiming).encodedBodySize || 0,
            actualSize: (entry as PerformanceResourceTiming).decodedBodySize || 0
          });
        });
      });
      resourceObserver.observe({ entryTypes: ['resource'] });
    } catch (e) {
      console.error('Error setting up performance observers:', e);
    }
  }

  /**
   * Should record this operation (based on sampling rate)
   */
  private shouldRecord(): boolean {
    if (!this.config.enabled) {
      return false;
    }
    
    // Always record in development
    if (process.env.NODE_ENV === 'development') {
      return true;
    }
    
    // Sample in production based on rate
    return Math.random() < this.config.samplingRate;
  }

  /**
   * Get simplified API endpoint name from URL
   */
  private getApiEndpointName(url: string): string {
    try {
      const urlObj = new URL(url, window.location.origin);
      const pathname = urlObj.pathname;
      
      // Replace numeric IDs with :id placeholder
      return pathname.replace(/\/\d+/g, '/:id');
    } catch (e) {
      return url;
    }
  }

  /**
   * Track API endpoint for metrics
   */
  private trackApiEndpoint(url: string, duration: number | null): void {
    const endpoint = this.getApiEndpointName(url);
    this.apiEndpoints.add(endpoint);
  }

  /**
   * Log slow operation
   */
  private logSlowOperation(record: PerformanceRecord): void {
    if (!record.duration) return;
    
    const threshold = this.config.slowThresholds[record.type];
    const timeOverThreshold = record.duration - threshold;
    const percentOver = Math.round((timeOverThreshold / threshold) * 100);
    
    console.warn(
      `%cSlow ${record.type}: ${record.name} took ${record.duration.toFixed(2)}ms ` + 
      `(${percentOver}% over ${threshold}ms threshold)`,
      'color: #ff6b6b; font-weight: bold;'
    );
    
    if (record.type === MarkType.API_REQUEST) {
      console.warn('API details:', {
        url: record.metadata.url,
        method: record.metadata.method,
        success: record.success,
        duration: record.duration
      });
    }
  }

  /**
   * Sanitize response data for logging
   */
  private sanitizeResponseForLogging(response: any): any {
    if (!response) return null;
    
    // For API responses, we only want basic info, not full data 
    // to avoid logging sensitive information
    try {
      if (typeof response === 'object') {
        const sanitized: Record<string, any> = {};
        
        // Include only safe properties
        if (response.status) sanitized.status = response.status;
        if (response.statusText) sanitized.statusText = response.statusText;
        if (response.ok) sanitized.ok = response.ok;
        if (response.headers) sanitized.headers = 'present';
        if (response.size) sanitized.size = response.size;
        if (response.type) sanitized.type = response.type;
        
        // Include a small metadata about the actual data
        if (response.data) {
          if (Array.isArray(response.data)) {
            sanitized.data = `Array with ${response.data.length} items`;
          } else if (typeof response.data === 'object') {
            sanitized.data = `Object with keys: ${Object.keys(response.data).join(', ')}`;
          } else {
            sanitized.data = `${typeof response.data} present`;
          }
        }
        
        return sanitized;
      }
      
      return `${typeof response} present`;
    } catch (e) {
      return 'Error sanitizing response';
    }
  }

  /**
   * Sanitize props for logging
   */
  private sanitizePropsForLogging(props: Record<string, any>): Record<string, any> {
    // We want to avoid logging sensitive props like passwords, tokens, etc.
    const sanitized: Record<string, any> = {};
    
    for (const key in props) {
      // Skip sensitive props
      if (['password', 'token', 'secret', 'auth', 'authorization', 'key'].includes(key.toLowerCase())) {
        sanitized[key] = '[REDACTED]';
        continue;
      }
      
      // For arrays and objects, just log the type and size
      if (Array.isArray(props[key])) {
        sanitized[key] = `Array with ${props[key].length} items`;
      } else if (typeof props[key] === 'object' && props[key] !== null) {
        sanitized[key] = `Object with keys: ${Object.keys(props[key]).join(', ')}`;
      } else {
        // For simple values, log as is (but truncate large strings)
        if (typeof props[key] === 'string' && props[key].length > 100) {
          sanitized[key] = `${props[key].substring(0, 100)}... (truncated)`;
        } else {
          sanitized[key] = props[key];
        }
      }
    }
    
    return sanitized;
  }

  /**
   * Remove oldest record when cache is full
   */
  private removeOldestRecord(): void {
    if (this.records.size === 0) return;
    
    let oldestKey: string | null = null;
    let oldestTime = Infinity;
    
    this.records.forEach((record, key) => {
      if (record.startTime < oldestTime) {
        oldestTime = record.startTime;
        oldestKey = key;
      }
    });
    
    if (oldestKey) {
      this.records.delete(oldestKey);
    }
  }

  /**
   * Save records to localStorage
   */
  private saveToStorage(): void {
    if (!this.config.persistentStorage) return;
    
    try {
      // Only save completed records
      const completedRecords = Array.from(this.records.values())
        .filter(r => r.endTime !== null)
        // Limit to most recent records to avoid localStorage size limits
        .sort((a, b) => (b.endTime || 0) - (a.endTime || 0))
        .slice(0, 50);
      
      localStorage.setItem('performance_records', JSON.stringify(completedRecords));
    } catch (e) {
      console.error('Error saving performance records to localStorage:', e);
    }
  }

  /**
   * Load records from localStorage
   */
  private loadFromStorage(): void {
    if (!this.config.persistentStorage) return;
    
    try {
      const stored = localStorage.getItem('performance_records');
      if (stored) {
        const records = JSON.parse(stored);
        
        if (Array.isArray(records)) {
          records.forEach(record => {
            this.records.set(record.id, record);
          });
          this.debugLog(`Loaded ${records.length} performance records from localStorage`);
        }
      }
    } catch (e) {
      console.error('Error loading performance records from localStorage:', e);
    }
  }

  /**
   * Clean up old records periodically
   */
  private cleanup(): void {
    // Keep only records from the last hour
    const oneHourAgo = performance.now() - (60 * 60 * 1000);
    let removedCount = 0;
    
    this.records.forEach((record, key) => {
      if (record.startTime < oneHourAgo) {
        this.records.delete(key);
        removedCount++;
      }
    });
    
    if (removedCount > 0) {
      this.debugLog(`Cleaned up ${removedCount} old performance records`);
      this.saveToStorage();
    }
  }

  /**
   * Debug log
   */
  private debugLog(message: string): void {
    if (this.config.debugLog) {
      console.log(`%c[PerformanceMonitor] ${message}`, 'color: #3498db');
    }
  }
}

// Create and export singleton instance
export const performanceMonitor = PerformanceMonitor.getInstance();

// API wrapper for monitoring
export const monitorApiRequest = <T>(
  url: string, 
  requestFn: () => Promise<T>, 
  metadata: Record<string, any> = {}
): Promise<T> => {
  return performanceMonitor.measureApiRequest(url, requestFn, metadata);
};

// Component performance HOC (for class components)
export function withPerformanceTracking(componentName: string) {
  return function<P>(Component: React.ComponentType<P>) {
    return class PerformanceTrackedComponent extends React.Component<P> {
      private trackingId: string = '';
      
      componentDidMount() {
        this.trackingId = performanceMonitor.startComponentRender(componentName, this.props);
        performanceMonitor.endComponentRender(this.trackingId);
      }
      
      componentDidUpdate(prevProps: P) {
        this.trackingId = performanceMonitor.startComponentRender(componentName, this.props);
        performanceMonitor.endComponentRender(this.trackingId);
      }
      
      render() {
        return <Component {...this.props} />;
      }
    };
  };
}

// Hook for functional components
export function usePerformanceTracking(componentName: string, deps: any[] = []) {
  const trackingIdRef = React.useRef('');
  
  // Track initial render
  React.useEffect(() => {
    trackingIdRef.current = performanceMonitor.startComponentRender(componentName);
    
    return () => {
      if (trackingIdRef.current) {
        performanceMonitor.endComponentRender(trackingIdRef.current);
        trackingIdRef.current = '';
      }
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []); // Only on mount/unmount
  
  // Track updates
  React.useEffect(() => {
    if (deps.length > 0) { // Skip the first render
      const updateTrackingId = performanceMonitor.startComponentRender(`${componentName}.update`);
      performanceMonitor.endComponentRender(updateTrackingId);
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);
}

export default performanceMonitor; 
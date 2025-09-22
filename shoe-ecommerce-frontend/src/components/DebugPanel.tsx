import React, { useState, useEffect } from 'react';
import { getPerformanceMetrics, clearPerformanceCache, getPerformanceConfig, updatePerformanceConfig } from '../utils/performance';

interface RequestLog {
  url: string;
  status: number;
  method: string;
  time: Date;
  success: boolean;
}

// 选项卡类型
type TabType = 'requests' | 'performance' | 'settings';

const DebugPanel: React.FC = () => {
  const [visible, setVisible] = useState(false);
  const [logs, setLogs] = useState<RequestLog[]>([]);
  const [xhrPatched, setXhrPatched] = useState(false);
  const [activeTab, setActiveTab] = useState<TabType>('requests');
  const [metrics, setMetrics] = useState(getPerformanceMetrics());
  const [config, setConfig] = useState(getPerformanceConfig());
  
  // 定期更新性能指标
  useEffect(() => {
    if (!visible) return;
    
    const intervalId = setInterval(() => {
      setMetrics(getPerformanceMetrics());
    }, 2000);
    
    return () => clearInterval(intervalId);
  }, [visible]);

  // 初始化时拦截XMLHttpRequest以记录API请求
  useEffect(() => {
    if (xhrPatched) return;

    const originalXhrOpen = XMLHttpRequest.prototype.open;
    const originalXhrSend = XMLHttpRequest.prototype.send;

    // 拦截XHR open方法
    XMLHttpRequest.prototype.open = function(method: string, url: string) {
      // @ts-ignore
      this._requestMethod = method;
      // @ts-ignore
      this._requestUrl = url;
      return originalXhrOpen.apply(this, arguments as any);
    };

    // 拦截XHR send方法
    XMLHttpRequest.prototype.send = function(body) {
      // @ts-ignore
      const method = this._requestMethod;
      // @ts-ignore
      const url = this._requestUrl;
      const startTime = new Date();

      // 监听加载完成事件
      this.addEventListener('load', function() {
        const status = this.status;
        const endTime = new Date();
        const success = status >= 200 && status < 300;

        // 仅记录API请求
        if (url.includes('/api/')) {
          setLogs(prev => [
            {
              url,
              status,
              method,
              time: endTime,
              success
            },
            ...prev.slice(0, 19) // 最多保留20条记录
          ]);
        }
      });

      return originalXhrSend.apply(this, arguments as any);
    };

    setXhrPatched(true);
    console.log('XMLHttpRequest patched for debugging');
  }, [xhrPatched]);

  // 更新配置设置
  const handleConfigChange = (key: string, value: any) => {
    const updates = { [key]: value };
    updatePerformanceConfig(updates);
    setConfig(getPerformanceConfig());
  };

  if (!visible) {
    return (
      <button 
        onClick={() => setVisible(true)}
        className="fixed bottom-4 right-4 bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center shadow-lg z-50"
      >
        <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </button>
    );
  }

  return (
    <div className="fixed bottom-4 right-4 bg-white border border-gray-300 rounded-lg shadow-xl z-50 w-96 max-h-[80vh] overflow-hidden flex flex-col">
      <div className="bg-gray-100 px-4 py-2 flex justify-between items-center border-b border-gray-300">
        <h3 className="font-medium">开发者工具面板</h3>
        <button 
          onClick={() => setVisible(false)}
          className="text-gray-500 hover:text-gray-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      {/* 选项卡导航 */}
      <div className="flex border-b border-gray-300">
        <button 
          className={`flex-1 py-2 text-sm font-medium ${activeTab === 'requests' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'}`}
          onClick={() => setActiveTab('requests')}
        >
          请求
        </button>
        <button 
          className={`flex-1 py-2 text-sm font-medium ${activeTab === 'performance' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'}`}
          onClick={() => setActiveTab('performance')}
        >
          性能
        </button>
        <button 
          className={`flex-1 py-2 text-sm font-medium ${activeTab === 'settings' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'}`}
          onClick={() => setActiveTab('settings')}
        >
          设置
        </button>
      </div>
      
      {/* 请求记录选项卡 */}
      {activeTab === 'requests' && (
        <>
          <div className="overflow-y-auto flex-grow">
            {logs.length === 0 ? (
              <div className="p-4 text-gray-500 text-center">暂无API请求记录</div>
            ) : (
              <div className="divide-y divide-gray-200">
                {logs.map((log, idx) => (
                  <div key={idx} className="p-3 text-sm hover:bg-gray-50">
                    <div className="flex justify-between items-start">
                      <div className="font-medium truncate max-w-[220px]" title={log.url}>
                        {log.url.replace(/^.*\/api\//, '/api/')}
                      </div>
                      <span className={`px-2 py-1 rounded-full text-xs ${log.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                        {log.status}
                      </span>
                    </div>
                    <div className="mt-1 flex justify-between text-xs text-gray-500">
                      <span>{log.method}</span>
                      <span>{log.time.toLocaleTimeString()}</span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
          
          <div className="border-t border-gray-300 p-2 flex justify-end">
            <button
              onClick={() => setLogs([])}
              className="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300"
            >
              清除记录
            </button>
          </div>
        </>
      )}
      
      {/* 性能指标选项卡 */}
      {activeTab === 'performance' && (
        <>
          <div className="overflow-y-auto flex-grow p-4">
            <div className="space-y-4">
              {/* 性能摘要 */}
              <div className="bg-blue-50 p-3 rounded-lg">
                <h4 className="font-medium text-blue-800 mb-2">性能摘要</h4>
                <div className="grid grid-cols-2 gap-2 text-sm">
                  <div>
                    <p className="text-gray-500">API调用总数</p>
                    <p className="font-medium">{metrics.summary.totalApiCalls}</p>
                  </div>
                  <div>
                    <p className="text-gray-500">平均响应时间</p>
                    <p className="font-medium">{metrics.summary.averageResponseTime.toFixed(0)} ms</p>
                  </div>
                  <div>
                    <p className="text-gray-500">最长响应时间</p>
                    <p className="font-medium">{metrics.summary.maxResponseTime.toFixed(0)} ms</p>
                  </div>
                  <div>
                    <p className="text-gray-500">慢响应数量</p>
                    <p className="font-medium">{metrics.summary.totalSlowResponses}</p>
                  </div>
                </div>
              </div>
              
              {/* 慢响应 */}
              {metrics.slowResponses.length > 0 && (
                <div>
                  <h4 className="font-medium text-gray-700 mb-2">慢响应 <span className="text-xs text-gray-500">(&gt; {config.slowResponseThresholdMs}ms)</span></h4>
                  <div className="space-y-2">
                    {metrics.slowResponses.slice(0, 5).map((log, idx) => (
                      <div key={idx} className="bg-yellow-50 p-2 rounded border border-yellow-200 text-sm">
                        <div className="font-medium truncate" title={log.url}>
                          {log.url.replace(/^.*\/api\//, '/api/')}
                        </div>
                        <div className="flex justify-between text-xs mt-1">
                          <span className="text-yellow-700">{log.time.toFixed(0)} ms</span>
                          <span className="text-gray-500">{log.timestamp.toLocaleTimeString()}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
              
              {/* 错误记录 */}
              {metrics.errors.length > 0 && (
                <div>
                  <h4 className="font-medium text-gray-700 mb-2">错误记录</h4>
                  <div className="space-y-2">
                    {metrics.errors.slice(0, 5).map((error, idx) => (
                      <div key={idx} className="bg-red-50 p-2 rounded border border-red-200 text-sm">
                        <div className="font-medium text-red-700">{error.type}</div>
                        <div className="truncate text-gray-700" title={error.message}>
                          {error.message}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                          {error.timestamp.toLocaleTimeString()}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
          
          <div className="border-t border-gray-300 p-2 flex justify-end space-x-2">
            <button
              onClick={() => setMetrics(getPerformanceMetrics())}
              className="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200"
            >
              刷新数据
            </button>
            <button
              onClick={() => clearPerformanceCache('all')}
              className="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300"
            >
              清除缓存
            </button>
          </div>
        </>
      )}
      
      {/* 设置选项卡 */}
      {activeTab === 'settings' && (
        <>
          <div className="overflow-y-auto flex-grow p-4">
            <div className="space-y-4">
              {/* 调试设置 */}
              <div>
                <h4 className="font-medium text-gray-700 mb-2">调试设置</h4>
                <div className="space-y-2">
                  <div>
                    <label className="flex items-center">
                      <span className="text-sm text-gray-700 mr-2">调试级别:</span>
                      <select 
                        value={config.debugLevel}
                        onChange={(e) => handleConfigChange('debugLevel', parseInt(e.target.value))}
                        className="form-select text-sm rounded border-gray-300"
                      >
                        <option value={0}>关闭</option>
                        <option value={1}>仅错误</option>
                        <option value={2}>详细</option>
                      </select>
                    </label>
                  </div>
                  
                  <div>
                    <label className="flex items-center">
                      <input 
                        type="checkbox" 
                        checked={config.enableMonitoring}
                        onChange={(e) => handleConfigChange('enableMonitoring', e.target.checked)}
                        className="form-checkbox rounded text-blue-600"
                      />
                      <span className="text-sm text-gray-700 ml-2">启用性能监控</span>
                    </label>
                  </div>
                </div>
              </div>
              
              {/* 缓存设置 */}
              <div>
                <h4 className="font-medium text-gray-700 mb-2">缓存设置</h4>
                <div className="space-y-2">
                  <div>
                    <label className="flex items-center">
                      <input 
                        type="checkbox" 
                        checked={config.cacheApiResponses}
                        onChange={(e) => handleConfigChange('cacheApiResponses', e.target.checked)}
                        className="form-checkbox rounded text-blue-600"
                      />
                      <span className="text-sm text-gray-700 ml-2">缓存API响应</span>
                    </label>
                  </div>
                  
                  <div>
                    <label className="flex items-center">
                      <span className="text-sm text-gray-700 mr-2">缓存时间(分钟):</span>
                      <input 
                        type="number" 
                        value={config.cacheMaxAge / (60 * 1000)}
                        onChange={(e) => handleConfigChange('cacheMaxAge', parseInt(e.target.value) * 60 * 1000)}
                        min="1" 
                        max="60"
                        className="form-input w-16 text-sm rounded border-gray-300"
                      />
                    </label>
                  </div>
                  
                  <div>
                    <label className="flex items-center">
                      <input 
                        type="checkbox" 
                        checked={config.clearCacheOnLogout}
                        onChange={(e) => handleConfigChange('clearCacheOnLogout', e.target.checked)}
                        className="form-checkbox rounded text-blue-600"
                      />
                      <span className="text-sm text-gray-700 ml-2">登出时清除缓存</span>
                    </label>
                  </div>
                </div>
              </div>
              
              {/* 会话设置 */}
              <div>
                <h4 className="font-medium text-gray-700 mb-2">会话设置</h4>
                <div className="space-y-2">
                  <div>
                    <label className="flex items-center">
                      <span className="text-sm text-gray-700 mr-2">检查频率(分钟):</span>
                      <input 
                        type="number" 
                        value={config.sessionCheckFrequencyMs / (60 * 1000)}
                        onChange={(e) => handleConfigChange('sessionCheckFrequencyMs', parseInt(e.target.value) * 60 * 1000)}
                        min="1" 
                        max="30"
                        className="form-input w-16 text-sm rounded border-gray-300"
                      />
                    </label>
                  </div>
                  
                  <div>
                    <label className="flex items-center">
                      <span className="text-sm text-gray-700 mr-2">Ping频率(分钟):</span>
                      <input 
                        type="number" 
                        value={config.sessionPingFrequencyMs / (60 * 1000)}
                        onChange={(e) => handleConfigChange('sessionPingFrequencyMs', parseInt(e.target.value) * 60 * 1000)}
                        min="1" 
                        max="60"
                        className="form-input w-16 text-sm rounded border-gray-300"
                      />
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div className="border-t border-gray-300 p-2 flex justify-end space-x-2">
            <button
              onClick={() => {
                updatePerformanceConfig({ ...getPerformanceConfig() });
                setConfig(getPerformanceConfig());
              }}
              className="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200"
            >
              应用配置
            </button>
          </div>
        </>
      )}
    </div>
  );
};

export default DebugPanel; 
import React, { useState } from 'react';
import { useHomepageSettings } from '../contexts/HomepageSettingsContext';
import homepageService from '../services/homepageService';
import { toast } from 'react-toastify';

/**
 * 开发工具组件 - 仅在开发环境中使用
 * 提供各种调试功能和状态查看
 */
const DevTools: React.FC = () => {
  const { settings, refreshSettings } = useHomepageSettings();
  const [isExpanded, setIsExpanded] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  // 仅在开发环境中显示
  if (process.env.NODE_ENV !== 'development') {
    return null;
  }

  // 强制刷新设置
  const handleForceRefresh = async () => {
    try {
      setIsLoading(true);
      await refreshSettings();
      toast.success('设置已刷新');
    } catch (error) {
      console.error('刷新设置失败', error);
      toast.error('刷新设置失败: ' + (error instanceof Error ? error.message : String(error)));
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="fixed bottom-4 left-4 z-50">
      <div className="bg-gray-800 text-white p-2 rounded-md shadow-lg">
        <button 
          onClick={() => setIsExpanded(!isExpanded)}
          className="flex items-center justify-between w-full"
        >
          <span>🛠️ 开发工具</span>
          <span>{isExpanded ? '▼' : '▶'}</span>
        </button>
        
        {isExpanded && (
          <div className="mt-2 space-y-2">
            <div className="flex space-x-2">
              <button
                onClick={handleForceRefresh}
                className="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs"
                disabled={isLoading}
              >
                强制刷新设置
              </button>
            </div>
            
            {isLoading && (
              <div className="text-center py-2">
                <span className="animate-pulse">加载中...</span>
              </div>
            )}
            
            <div className="mt-3 pt-2 border-t border-gray-700">
              <h4 className="text-sm font-medium mb-1">当前设置 (HomepageSettingsContext)</h4>
              <pre className="text-xs bg-gray-900 p-2 rounded max-h-40 overflow-y-auto">
                {JSON.stringify(settings, null, 2)}
              </pre>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default DevTools; 
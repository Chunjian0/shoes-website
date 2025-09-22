import React from 'react';
import { useHomepageSettings } from '../contexts/HomepageSettingsContext';
import { SettingsUpdateEventType } from '../types/homepage';

/**
 * 首页设置状态组件 - 显示设置的加载状态和最后更新时间
 * 通常用于开发环境调试
 */
const HomepageSettingsStatus: React.FC = () => {
  const { loading, error, lastUpdate, refreshSettings } = useHomepageSettings();

  // 格式化更新时间
  const formatTimestamp = (timestamp: number) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString();
  };

  // 获取更新类型的中文显示
  const getUpdateTypeText = (type: SettingsUpdateEventType | string) => {
    switch (type) {
      case SettingsUpdateEventType.ALL_UPDATED:
        return '全部刷新';
      case SettingsUpdateEventType.BANNER_UPDATED:
        return '横幅更新';
      case SettingsUpdateEventType.CAROUSEL_UPDATED:
        return '轮播更新';
      case SettingsUpdateEventType.FEATURED_UPDATED:
        return '精选产品更新';
      case SettingsUpdateEventType.NEW_ARRIVAL_UPDATED:
        return '新品更新';
      case SettingsUpdateEventType.SALE_UPDATED:
        return '促销更新';
      case SettingsUpdateEventType.OFFER_UPDATED:
        return '优惠更新';
      default:
        console.warn(`[HomepageSettingsStatus] Unknown update event type: ${type}`);
        return `未知更新 (${type})`;
    }
  };

  // 仅在开发环境中显示
  if (process.env.NODE_ENV !== 'development') {
    return null;
  }

  return (
    <div className="fixed bottom-4 right-4 bg-white border border-gray-200 rounded-md shadow-md p-3 text-xs z-50 max-w-xs">
      <div className="flex items-center justify-between mb-2">
        <h4 className="font-semibold text-gray-700">首页设置状态</h4>
        <button 
          onClick={() => refreshSettings()}
          className="bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs transition-colors"
        >
          刷新
        </button>
      </div>
      
      {loading && (
        <div className="text-blue-600 flex items-center mb-1">
          <svg className="animate-spin -ml-1 mr-2 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          加载中...
        </div>
      )}
      
      {error && (
        <div className="text-red-600 mb-1">
          错误: {error.message}
        </div>
      )}
      
      {lastUpdate && (
        <div className="text-gray-600">
          <div>最后更新: {formatTimestamp(lastUpdate.timestamp)}</div>
          <div>更新类型: {getUpdateTypeText(lastUpdate.type as SettingsUpdateEventType)}</div>
        </div>
      )}
    </div>
  );
};

export default HomepageSettingsStatus; 
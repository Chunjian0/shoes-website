import React, { useState, useEffect, useCallback } from 'react';
import { useHomepageSettings } from '../contexts/HomepageSettingsContext';
import homepageService from '../services/homepageService';
import { LayoutTemplate, HomepageSettings } from '../types/homepage';
import { toast } from 'react-toastify';

// 添加一个自定义事件用于模板切换，并导出供其他组件使用
export const TEMPLATE_CHANGE_EVENT = 'template_change';

// 派发模板变更事件的辅助函数
const dispatchTemplateChangeEvent = (template: LayoutTemplate, settings: any) => {
  const event = new CustomEvent(TEMPLATE_CHANGE_EVENT, { 
    detail: { template, settings }
  });
  window.dispatchEvent(event);
};

/**
 * 调试控制组件，漂浮在页面右下角
 */
const DebugControls: React.FC = () => {
  const { settings, refreshSettings } = useHomepageSettings();
  const [isExpanded, setIsExpanded] = useState(false);
  const [selectedLayout, setSelectedLayout] = useState<string>(settings?.active_template || LayoutTemplate.MODERN);
  const [isLoading, setIsLoading] = useState(false);

  // 根据设置更新选中的布局
  useEffect(() => {
    if (settings?.active_template) {
      setSelectedLayout(settings.active_template);
    }
  }, [settings]);

  const handleApplyTemplate = useCallback(async (template: LayoutTemplate) => {
    const currentSettings = settings;
    if (!currentSettings) {
        console.error("应用模板失败: HomepageSettingsContext 中的设置为空。");
        toast.error("应用模板失败，无法获取当前设置。请刷新页面重试。");
        return;
    }

    try {
      const updatedSettings: HomepageSettings = {
        ...currentSettings,
        active_template: template,
        layout: template,
      };

      refreshSettings();

      toast.success(`模板 ${template} 应用成功！(未持久化)`);
    } catch (error) {
      console.error("应用模板失败:", error);
      toast.error("应用模板时发生未知错误。");
    }
  }, [settings, refreshSettings]);

  // 切换布局模板
  const handleTemplateChange = async (template: LayoutTemplate) => {
    if (isLoading) return;
    setIsLoading(true);
    setSelectedLayout(template);
    
    try {
      await handleApplyTemplate(template);
      dispatchTemplateChangeEvent(template, settings);
    } catch (error) {
      console.error('切换模板失败:', error);
      toast.error('切换模板失败');
    } finally {
      setIsLoading(false);
    }
  };

  // Helper function to call handleTemplateChange
  const changeLayout = (template: LayoutTemplate) => {
    handleTemplateChange(template);
  };

  return (
    <div className="fixed bottom-5 right-5 z-50">
      {/* 折叠/展开按钮 */}
      <button
        onClick={() => setIsExpanded(!isExpanded)}
        className="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg flex items-center justify-center"
        title={isExpanded ? '收起调试面板' : '展开调试面板'}
      >
        <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </button>

      {/* 调试面板 */}
      {isExpanded && (
        <div className="bg-white rounded-lg shadow-xl p-4 mt-4 w-96 max-h-[80vh] overflow-auto">
          <h2 className="text-lg font-bold mb-4 text-gray-800">调试控制面板</h2>
          
          {/* 模板切换部分 */}
          <div className="mb-4 p-3 bg-gray-50 rounded-lg">
            <h3 className="font-medium text-gray-700 mb-2">模板切换</h3>
            <div className="flex flex-wrap gap-2 mb-2">
              <button 
                onClick={() => changeLayout(LayoutTemplate.MODERN)}
                className={`px-3 py-1 rounded text-sm ${selectedLayout === LayoutTemplate.MODERN ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
              >
                Modern
              </button>
              <button 
                onClick={() => changeLayout(LayoutTemplate.CLASSIC)}
                className={`px-3 py-1 rounded text-sm ${selectedLayout === LayoutTemplate.CLASSIC ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
              >
                Classic
              </button>
              <button 
                onClick={() => changeLayout(LayoutTemplate.MINIMAL)}
                className={`px-3 py-1 rounded text-sm ${selectedLayout === LayoutTemplate.MINIMAL ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
              >
                Minimal
              </button>
              <button 
                onClick={() => changeLayout(LayoutTemplate.BOLD)}
                className={`px-3 py-1 rounded text-sm ${selectedLayout === LayoutTemplate.BOLD ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`}
              >
                Bold
              </button>
            </div>
            {isLoading && <p className="text-xs text-gray-500 italic animate-pulse">切换中...</p>}
          </div>
        </div>
      )}
    </div>
  );
};

export default DebugControls; 
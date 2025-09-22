import React, { useState, useEffect, useCallback } from 'react';
import { toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { HomepageSettings, LayoutTemplate } from '../types/homepage';
import MinimalTemplate from '../components/templates/MinimalTemplate';
import { TemplateProps } from '../components/templates/TemplateProps';
import homepageService from '../services/homepageService';
import { ProductTemplate } from '../types/apiTypes';
import LoadingIndicator from '../components/LoadingIndicator';

// 防抖函数
const debounce = (fn: Function, ms = 300) => {
  let timeoutId: ReturnType<typeof setTimeout>;
  return function(this: any, ...args: any[]) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), ms);
  };
};

// 简单的加载指示器组件
const LoadingSpinner: React.FC<{ size?: 'small' | 'medium' | 'large' }> = ({ size = 'medium' }) => {
  const sizeClass = {
    small: 'h-8 w-8',
    medium: 'h-12 w-12',
    large: 'h-16 w-16'
  };

  return (
    <div className="flex justify-center items-center">
      <div className={`animate-spin rounded-full border-t-2 border-b-2 border-blue-500 ${sizeClass[size]}`}></div>
    </div>
  );
};

const HomePage: React.FC = () => {
  // 状态管理
  const [settings, setSettings] = useState<HomepageSettings | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // 产品数据
  // const [featuredProducts, setFeaturedProducts] = useState<any[]>([]);
  // const [newProducts, setNewProducts] = useState<any[]>([]);
  // const [saleProducts, setSaleProducts] = useState<any[]>([]);
  const [categories, setCategories] = useState<any[]>([]);
  
  // 加载状态
  const [loadingStates, setLoadingStates] = useState({
    featuredProducts: false,
    newProducts: false,
    saleProducts: false,
    categories: false
  });
  
  // 新增状态：API数据是否已准备就绪
  const [apiDataReady, setApiDataReady] = useState(false);
  
  // 错误状态
  const [errors, setErrors] = useState<{
    featuredProducts: string | null,
    newProducts: string | null,
    saleProducts: string | null,
    categories: string | null
  }>({
    featuredProducts: null,
    newProducts: null,
    saleProducts: null,
    categories: null
  });

  // 添加模板数据的状态
  const [templates, setTemplates] = useState<{
    featured: ProductTemplate[],
    newArrival: ProductTemplate[],
    sale: ProductTemplate[]
  }>({
    featured: [],
    newArrival: [],
    sale: []
  });

  // 加载首页数据函数
  const loadHomePageData = useCallback(async (forceRefresh: boolean = false) => {
    try {
      // 设置加载状态
      setIsLoading(true);
      setApiDataReady(false);
      
      setLoadingStates({
        featuredProducts: true,
        newProducts: true,
        saleProducts: true,
        categories: true
      });
      
      console.log(`Starting to load homepage data${forceRefresh ? ' (forced refresh)' : ''}`);
      
      // 使用getHomePageData方法获取所有数据
      const {
        settings: apiSettings,
        templates: templateData
      } = await homepageService.getHomePageData(forceRefresh);
      
      console.log('API data loaded:', {
        settingsLoaded: !!apiSettings,
        featuredCount: templates.featured.length || 0,
        newCount: templates.newArrival.length || 0,
        saleCount: templates.sale.length || 0
      });
      
      // 确保设置存在
      if (!apiSettings) {
        throw new Error('Failed to get page settings data');
      }
      
      // 更新所有状态
      const updatedSettings: HomepageSettings = {
        ...apiSettings,
        active_template: LayoutTemplate.MINIMAL,
      };
      
      setSettings(updatedSettings);
      
      console.log(`Using fixed Minimal template`);
      
      // 存储模板数据
      setTemplates(templateData);
      
      // 标记API数据已准备就绪
      setApiDataReady(true);
      
      // 清除错误状态
      setError(null);
      setErrors({
        featuredProducts: null,
        newProducts: null,
        saleProducts: null,
        categories: null
      });
    } catch (err) {
      console.error('Failed to load homepage data:', err);
      const errorMessage = err instanceof Error ? err.message : 'Failed to load homepage data. Please try again.';
      setError(errorMessage);
      toast.error(errorMessage);
      
      // 即使加载失败，也标记为API数据已准备（允许显示错误信息）
      setApiDataReady(true);
    } finally {
      setIsLoading(false);
      setLoadingStates({
        featuredProducts: false,
        newProducts: false,
        saleProducts: false,
        categories: false
      });
    }
  }, []);

  // 初始化和事件监听
  useEffect(() => {
    // 初始化时加载数据
    loadHomePageData(false); // 默认不强制刷新
  }, [loadHomePageData]);

  // 在API数据准备好之前显示加载指示器
  if (!apiDataReady || isLoading) {
    return (
      <div className="flex flex-col items-center justify-center h-screen">
        <LoadingSpinner size="large" />
        <p className="mt-4 text-gray-600">Loading page content...</p>
      </div>
    );
  }

  // 错误状态
  if (error || !settings) {
    return (
      <div className="text-center p-8">
        <h2 className="text-xl text-red-600">Error</h2>
        <p className="mt-2">{error || 'Failed to load homepage settings'}</p>
        <button 
          className="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          onClick={() => loadHomePageData(true)}
        >
          Retry
        </button>
      </div>
    );
  }

  // 模板属性
  const templateProps: TemplateProps = {
    settings,
    categories,
    isLoading: loadingStates,
    errors,
    templates
  };

  return (
    <div className="relative">
      {isLoading && !apiDataReady && <LoadingIndicator />}
      {error && <div className="text-red-500 text-center p-4">Error: {error}</div>}
      {settings && apiDataReady ? (
        <MinimalTemplate
          settings={settings}
          categories={categories}
          isLoading={loadingStates}
          errors={errors}
          templates={templates}
        />
      ) : (
        !error && <LoadingIndicator />
      )}
    </div>
  );
};

export default HomePage; 
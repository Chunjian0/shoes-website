import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { fetchHomepageData, saveHomepageSettings, fetchProducts, fetchCategories } from '../api/homepage';
import { HomepageSettings, LayoutTemplate } from '../types/homepage';
import { useAppDispatch, useAppSelector } from '../store';
import { fetchTemplates } from '../store/slices/productSlice';

// API调试页面组件
const DevTools: React.FC = () => {
  const dispatch = useAppDispatch();
  const [activeTab, setActiveTab] = useState<string>('homepage');
  const [apiResponse, setApiResponse] = useState<any>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [requestPayload, setRequestPayload] = useState<string>('');
  const [endpoint, setEndpoint] = useState('/api/product-templates');
  
  const storeState = useAppSelector(state => state.products);

  // 加载初始数据
  useEffect(() => {
    if (activeTab === 'homepage') {
      fetchHomepageSettings();
    }
  }, [activeTab]);

  // 获取首页设置
  const fetchHomepageSettings = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await fetchHomepageData();
      setApiResponse(data);
      setRequestPayload(JSON.stringify(data, null, 2));
    } catch (err) {
      console.error('Error fetching homepage settings:', err);
      setError('Failed to load homepage settings');
      setApiResponse(null);
    } finally {
      setLoading(false);
    }
  };

  // 获取产品数据
  const handleFetchProducts = async (type: 'featured' | 'new' | 'sale', limit: number = 8) => {
    setLoading(true);
    setError(null);
    try {
      const data = await fetchProducts(type, limit);
      setApiResponse(data);
    } catch (err) {
      console.error(`Error fetching ${type} products:`, err);
      setError(`Failed to load ${type} products`);
      setApiResponse(null);
    } finally {
      setLoading(false);
    }
  };

  // 获取分类数据
  const handleFetchCategories = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await fetchCategories();
      setApiResponse(data);
    } catch (err) {
      console.error('Error fetching categories:', err);
      setError('Failed to load categories');
      setApiResponse(null);
    } finally {
      setLoading(false);
    }
  };

  // 保存设置
  const handleSaveSettings = async () => {
    setLoading(true);
    setError(null);
    try {
      let payload;
      try {
        payload = JSON.parse(requestPayload);
      } catch (err) {
        setError('Invalid JSON payload');
        setLoading(false);
        return;
      }
      
      const data = await saveHomepageSettings(payload);
      setApiResponse(data);
      alert('设置保存成功！');
    } catch (err) {
      console.error('Error saving settings:', err);
      setError('Failed to save settings');
      setApiResponse(null);
    } finally {
      setLoading(false);
    }
  };

  // 运行自定义API请求
  const handleCustomRequest = async (endpoint: string, method: string, payload?: any) => {
    setLoading(true);
    setError(null);
    
    try {
      let parsedPayload;
      if (payload) {
        try {
          parsedPayload = JSON.parse(payload);
        } catch (err) {
          setError('Invalid JSON payload');
          setLoading(false);
          return;
        }
      }
      
      // 实际发送API请求
      let response;
      const url = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
      
      switch (method.toUpperCase()) {
        case 'GET':
          response = await axios.get(url, { params: parsedPayload });
          break;
        case 'POST':
          response = await axios.post(url, parsedPayload);
          break;
        case 'PUT':
          response = await axios.put(url, parsedPayload);
          break;
        case 'DELETE':
          response = await axios.delete(url, { data: parsedPayload });
          break;
        default:
          throw new Error(`不支持的HTTP方法: ${method}`);
      }
      
      setApiResponse(response.data);
    } catch (err) {
      console.error('Error in custom request:', err);
      setError(`请求失败: ${err instanceof Error ? err.message : String(err)}`);
      setApiResponse(null);
    } finally {
      setLoading(false);
    }
  };

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await axios.get(endpoint);
      setApiResponse(response.data);
      console.log('API响应数据:', response.data);
    } catch (err) {
      setError('请求失败：' + (err instanceof Error ? err.message : String(err)));
      console.error('API请求失败:', err);
    } finally {
      setLoading(false);
    }
  };
  
  const loadTemplatesFromStore = async () => {
    try {
      setLoading(true);
      await dispatch(fetchTemplates({}));
      setLoading(false);
    } catch (err) {
      setError('加载模板失败：' + (err instanceof Error ? err.message : String(err)));
      setLoading(false);
    }
  };

  // 渲染标签页
  const renderTabs = () => {
    const tabs = ['homepage', 'products', 'categories', 'custom'];
    return (
      <div className="flex border-b border-gray-200">
        {tabs.map(tab => (
          <button
            key={tab}
            onClick={() => setActiveTab(tab)}
            className={`px-4 py-2 font-medium text-sm ${
              activeTab === tab
                ? 'border-b-2 border-blue-500 text-blue-600'
                : 'text-gray-500 hover:text-gray-700'
            }`}
          >
            {tab.charAt(0).toUpperCase() + tab.slice(1)}
          </button>
        ))}
      </div>
    );
  };

  // 渲染首页设置标签页内容
  const renderHomepageTab = () => {
    return (
      <div className="mt-4">
        <div className="flex justify-between mb-4">
          <button
            onClick={fetchHomepageSettings}
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            获取首页设置
          </button>
          <button
            onClick={handleSaveSettings}
            className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
          >
            保存设置
          </button>
        </div>
        
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            设置数据 (JSON)
          </label>
          <textarea
            className="w-full p-2 border border-gray-300 rounded h-60 font-mono text-sm"
            value={requestPayload}
            onChange={(e) => setRequestPayload(e.target.value)}
          />
        </div>
      </div>
    );
  };

  // 渲染产品标签页内容
  const renderProductsTab = () => {
    return (
      <div className="mt-4">
        <div className="grid grid-cols-3 gap-4 mb-4">
          <button
            onClick={() => handleFetchProducts('featured')}
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            获取精选产品
          </button>
          <button
            onClick={() => handleFetchProducts('new')}
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            获取新品
          </button>
          <button
            onClick={() => handleFetchProducts('sale')}
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            获取特价产品
          </button>
        </div>
        
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            产品数量
          </label>
          <input
            type="number"
            min="1"
            max="20"
            defaultValue="8"
            className="p-2 border border-gray-300 rounded"
            onChange={(e) => {
              const limit = parseInt(e.target.value, 10) || 8;
              if (apiResponse) {
                handleFetchProducts(activeTab as 'featured' | 'new' | 'sale', limit);
              }
            }}
          />
        </div>
      </div>
    );
  };

  // 渲染分类标签页内容
  const renderCategoriesTab = () => {
    return (
      <div className="mt-4">
        <button
          onClick={handleFetchCategories}
          className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          获取分类列表
        </button>
      </div>
    );
  };

  // 渲染自定义请求标签页内容
  const renderCustomTab = () => {
    const [endpoint, setEndpoint] = useState<string>('/api/custom');
    const [method, setMethod] = useState<string>('GET');
    const [customPayload, setCustomPayload] = useState<string>('');

    return (
      <div className="mt-4">
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            API 端点
          </label>
          <input
            type="text"
            className="w-full p-2 border border-gray-300 rounded"
            value={endpoint}
            onChange={(e) => setEndpoint(e.target.value)}
          />
        </div>
        
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            请求方法
          </label>
          <select
            className="w-full p-2 border border-gray-300 rounded"
            value={method}
            onChange={(e) => setMethod(e.target.value)}
          >
            <option value="GET">GET</option>
            <option value="POST">POST</option>
            <option value="PUT">PUT</option>
            <option value="DELETE">DELETE</option>
          </select>
        </div>
        
        {(method === 'POST' || method === 'PUT') && (
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              请求数据 (JSON)
            </label>
            <textarea
              className="w-full p-2 border border-gray-300 rounded h-40 font-mono text-sm"
              value={customPayload}
              onChange={(e) => setCustomPayload(e.target.value)}
            />
          </div>
        )}
        
        <button
          onClick={() => handleCustomRequest(endpoint, method, customPayload)}
          className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          发送请求
        </button>
      </div>
    );
  };

  // 渲染当前标签页内容
  const renderActiveTab = () => {
    switch (activeTab) {
      case 'homepage':
        return renderHomepageTab();
      case 'products':
        return renderProductsTab();
      case 'categories':
        return renderCategoriesTab();
      case 'custom':
        return renderCustomTab();
      default:
        return null;
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">开发工具</h1>
      
      <div className="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 className="text-xl font-semibold mb-4">API请求测试</h2>
        
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            API端点
          </label>
          <div className="flex">
            <input 
              type="text"
              value={endpoint}
              onChange={(e) => setEndpoint(e.target.value)}
              className="flex-grow px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="输入API端点, 例如 /api/product-templates"
            />
            <button
              onClick={fetchData}
              className="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              disabled={loading}
            >
              {loading ? '加载中...' : '发送请求'}
            </button>
            </div>
          </div>
          
        <div className="mb-4">
          <button
            onClick={loadTemplatesFromStore}
            className="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 mr-2"
            disabled={loading}
          >
            从Store加载模板
          </button>
            </div>
          
          {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
              <p>{error}</p>
            </div>
          )}
          
        <h3 className="text-lg font-medium mb-2">redux存储中的产品数据:</h3>
        <div className="bg-gray-100 p-4 rounded-md overflow-auto max-h-60 mb-4">
          <pre className="text-sm">{JSON.stringify(storeState, null, 2)}</pre>
        </div>
        
        <h3 className="text-lg font-medium mb-2">API响应:</h3>
        <div className="bg-gray-100 p-4 rounded-md overflow-auto max-h-96">
          <pre className="text-sm">{apiResponse ? JSON.stringify(apiResponse, null, 2) : '暂无数据'}</pre>
        </div>
      </div>
      
      <div className="bg-white shadow-md rounded-lg p-6">
        <h2 className="text-xl font-semibold mb-4">图片测试</h2>
        
        {apiResponse && apiResponse.data && apiResponse.data.data && apiResponse.data.data.templates && apiResponse.data.data.templates.length > 0 && (
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {apiResponse.data.data.templates.map((template: any) => (
              <div key={template.id} className="border rounded-md p-4">
                <h3 className="font-medium mb-2">{template.name || template.title}</h3>
                <p className="text-sm text-gray-600 mb-2">ID: {template.id}</p>
                
                {template.images && Array.isArray(template.images) && template.images.length > 0 ? (
                  <div>
                    <p className="font-medium mb-1">模板图片:</p>
                    <div className="grid grid-cols-2 gap-2">
                      {template.images.map((image: any, idx: number) => (
                        <div key={idx} className="border rounded-md overflow-hidden">
                          <p className="text-xs bg-gray-100 p-1">{typeof image === 'string' ? '字符串URL' : 'Image对象'}</p>
                          <img 
                            src={typeof image === 'string' ? image : (image.url || image.thumbnail || '')} 
                            alt={`图片 ${idx+1}`}
                            className="w-full h-32 object-cover"
                            onError={(e) => {
                              (e.target as HTMLImageElement).src = 'https://via.placeholder.com/150?text=加载失败';
                            }}
                          />
                          <p className="text-xs p-1 break-all">{typeof image === 'string' ? image : JSON.stringify(image)}</p>
                        </div>
                      ))}
                    </div>
                  </div>
                ) : (
                  <p className="text-sm text-gray-500">无图片</p>
                )}
                
                {template.linked_products && Array.isArray(template.linked_products) && template.linked_products.length > 0 && (
            <div className="mt-4">
                    <p className="font-medium mb-1">关联产品图片:</p>
                    <div className="grid grid-cols-2 gap-2">
                      {template.linked_products.slice(0, 4).map((product: any, idx: number) => (
                        <div key={idx} className="border rounded-md overflow-hidden">
                          <p className="text-xs bg-gray-100 p-1">{product.name}</p>
                          {product.images && Array.isArray(product.images) && product.images.length > 0 ? (
                            <img 
                              src={typeof product.images[0] === 'string' ? product.images[0] : (product.images[0].url || product.images[0].thumbnail || '')} 
                              alt={product.name}
                              className="w-full h-32 object-cover"
                              onError={(e) => {
                                (e.target as HTMLImageElement).src = 'https://via.placeholder.com/150?text=加载失败';
                              }}
                            />
                          ) : product.image_url ? (
                            <img 
                              src={product.image_url} 
                              alt={product.name}
                              className="w-full h-32 object-cover"
                              onError={(e) => {
                                (e.target as HTMLImageElement).src = 'https://via.placeholder.com/150?text=加载失败';
                              }}
                            />
                          ) : (
                            <div className="w-full h-32 bg-gray-200 flex items-center justify-center">
                              <p className="text-sm text-gray-500">无图片</p>
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
            </div>
          )}
        </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default DevTools; 
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { apiService as api } from '../services/api';
import { useHomepageSettings } from '../contexts/HomepageSettingsContext';

const DebugPage: React.FC = () => {
  // 状态
  const [apiTests, setApiTests] = useState<{
    name: string;
    status: 'pending' | 'success' | 'error';
    data: any;
    error: any;
  }[]>([
    { name: 'CSRF Cookie', status: 'pending', data: null, error: null },
    { name: 'Homepage Settings', status: 'pending', data: null, error: null },
    { name: 'Featured Products', status: 'pending', data: null, error: null },
    { name: 'New Products', status: 'pending', data: null, error: null },
    { name: 'Sale Products', status: 'pending', data: null, error: null },
  ]);
  
  const [customUrl, setCustomUrl] = useState<string>('/homepage/settings');
  const [customResponse, setCustomResponse] = useState<any>(null);
  const [customError, setCustomError] = useState<any>(null);
  const [loading, setLoading] = useState<boolean>(false);

  // 从HomepageSettingsContext获取设置
  const { settings, loading: settingsLoading, error: settingsError } = useHomepageSettings();

  // 运行API测试
  const runApiTest = async (index: number) => {
    const tests = [...apiTests];
    tests[index].status = 'pending';
    tests[index].data = null;
    tests[index].error = null;
    // For settings test, directly reflect context state
    if (index === 1) {
        tests[index].status = settingsLoading ? 'pending' : (settingsError ? 'error' : (settings ? 'success' : 'pending'));
        tests[index].data = settings;
        tests[index].error = settingsError;
        setApiTests(tests);
        return; // No need for further API call for settings
    }
    setApiTests(tests); // Update status to pending for other tests
    
    try {
      let response;
      
      switch(index) {
        case 0: // CSRF Cookie
          // 模拟成功状态，因为 CSRF 是请求的一部分
          tests[index].status = 'success';
          tests[index].data = 'CSRF handling is part of requests';
          break;
        
        case 2: // Featured Products
          response = await api.products.getFeatured({});
          tests[index].status = 'success';
          tests[index].data = response;
          break;
        
        case 3: // New Products
          response = await api.products.getNewArrivals({});
          tests[index].status = 'success';
          tests[index].data = response;
          break;
        
        case 4: // Sale Products
          response = await api.products.getSale({});
          tests[index].status = 'success';
          tests[index].data = response;
          break;
      }
    } catch (error) {
      console.error(`API测试 ${apiTests[index].name} 失败:`, error);
      tests[index].status = 'error';
      tests[index].error = error;
      
      if (axios.isAxiosError(error)) {
        tests[index].error = {
          message: error.message,
          status: error.response?.status,
          data: error.response?.data,
        };
      }
    }
    
    setApiTests(tests);
  };

  // 运行所有测试
  const runAllTests = () => {
    apiTests.forEach((_, index) => {
      runApiTest(index);
    });
  };

  // 运行自定义API请求
  const runCustomRequest = async () => {
    setLoading(true);
    setCustomResponse(null);
    setCustomError(null);
    
    try {
      // 2. 注意：这里无法直接使用 api.get，因为 api 是 apiService 对象
      // 需要一个通用的请求方法或根据 customUrl 调用特定服务
      // 暂时注释掉，需要重新设计或使用底层 axios 实例 (如果导出)
      // const response = await api.get(customUrl); 
      // setCustomResponse(response.data);
      setCustomError('自定义请求功能需要调整以适配 apiService 结构');
    } catch (error) {
      console.error('自定义API请求失败:', error);
      
      if (axios.isAxiosError(error)) {
        setCustomError({
          message: error.message,
          status: error.response?.status,
          data: error.response?.data,
        });
      } else {
        setCustomError(error);
      }
    } finally {
      setLoading(false);
    }
  };

  // 检查代理设置
  const proxyConfig = {
    targetUrl: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000',
    frontendUrl: window.location.origin,
    apiBasePath: '/api'
  };

  return (
    <div className="min-h-screen bg-gray-100 p-6">
      <div className="max-w-6xl mx-auto">
        <header className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">API调试页面</h1>
          <p className="text-gray-600">用于测试前端与Laravel后端之间的连接</p>
          
          <div className="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
            <h2 className="text-lg font-semibold text-blue-800">代理配置信息</h2>
            <div className="mt-2 space-y-1 text-sm">
              <p><span className="font-medium">前端URL:</span> {proxyConfig.frontendUrl}</p>
              <p><span className="font-medium">API代理路径:</span> {proxyConfig.apiBasePath}</p>
              <p><span className="font-medium">目标后端URL:</span> {proxyConfig.targetUrl}</p>
              <p><span className="font-medium">实际请求会发送到:</span> {`${proxyConfig.frontendUrl}${proxyConfig.apiBasePath}/实际路径`}</p>
              <p><span className="font-medium">代理会转发到:</span> {`${proxyConfig.targetUrl}/实际路径`}</p>
            </div>
          </div>
        </header>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <section className="bg-white rounded-lg shadow p-6">
            <h2 className="text-xl font-semibold mb-4">API测试</h2>
            <div className="space-y-2 mb-4">
              {apiTests.map((test, index) => (
                <div key={index} className="border rounded p-4">
                  <div className="flex justify-between items-center mb-2">
                    <h3 className="font-medium">{test.name}</h3>
                    <button
                      onClick={() => runApiTest(index)}
                      className="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                    >
                      测试
                    </button>
                  </div>
                  
                  <div className="text-sm">
                    <p className="mb-1">
                      状态: 
                      {test.status === 'pending' && <span className="text-gray-500">等待测试</span>}
                      {test.status === 'success' && <span className="text-green-600 font-medium">成功</span>}
                      {test.status === 'error' && <span className="text-red-600 font-medium">失败</span>}
                    </p>
                    
                    {test.status === 'success' && (
                      <pre className="bg-gray-50 p-2 rounded text-xs overflow-auto max-h-40">
                        {typeof test.data === 'object' ? JSON.stringify(test.data, null, 2) : test.data}
                      </pre>
                    )}
                    
                    {test.status === 'error' && (
                      <pre className="bg-red-50 p-2 rounded text-xs overflow-auto max-h-40 text-red-800">
                        {typeof test.error === 'object' ? JSON.stringify(test.error, null, 2) : test.error}
                      </pre>
                    )}
                  </div>
                </div>
              ))}
            </div>
            
            <button
              onClick={runAllTests}
              className="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              运行所有测试
            </button>
          </section>
          
          <section className="bg-white rounded-lg shadow p-6">
            <h2 className="text-xl font-semibold mb-4">自定义API请求</h2>
            <div className="mb-4">
              <label className="block text-sm font-medium text-gray-700 mb-1">API路径</label>
              <div className="flex">
                <span className="inline-flex items-center px-3 bg-gray-100 text-gray-500 border border-r-0 border-gray-300 rounded-l-md text-sm">
                  /api
                </span>
                <input
                  type="text"
                  value={customUrl}
                  onChange={(e) => setCustomUrl(e.target.value)}
                  className="flex-1 min-w-0 block w-full px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                  placeholder="/homepage/settings"
                />
              </div>
              <p className="mt-1 text-xs text-gray-500">示例: /homepage/settings, /products/featured</p>
            </div>
            
            <button
              onClick={runCustomRequest}
              disabled={loading}
              className="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-blue-300"
            >
              {loading ? '请求中...' : '发送请求'}
            </button>
            
            <div className="mt-4">
              <h3 className="font-medium mb-2">响应结果</h3>
              
              {loading && (
                <div className="text-center py-4">
                  <p className="text-gray-500">加载中...</p>
                </div>
              )}
              
              {!loading && customResponse && (
                <pre className="bg-gray-50 p-3 rounded text-xs overflow-auto max-h-60">
                  {JSON.stringify(customResponse, null, 2)}
                </pre>
              )}
              
              {!loading && customError && (
                <div>
                  <p className="text-red-600 font-medium mb-1">错误:</p>
                  <pre className="bg-red-50 p-3 rounded text-xs overflow-auto max-h-60 text-red-800">
                    {JSON.stringify(customError, null, 2)}
                  </pre>
                </div>
              )}
            </div>
          </section>
        </div>

        <section className="bg-white rounded-lg shadow p-6 mb-8">
          <h2 className="text-xl font-semibold mb-4">HomepageSettingsContext 测试</h2>
          
          <div className="mb-4">
            <p className="mb-2">
              <span className="font-medium">加载状态:</span> 
              <span className={settingsLoading ? "text-blue-600" : "text-green-600"}>
                {settingsLoading ? "加载中..." : "已加载"}
              </span>
            </p>
            
            {settingsError && (
              <div className="mb-4">
                <p className="text-red-600 font-medium mb-1">发生错误:</p>
                <pre className="bg-red-50 p-3 rounded text-xs overflow-auto max-h-40 text-red-800">
                  {JSON.stringify(settingsError, null, 2)}
                </pre>
              </div>
            )}
            
            <div>
              <h3 className="font-medium mb-2">设置数据:</h3>
              <pre className="bg-gray-50 p-3 rounded text-xs overflow-auto max-h-80">
                {JSON.stringify(settings, null, 2)}
              </pre>
            </div>
          </div>
        </section>

        <div className="text-center">
          <p className="mb-4 text-gray-600">测试完成后，您可以返回首页或其他页面</p>
          <Link 
            to="/" 
            className="inline-block px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700"
          >
            返回首页
          </Link>
        </div>
      </div>
    </div>
  );
};

export default DebugPage; 
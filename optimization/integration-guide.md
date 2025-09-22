# 性能优化服务集成指南

本指南将帮助开发团队将性能优化服务（API缓存、批量请求、预加载、图片优化等）整合到现有项目中，以提高网站性能。

## 目录

1. [先决条件](#先决条件)
2. [服务概述](#服务概述)
3. [集成步骤](#集成步骤)
4. [前端集成](#前端集成)
5. [后端集成](#后端集成)
6. [配置和自定义](#配置和自定义)
7. [常见问题和解决方案](#常见问题和解决方案)
8. [性能监控和指标](#性能监控和指标)

## 先决条件

在开始集成之前，请确保您的项目满足以下条件：

- 前端项目使用TypeScript（或者可以将服务转换为JavaScript）
- 后端使用Laravel框架（版本8.0+）
- 配置了正确的CORS设置以允许批量API请求
- Node.js版本12+（用于构建工具）

## 服务概述

我们开发了以下优化服务：

1. **`CacheService`** - 实现API响应缓存，减少重复请求
2. **`BatchRequestService`** - 将多个API请求合并为一个HTTP请求
3. **`PreloadService`** - 预加载关键API数据，减少用户等待时间
4. **`PerformanceMonitor`** - 监控API响应时间和性能指标
5. **`ImageService`** - 优化图片加载，支持响应式图片和WebP格式
6. **`ServiceIntegration`** - 集成上述所有服务的统一接口

## 集成步骤

### 步骤1：复制服务文件

将以下文件复制到您项目的相应位置：

**前端文件：**
- `src/services/CacheService.ts`
- `src/services/BatchRequestService.ts`
- `src/services/PreloadService.ts`
- `src/services/PerformanceMonitor.ts`
- `src/services/ImageService.ts`
- `src/services/ServiceIntegration.ts`

**后端文件：**
- `app/Http/Controllers/Api/BatchRequestController.php`
- `config/api.php`

### 步骤2：安装依赖

确保您的项目包含所需的依赖项。对于前端优化服务，不需要额外的依赖项，因为它们使用浏览器原生API。

对于后端批处理控制器，确保您有正确配置的Laravel环境。

### 步骤3：注册批处理路由

在Laravel的`routes/api.php`文件中添加批处理路由：

```php
Route::post('/batch', 'App\Http\Controllers\Api\BatchRequestController@process');
```

### 步骤4：发布配置文件

运行以下命令发布API配置文件：

```bash
php artisan vendor:publish --tag=api-config
```

或者手动复制`config/api.php`文件到您项目的`config`目录。

## 前端集成

### 基本集成

在您的应用入口文件（如`src/index.ts`或`src/main.ts`）中，添加以下代码：

```typescript
import { initOptimizationServices } from './services/ServiceIntegration';

// 初始化所有优化服务
initOptimizationServices();
```

### 路由变更集成

如果您使用路由（如React Router、Vue Router等），添加路由变更处理：

#### React Router示例

```typescript
import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { handleRouteChange } from './services/ServiceIntegration';

function App() {
  const location = useLocation();
  
  useEffect(() => {
    // 提取路由名称
    const path = location.pathname;
    const routeName = getRouteNameFromPath(path);
    
    // 处理路由变更
    handleRouteChange(routeName);
  }, [location]);
  
  function getRouteNameFromPath(path: string): string {
    if (path.startsWith('/products')) return 'product-list';
    if (path.startsWith('/checkout')) return 'checkout';
    return 'home';
  }
  
  return (
    // 应用组件
  );
}
```

#### Vue Router示例

```typescript
// 在main.ts或router.ts中
import router from './router';
import { handleRouteChange } from './services/ServiceIntegration';

router.beforeEach((to, from, next) => {
  // 处理路由变更
  const routeName = to.name?.toString() || 'home';
  handleRouteChange(routeName);
  next();
});
```

### 使用优化的API调用

替换项目中的API调用，使用优化的API服务：

```typescript
// 替换：
// fetch('/api/products')
//   .then(res => res.json())
//   .then(data => setProducts(data));

// 使用：
import { api } from './services/ServiceIntegration';

api.get('/api/products')
  .then(data => setProducts(data));
```

### 使用批量请求

对于需要多个API调用的页面，使用批量请求：

```typescript
import { api } from './services/ServiceIntegration';

async function loadProductPage(productId: number) {
  try {
    const [product, reviews, related] = await api.batchRequests([
      { url: `/api/products/${productId}`, method: 'GET' },
      { url: `/api/products/${productId}/reviews`, method: 'GET' },
      { url: `/api/products/related`, method: 'GET', params: { id: productId } }
    ]);
    
    setProduct(product);
    setReviews(reviews);
    setRelatedProducts(related);
  } catch (error) {
    console.error('Failed to load product page:', error);
  }
}
```

### 使用优化的图片

替换标准`<img>`标签，使用优化的图片URL和srcSet：

```typescript
import { getOptimizedImageUrl, getResponsiveSrcSet } from './services/ServiceIntegration';

function ProductImage({ product }) {
  return (
    <img 
      src={getOptimizedImageUrl(product.image, { width: 300, height: 300 })}
      srcSet={getResponsiveSrcSet(product.image, { widths: [300, 600, 900] })}
      sizes="(max-width: 768px) 100vw, 300px"
      alt={product.name}
      loading="lazy"
      width={300}
      height={300}
    />
  );
}
```

## 后端集成

### 配置批处理控制器

编辑`config/api.php`文件以配置批处理行为：

```php
// config/api.php
return [
    'batch_max_requests' => env('API_BATCH_MAX_REQUESTS', 10),
    'batch_logging' => env('API_BATCH_LOGGING', true),
    // 其他配置...
];
```

### 配置服务器端缓存（可选但推荐）

为了进一步优化性能，建议配置服务器端缓存。在您的API控制器中添加缓存：

```php
// 在控制器方法中
public function index()
{
    return Cache::remember('products.all', 3600, function () {
        return Product::with('category')->get();
    });
}
```

## 配置和自定义

### 自定义缓存配置

您可以根据不同API的需求自定义缓存设置：

```typescript
import { apiCache, CacheExpiry } from './services/CacheService';

// 设置全局配置
apiCache.setConfig({
  enabled: true,
  storage: 'localStorage', // 或 'sessionStorage'
  defaultExpiry: CacheExpiry.MEDIUM // 默认15分钟
});

// 针对特定API设置缓存过期时间
api.get('/api/products', null, { cacheExpiry: CacheExpiry.LONG }); // 1小时
api.get('/api/cart', null, { cacheExpiry: CacheExpiry.SHORT }); // 1分钟
```

### 自定义批处理配置

您可以自定义批处理行为：

```typescript
import { batchApi } from './services/BatchRequestService';

batchApi.setConfig({
  enabled: true,
  maxBatchSize: 5, // 每批最多5个请求
  batchDelay: 100, // 延迟100ms后发送批请求
  endpoint: '/api/batch'
});
```

### 自定义图片服务配置

根据您的图片托管服务调整图片服务配置：

```typescript
import { imageService } from './services/ImageService';

imageService.updateConfig({
  baseUrl: 'https://yourapp.com',
  cdnUrl: 'https://cdn.yourapp.com',
  breakpoints: {
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280
  },
  defaultQuality: 80,
  supportWebP: true,
  supportAvif: 'auto' // 'auto', true, 或 false
});
```

## 常见问题和解决方案

### 1. 批处理请求返回403错误

**问题**: 批处理请求返回403 Forbidden错误。

**解决方案**: 
- 检查CSRF保护设置
- 确保批处理路由未被中间件阻止
- 验证用户权限

```php
// 在 VerifyCsrfToken.php 中添加批处理路由到排除列表
protected $except = [
    'api/batch',
];
```

### 2. 缓存导致数据不一致

**问题**: 用户看到的是缓存数据，而不是最新数据。

**解决方案**:
- 在数据变更后清除相关缓存

```typescript
// 在成功更新后清除缓存
await api.post('/api/products', newProduct);
api.clearCache('/api/products');
```

### 3. 图片未显示WebP格式

**问题**: 图片未使用WebP格式，尽管浏览器支持。

**解决方案**:
- 确保服务器支持WebP
- 检查图片URL生成逻辑
- 确认CDN配置正确

## 性能监控和指标

### 监控API性能

使用`PerformanceMonitor`服务监控API性能：

```typescript
import { performanceMonitor } from './services/PerformanceMonitor';

// 获取性能报告
const report = performanceMonitor.getReport();
console.log('Slow requests:', report.slowRequests);
console.log('Average response time:', report.averageResponseTime);
```

### 集成Google Analytics

将性能数据发送到Google Analytics或其他分析工具：

```typescript
import { performanceMonitor, MarkType } from './services/PerformanceMonitor';

performanceMonitor.setConfig({
  enabled: true,
  reportToServer: true,
  reportEndpoint: '/api/performance/report',
  onReport: (report) => {
    // 发送到Google Analytics
    if (window.gtag) {
      window.gtag('event', 'performance', {
        'event_category': 'API',
        'event_label': 'Average Response Time',
        'value': report.averageResponseTime
      });
    }
  }
});
```

## 进一步优化建议

1. **实现服务工作者 (Service Worker)** - 缓存静态资源和API响应
2. **添加资源提示** - 使用`<link rel="preload">`和`<link rel="preconnect">`
3. **优化字体加载** - 使用`font-display: swap`和字体子集
4. **添加骨架屏** - 改善感知性能
5. **实现虚拟滚动** - 对于长列表，只渲染可视区域内的项目

---

如果您在集成过程中遇到任何问题，请查阅详细的服务文档或联系开发团队获取支持。 
# 首页管理系统文档

本文档详细记录了首页管理系统(`/admin/homepage`)的各个部分、功能和用法，服务于管理员和开发人员。

## 目录

1. [系统概览](#系统概览)
2. [首页部分管理](#首页部分管理)
3. [部分类型与布局](#部分类型与布局)
4. [库存阈值管理](#库存阈值管理)
5. [SEO设置](#SEO设置)
6. [缓存机制](#缓存机制)
7. [前后端集成](#前后端集成)
8. [事件与通知](#事件与通知)
9. [API接口](#API接口)
10. [开发者资源](#开发者资源)

## 系统概览

首页管理系统是一个用于管理电子商务网站首页的后台管理工具。它允许管理员完全控制首页的各个部分，包括产品展示、分类、布局和设计。系统的特点包括：

- 可视化部分管理
- 拖拽排序功能
- 库存自动管理
- SEO优化设置
- 缓存系统集成
- 前后端数据同步

管理路径: `/admin/homepage`

## 首页部分管理

### 部分列表

首页由多个"部分"(Sections)组成，管理界面左侧展示所有已创建的部分，并提供以下功能：

- **添加部分**: 点击"Add Section"按钮创建新部分
- **拖拽排序**: 通过拖拽调整部分显示顺序
- **状态切换**: 快速启用/禁用部分
- **编辑**: 修改部分内容和设置
- **删除**: 移除不需要的部分

每个部分都有以下状态指示：
- 绿色边框: 启用状态
- 红色边框: 禁用状态

### 创建/编辑部分

创建或编辑部分时，需要设置以下内容：

1. **基本信息**:
   - 标题(Title): 部分的主标题
   - 副标题(Subtitle): 可选的副标题
   - 类型(Type): 部分的内容类型
   - 布局(Layout): 展示布局

2. **样式设置**:
   - 背景颜色(Background Color)
   - 文字颜色(Text Color)
   - 内边距(Padding)
   - 自定义类(Custom Class): 用于添加额外CSS样式

3. **内容管理**:
   根据不同的部分类型，可能需要选择：
   - 产品列表
   - 分类列表
   - 自定义HTML内容
   - 图片或横幅

## 部分类型与布局

首页管理系统支持多种部分类型和布局，以满足不同的展示需求。

### 部分类型

首页支持以下部分类型：

- **Hero Banner(英雄横幅)**: 大型横幅，通常位于页面顶部
- **Featured Products(特色产品)**: 展示被标记为特色的产品
- **Category Showcase(分类展示)**: 展示产品分类
- **Sale Products(促销产品)**: 展示正在促销的产品
- **Banner(横幅)**: 小型广告横幅
- **Text Content(文本内容)**: 纯文本信息区域
- **Custom HTML(自定义HTML)**: 允许添加自定义HTML代码

### 布局选项

每种部分类型可以使用以下布局：

- **Full Width(全宽)**: 占据整个屏幕宽度
- **Contained(容器)**: 在中心容器内显示
- **Boxed(盒式)**: 带边框和背景的盒式显示
- **Side by Side(并排)**: 内容分为左右两部分
- **Grid(网格)**: 可选2、3或4列的网格布局

## 库存阈值管理

### 自动移除低库存产品

系统会自动管理首页展示的产品库存状态：

- **最小库存阈值**: 设置自动从首页移除产品的最小库存数量
- **手动过滤**: "Run Stock Filter"按钮可手动触发低库存产品的移除
- **通知**: 当产品因库存不足被移除时，系统会自动通知管理员

### 设置库存阈值

设置位置：页面右侧"Stock Threshold"卡片

1. 输入数字设置最小库存阈值
2. 点击"Save Threshold"保存设置
3. 设置后，所有库存低于此阈值的产品将不会出现在首页

## SEO设置

### 首页SEO优化

在"Homepage Settings"卡片中可以设置以下SEO相关内容：

- **SEO标题**: 用于搜索引擎结果的页面标题
- **SEO描述**: 页面的元描述，用于搜索引擎结果显示
- **SEO关键词**: 网页的关键词，多个关键词用逗号分隔

这些设置影响首页的`<title>`标签、`meta description`和`meta keywords`标签。

## 缓存机制

系统实现了高效的缓存机制，减少数据库查询并提高页面加载速度。

### 缓存服务(HomepageCacheService)

首页管理系统使用专用的缓存服务来管理所有首页相关的缓存操作。缓存服务提供以下功能：

- **缓存获取**: 从缓存获取数据或重建缓存
- **缓存清理**: 当数据变更时清除相关缓存
- **缓存预热**: 预先生成缓存以提高首次访问速度

主要缓存方法包括：
- `getCachedSectionProducts(int $sectionId)`: 获取某个部分的缓存产品
- `getCachedFeaturedProducts()`: 获取缓存的特色产品
- `getCachedNewArrivals()`: 获取缓存的新品
- `getCachedSaleProducts()`: 获取缓存的促销产品
- `getCachedHomepageSections()`: 获取缓存的首页部分列表
- `getCachedProductStockStatus(int $productId)`: 获取缓存的产品库存状态

缓存清理方法：
- `clearProductCaches(int $productId)`: 清除特定产品的相关缓存
- `clearStockRelatedCaches(int $productId)`: 清除库存相关缓存
- `clearSectionCaches(int $sectionId)`: 清除部分相关缓存
- `clearAllHomepageCaches()`: 清除所有首页相关缓存

### 缓存管理

- **自动缓存**: 当修改首页内容时，缓存会自动失效并重建
- **缓存类型**:
  - 首页设置缓存(`homepage_settings`)
  - 特色产品缓存(`homepage_featured_products`)
  - 新品缓存(`homepage_new_arrivals`)
  - 促销产品缓存(`homepage_sale_products`)
  - 部分结构缓存(`homepage_sections`)
  - 部分产品缓存(`homepage_section_{id}_products`)
  - 产品库存状态缓存(`product_{id}_stock_status`)

### 缓存过期时间

系统为不同类型的缓存设置了不同的过期时间：
- 首页部分和产品列表: 3小时
- 产品库存状态: 30分钟
- 设置数据: 24小时

### 缓存预热

系统提供了缓存预热功能，通常在以下情况下执行：
- 系统部署后
- 大规模数据更新后
- 通过计划任务定期执行

预热命令: `php artisan homepage:warmup-cache`

## 前后端集成

首页管理系统与前端React应用集成，实现数据实时同步。

### Redis连接器

系统使用Redis连接器(`homepage-redis-connector.js`)在后端管理界面和前端应用之间同步数据。这个脚本加载在管理后台的布局文件中，提供以下功能：

#### 数据同步功能

- **实时更新**: 当管理员更改首页内容时，前端缓存会自动失效
- **无缝体验**: 客户总是能看到最新的首页内容
- **性能优化**: 减少不必要的API调用和数据库查询

#### 主要功能

- **事件监听**: 监听Laravel事件和Livewire事件
- **缓存失效**: 根据更新类型失效特定缓存
- **数据预取**: 在缓存失效后预取新数据
- **跨域通信**: 在不同域名下工作的系统之间进行消息传递

#### 通信机制

连接器使用`postMessage` API在不同窗口/域名之间传递消息，支持的消息类型包括：

- `invalidateCache`: 通知前端使特定缓存失效
- `cacheData`: 向前端发送新的数据以更新缓存
- `ping`/`pong`: 检查前端可用性的心跳机制

#### 安全性

连接器实现了以下安全机制：

- **消息来源验证**: 验证消息是否来自可信来源
- **错误处理**: 妥善处理所有通信错误
- **源检查**: 只与已批准的源域通信

### React Redis客户端

为React前端开发了专用的Redis客户端(`react-redis-client.js`)，用于接收和处理来自后端的缓存更新消息。

#### 主要组件

- **RedisService类**: 核心服务类，提供缓存读写功能
- **React Hooks**: 简化在React组件中使用缓存数据的过程
- **前端缓存**: 在localStorage中实现类似Redis的缓存功能

#### 提供的钩子

前端React应用可以使用以下钩子获取缓存数据：

- `useHomepageSettings()`: 获取首页设置
- `useFeaturedProducts(limit = 8)`: 获取特色产品
- `useNewArrivals(limit = 8)`: 获取新品
- `useSaleProducts(limit = 8)`: 获取促销产品
- `useHomepageSections()`: 获取首页部分结构

这些钩子自动处理以下功能：
- 从缓存获取数据
- 当缓存不存在时从API获取数据
- 监听缓存失效事件自动刷新数据
- 处理加载和错误状态

#### 使用示例

```jsx
// 在React组件中使用
import { useHomepageSettings, useFeaturedProducts } from './react-redis-client';

function HomePage() {
  const { data: settings, loading: settingsLoading } = useHomepageSettings();
  const { data: featuredProducts, loading: productsLoading } = useFeaturedProducts();
  
  if (settingsLoading || productsLoading) {
    return <div>Loading...</div>;
  }
  
  return (
    <div>
      <h1>{settings?.banner_title || 'Welcome'}</h1>
      <div className="featured-products">
        {featuredProducts?.map(product => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
    </div>
  );
}
```

### 集成架构

前后端集成的整体架构如下：

1. **更新触发**：管理员在后台更新内容
2. **事件发布**：后端触发`HomepageUpdatedEvent`事件
3. **连接器响应**：`homepage-redis-connector.js`监听事件
4. **消息传递**：连接器使用`postMessage`通知前端
5. **前端处理**：`react-redis-client.js`接收消息并更新缓存
6. **UI更新**：React组件通过钩子获取最新数据并重新渲染

## 事件与通知

### 事件系统

系统使用Laravel事件系统在首页内容更新时触发事件：

- **HomepageUpdatedEvent**: 当首页内容(特色产品、新品、促销产品、设置)更新时触发
- **StockChangedEvent**: 当产品库存变化时触发

### 通知功能

管理员可以接收以下通知：

- **更新通知**: 当首页内容被其他管理员更新时
- **低库存通知**: 当产品因库存不足被自动从首页移除时

通知通过电子邮件发送给配置的接收者，支持多个接收邮箱。

## API接口

首页管理系统提供了以下API接口供前端应用调用：

### 产品相关

- `GET /api/homepage/featured-products`: 获取特色产品
- `GET /api/homepage/new-arrivals`: 获取新品
- `GET /api/homepage/sale-products`: 获取促销产品
- `GET /api/homepage/product/{id}/stock-status`: 获取产品库存状态

### 首页结构相关

- `GET /api/homepage/settings`: 获取首页设置
- `GET /api/homepage/sections`: 获取首页部分
- `GET /api/homepage/auto-management-settings`: 获取自动管理设置

所有API接口都支持JSON响应，并提供适当的错误处理和状态代码。

## 开发者资源

### 核心服务类

首页管理系统使用以下核心服务类：

1. **HomepageService**: 处理首页部分和内容管理
   - 创建、更新和删除部分
   - 管理特色产品、新品和促销产品
   - 处理部分排序和内容更新

2. **HomepageStockService**: 处理产品库存相关功能
   - 管理库存阈值设置
   - 过滤低库存产品
   - 处理库存变化通知

3. **HomepageCacheService**: 管理首页缓存
   - 缓存数据获取和更新
   - 缓存清理和预热
   - 优化性能和响应时间

### 相关模型

系统使用以下主要模型：

- **HomepageSection**: 表示首页的一个显示部分
- **Product**: 产品信息
- **ProductCategory**: 产品分类
- **NotificationSetting**: 通知设置
- **NotificationReceiver**: 通知接收者

### 事件和监听器

系统使用事件驱动架构，主要事件包括：

- **HomepageUpdatedEvent**: 首页更新事件
  - 监听器: `SendHomepageUpdateNotification`

- **StockChangedEvent**: 库存变化事件
  - 监听器: `HomepageStockListener`

### 命令行工具

为开发人员和系统管理员提供以下命令行工具：

- `php artisan homepage:warmup-cache`: 预热首页缓存
- `php artisan homepage:cleanup-sections`: 清理废弃的部分
- `php artisan homepage:stock-filter`: 运行库存过滤 
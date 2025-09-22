# 首页设置功能实现文档

## 概述

本文档描述了鞋类电商前端项目中首页设置功能的实现。该功能允许从后端API获取首页设置数据（如横幅、优惠信息和精选产品），并缓存到前端的Redis模拟服务中，以提高页面加载性能并减少API调用次数。

## 技术架构

- **React**: 前端UI库
- **TypeScript**: 静态类型检查
- **Context API**: 状态管理和共享
- **Redis模拟服务**: 在前端模拟Redis缓存功能
- **Axios**: API请求

## 主要组件与模块

### 1. 类型定义 (`types/homepage.ts`)

定义了设置相关的接口：
- `BannerSettings`: 横幅设置
- `OfferSettings`: 优惠信息设置
- `FeaturedProductsSettings`: 精选产品设置
- `HomepageSettings`: 聚合了以上三种设置
- `SettingsUpdateEventType`: 设置更新事件类型
- `SettingsUpdateEvent`: 设置更新事件

### 2. 服务层 (`services/homepageService.ts`)

提供了与首页设置相关的数据处理功能：
- `fetchHomepageSettingsToRedis`: 从API获取设置并缓存到Redis
- `getHomepageSettingsFromRedis`: 从Redis获取设置，如不存在则从API获取
- `getBannerSettings`: 获取横幅设置
- `startSettingsPolling`: 启动轮询检查设置更新

### 3. Context提供者 (`contexts/HomepageSettingsContext.tsx`)

使用React Context API提供设置数据：
- `HomepageSettingsProvider`: 提供设置数据的Context Provider
- `useHomepageSettings`: 自定义hook，用于在组件中获取设置数据

### 4. 状态与调试组件

- `HomepageSettingsStatus`: 开发环境中显示设置加载状态和最后更新时间
- `DevTools`: 开发工具，提供查看和操作Redis缓存的功能

## 使用方法

### 1. 在组件中使用设置数据

```tsx
import { useHomepageSettings } from '../contexts/HomepageSettingsContext';

const MyComponent = () => {
  const { settings, loading, error } = useHomepageSettings();
  
  if (loading) return <div>加载中...</div>;
  if (error) return <div>加载错误: {error.message}</div>;
  
  return (
    <div>
      <h1>{settings.banner.banner_title}</h1>
      <p>{settings.banner.banner_subtitle}</p>
      {/* 其他内容 */}
    </div>
  );
};
```

### 2. 手动刷新设置

```tsx
import { useHomepageSettings } from '../contexts/HomepageSettingsContext';

const RefreshButton = () => {
  const { refreshSettings } = useHomepageSettings();
  
  return (
    <button onClick={() => refreshSettings()}>
      刷新设置
    </button>
  );
};
```

## 数据流

1. 应用启动时，`main.tsx`中调用`homepageService.fetchHomepageSettingsToRedis()`预加载设置到Redis缓存
2. `HomepageSettingsProvider`初始化时从Redis缓存获取设置，如不存在则从API获取
3. 设置轮询定时检查设置更新，发现更新则通知`HomepageSettingsProvider`更新状态
4. 各组件通过`useHomepageSettings`hook获取最新设置数据

## 缓存策略

- 首页设置缓存过期时间: 5分钟
- 轮询检查间隔: 30秒
- 缓存键:
  - `homepage:settings`: 完整的首页设置
  - `homepage:banner`: 仅横幅设置
  - `homepage:offer`: 仅优惠信息
  - `homepage:featured_products`: 仅精选产品设置

## 开发调试

1. 使用开发工具组件(`DevTools`)查看和操作Redis缓存
2. 通过`HomepageSettingsStatus`组件监控设置加载状态和最后更新时间
3. 控制台日志中包含详细的加载和更新信息

## 注意事项

1. Redis模拟服务仅在前端模拟缓存功能，不是真正的Redis服务器
2. 开发环境中使用模拟数据，生产环境必须配置正确的API端点
3. 图片路径需要确保正确，特别是在生产环境中 
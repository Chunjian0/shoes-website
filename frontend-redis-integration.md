# 前端Redis集成方案

## 概述

本文档详细描述了前端React应用与后端Laravel系统的数据缓存集成方案。通过在前端实现模拟Redis功能的客户端缓存服务，提高系统响应速度，减轻服务器负担，并确保两端数据的一致性。

## 前端Redis模拟实现

### 核心服务类

前端使用自定义的`RedisService`类在浏览器端实现类似Redis的缓存功能：

```typescript
// src/services/RedisService.ts
interface CacheItem<T> {
  value: T;
  expiry: number | null; // null表示永不过期
}

class RedisService {
  private cache: Map<string, CacheItem<any>>;
  private defaultExpiry: number = 5 * 60 * 1000; // 默认5分钟过期
  
  constructor() {
    this.cache = new Map();
    this.loadFromStorage();
    
    // 页面卸载时保存缓存到localStorage
    window.addEventListener('beforeunload', () => {
      this.saveToStorage();
    });
    
    // 定期清理过期缓存
    setInterval(() => {
      this.cleanExpiredCache();
    }, 60000); // 每分钟清理一次
  }
  
  // 从localStorage加载缓存
  private loadFromStorage(): void {
    try {
      const saved = localStorage.getItem('redis_cache');
      if (saved) {
        const parsed = JSON.parse(saved);
        Object.keys(parsed).forEach(key => {
          const item = parsed[key];
          if (item.expiry === null || item.expiry > Date.now()) {
            this.cache.set(key, item);
          }
        });
      }
    } catch (e) {
      console.error('Failed to load cache from storage:', e);
    }
  }
  
  // 保存缓存到localStorage
  private saveToStorage(): void {
    try {
      const toSave: Record<string, CacheItem<any>> = {};
      this.cache.forEach((value, key) => {
        if (value.expiry === null || value.expiry > Date.now()) {
          toSave[key] = value;
        }
      });
      localStorage.setItem('redis_cache', JSON.stringify(toSave));
    } catch (e) {
      console.error('Failed to save cache to storage:', e);
    }
  }
  
  // 清理过期缓存
  private cleanExpiredCache(): void {
    const now = Date.now();
    this.cache.forEach((item, key) => {
      if (item.expiry !== null && item.expiry <= now) {
        this.cache.delete(key);
      }
    });
  }
  
  // 获取缓存项
  async get<T>(key: string): Promise<T | null> {
    const item = this.cache.get(key);
    if (!item) return null;
    
    // 检查是否过期
    if (item.expiry !== null && item.expiry <= Date.now()) {
      this.cache.delete(key);
      return null;
    }
    
    return item.value as T;
  }
  
  // 设置缓存项
  async set<T>(key: string, value: T, expiry?: number): Promise<boolean> {
    const expiryTime = expiry !== undefined 
      ? (expiry > 0 ? Date.now() + expiry : null) 
      : (this.defaultExpiry > 0 ? Date.now() + this.defaultExpiry : null);
    
    this.cache.set(key, {
      value,
      expiry: expiryTime
    });
    
    return true;
  }
  
  // 获取或设置缓存
  async getOrSet<T>(key: string, callback: () => Promise<T>, expiry?: number): Promise<T> {
    const cached = await this.get<T>(key);
    if (cached !== null) return cached;
    
    const value = await callback();
    await this.set(key, value, expiry);
    return value;
  }
  
  // 删除缓存项
  async del(key: string): Promise<boolean> {
    return this.cache.delete(key);
  }
  
  // 清空所有缓存
  async flushAll(): Promise<boolean> {
    this.cache.clear();
    localStorage.removeItem('redis_cache');
    return true;
  }
  
  // 检查键是否存在
  async exists(key: string): Promise<boolean> {
    const item = this.cache.get(key);
    if (!item) return false;
    
    // 检查是否过期
    if (item.expiry !== null && item.expiry <= Date.now()) {
      this.cache.delete(key);
      return false;
    }
    
    return true;
  }
  
  // 设置过期时间
  async expire(key: string, seconds: number): Promise<boolean> {
    const item = this.cache.get(key);
    if (!item) return false;
    
    item.expiry = Date.now() + (seconds * 1000);
    return true;
  }
}

// 创建单例实例
const redisInstance = new RedisService();
export default redisInstance;
```

### 使用方式

以下是在React组件中使用前端Redis服务的示例：

```typescript
// src/hooks/useRedisData.ts
import { useState, useEffect } from 'react';
import redisService from '../services/RedisService';
import apiService from '../services/ApiService';

export function useRedisData<T>(
  key: string, 
  apiFetch: () => Promise<T>, 
  expiry: number = 5 * 60 * 1000, // 默认5分钟
  dependencies: any[] = []
) {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<Error | null>(null);
  
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const result = await redisService.getOrSet<T>(
          key,
          apiFetch,
          expiry
        );
        setData(result);
        setError(null);
      } catch (err) {
        setError(err instanceof Error ? err : new Error(String(err)));
      } finally {
        setLoading(false);
      }
    };
    
    fetchData();
  }, dependencies);
  
  return { data, loading, error };
}

// 使用示例
// src/components/HomePage.tsx
import React from 'react';
import { useRedisData } from '../hooks/useRedisData';
import apiService from '../services/ApiService';

const HomePage: React.FC = () => {
  const { data: homeSettings, loading } = useRedisData(
    'homepage_settings',
    () => apiService.get('/api/homepage/settings'),
    10 * 60 * 1000 // 10分钟
  );
  
  if (loading) return <div>Loading...</div>;
  
  return (
    <div>
      <h1>{homeSettings?.banner_title || 'Welcome'}</h1>
      {/* 渲染首页内容 */}
    </div>
  );
};
```

## 缓存策略

### 需要缓存的数据

1. **首页设置和布局配置**
   - 键名: `homepage_settings`
   - 过期时间: 10分钟
   - API端点: `/api/homepage/settings`

2. **产品分类数据**
   - 键名: `product_categories`
   - 过期时间: 30分钟
   - API端点: `/api/product-categories`

3. **特色产品数据**
   - 键名: `featured_products`
   - 过期时间: 5分钟
   - API端点: `/api/products/featured`

4. **新品数据**
   - 键名: `new_arrival_products`
   - 过期时间: 5分钟
   - API端点: `/api/products/new-arrivals`

5. **促销产品数据**
   - 键名: `sale_products`
   - 过期时间: 5分钟
   - API端点: `/api/products/sale`

6. **产品详情**
   - 键名: `product_<id>`
   - 过期时间: 10分钟
   - API端点: `/api/products/<id>`

7. **用户浏览历史**
   - 键名: `user_<id>_history`
   - 过期时间: 7天
   - 本地存储，无API端点

8. **购物车数据**
   - 键名: `cart_<user_id>`
   - 过期时间: 永不过期（手动管理）
   - API端点: `/api/cart`

### 缓存失效策略

1. **基于时间的自动过期**
   - 每个缓存条目都有预设的过期时间
   - 达到过期时间后自动失效并在下次访问时重新获取

2. **手动失效触发**
   - 当用户执行特定操作（如添加到购物车、下单等）时
   - 相关数据缓存被手动标记为失效
   - 实现方式：监听特定事件并调用`redisService.del(key)`

3. **后端通知驱动的失效**
   - 后端数据变更时通过WebSocket通知前端
   - 前端收到通知后清除对应缓存
   - 实现细节见下方WebSocket集成部分

## 前后端数据同步

### API同步机制

1. **定期轮询刷新**
   - 对于某些关键数据（如库存、价格）
   - 设置较短的缓存过期时间
   - 过期后重新从API获取最新数据

2. **操作触发同步**
   - 用户进行写操作（如添加购物车）时
   - 先发送API请求，成功后再更新本地缓存
   - 确保本地缓存与服务器数据一致

### WebSocket集成

为实现实时缓存失效通知，前端应当集成WebSocket监听后端事件：

```typescript
// src/services/WebSocketService.ts
import redisService from './RedisService';

class WebSocketService {
  private socket: WebSocket | null = null;
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 5;
  private reconnectTimeout = 1000;
  
  connect() {
    if (this.socket) return;
    
    try {
      this.socket = new WebSocket('ws://localhost:2268/ws');
      
      this.socket.onopen = () => {
        console.log('WebSocket connected');
        this.reconnectAttempts = 0;
      };
      
      this.socket.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          this.handleCacheInvalidation(data);
        } catch (e) {
          console.error('Failed to parse WebSocket message:', e);
        }
      };
      
      this.socket.onclose = () => {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
          setTimeout(() => {
            this.reconnectAttempts++;
            this.connect();
          }, this.reconnectTimeout * this.reconnectAttempts);
        }
      };
      
      this.socket.onerror = (error) => {
        console.error('WebSocket error:', error);
      };
    } catch (e) {
      console.error('Failed to connect WebSocket:', e);
    }
  }
  
  private handleCacheInvalidation(data: any) {
    if (data.type === 'cache_invalidation') {
      const { keys } = data;
      
      if (Array.isArray(keys)) {
        // 清除指定的缓存键
        keys.forEach(key => {
          redisService.del(key);
        });
      } else if (keys === '*') {
        // 清除所有缓存
        redisService.flushAll();
      }
    }
  }
  
  disconnect() {
    if (this.socket) {
      this.socket.close();
      this.socket = null;
    }
  }
}

const wsService = new WebSocketService();
export default wsService;
```

### 后端缓存事件

Laravel后端需实现以下功能以支持前端缓存同步：

1. **缓存失效通知**：后端数据更新时发送WebSocket通知
2. **更新标志**：API响应中包含`last_updated`时间戳
3. **版本控制**：为可缓存的资源提供ETag或版本标识

## 实现注意事项

### 安全性考虑

1. **敏感数据处理**
   - 用户个人信息等敏感数据不应缓存，或设置很短的过期时间
   - 密码、支付信息等永远不缓存

2. **数据验证**
   - 从缓存读取的数据在使用前应进行验证
   - 特别是用于提交表单的数据

### 存储限制

1. **缓存大小控制**
   - localStorage有5MB左右的限制
   - 图片等大型资源应使用浏览器缓存而非Redis模拟缓存
   - 实现LRU(最近最少使用)算法管理缓存容量

2. **存储优先级**
   - 设置数据优先级，空间不足时优先删除低优先级数据
   - 关键业务数据（如购物车）给予高优先级

### 性能优化

1. **序列化优化**
   - 大型对象序列化前进行压缩
   - 考虑使用专门的序列化库如msgpack-lite

2. **批量操作**
   - 一次性获取多个相关数据并缓存，减少API调用
   - 实现批量获取和批量更新方法

## API参考

| 接口名称 | URL | 方法 | 功能描述 | 缓存配置 |
|---------|-----|------|---------|---------|
| 首页设置 | `/api/homepage/settings` | GET | 获取首页配置信息 | 10分钟 |
| 产品分类 | `/api/product-categories` | GET | 获取所有产品分类 | 30分钟 |
| 特色产品 | `/api/products/featured` | GET | 获取首页推荐产品 | 5分钟 |
| 新品列表 | `/api/products/new-arrivals` | GET | 获取新上市产品 | 5分钟 |
| 促销产品 | `/api/products/sale` | GET | 获取特价促销产品 | 5分钟 |
| 产品详情 | `/api/products/{id}` | GET | 获取产品详细信息 | 10分钟 |
| 购物车 | `/api/cart` | GET | 获取用户购物车 | 手动管理 |
| 添加购物车 | `/api/cart/items` | POST | 添加商品到购物车 | 清除购物车缓存 |
| 更新购物车 | `/api/cart/items/{id}` | PUT | 更新购物车商品 | 清除购物车缓存 |
| 删除购物车商品 | `/api/cart/items/{id}` | DELETE | 从购物车移除商品 | 清除购物车缓存 |
| 用户信息 | `/api/customer/profile` | GET | 获取用户信息 | 1小时 |

## 后端所需修改

为支持前端Redis集成，后端Laravel系统需要进行以下修改：

1. **头部信息增强**
   - 为API响应添加`Cache-Control`头以指导前端缓存
   - 添加`Last-Modified`和`ETag`头实现协商缓存

2. **WebSocket服务器**
   - 使用Laravel WebSockets实现WebSocket服务器
   - 通过广播事件实现缓存失效通知

3. **缓存事件触发器**
   - 在数据更新操作中触发缓存失效事件
   - 例如：产品更新→触发产品缓存和相关列表缓存失效

4. **API接口优化**
   - 支持条件请求(`If-Modified-Since`, `If-None-Match`)
   - 支持部分响应以减少数据传输量

## 实施步骤

1. **前端Redis服务实现**
   - 开发RedisService类及单例
   - 编写单元测试确保功能正常

2. **React钩子开发**
   - 实现`useRedisData`钩子便于组件使用
   - 处理加载状态和错误情况

3. **API服务改造**
   - 修改API调用服务以支持缓存
   - 实现条件请求机制

4. **WebSocket集成**
   - 实现WebSocket连接服务
   - 处理缓存失效消息

5. **组件集成**
   - 在核心组件中使用缓存数据
   - 特别关注首页、产品列表和购物车组件

6. **测试与优化**
   - 进行性能测试，对比缓存前后的加载时间
   - 针对大型数据集进行优化

## 总结

前端Redis集成方案通过在客户端浏览器实现类Redis缓存功能，显著提升了用户体验和应用性能。该方案不仅减轻了服务器负担，还降低了网络延迟对用户体验的影响。

通过精心设计的缓存策略和失效机制，确保了前端展示数据的及时性和准确性，同时最大化利用了缓存带来的性能优势。前后端的紧密协作和数据同步机制则保证了系统数据的一致性。

该方案为电商系统的前端性能优化提供了全面的解决方案，特别适合频繁访问但变更较少的数据，如产品展示、分类信息和首页配置等。

## 附录：缓存数据结构示例

### 首页设置

```json
{
  "banner_title": "春季新品上市",
  "banner_subtitle": "全场8折优惠，限时特价",
  "banner_button_text": "立即选购",
  "banner_button_link": "/products/new-arrivals",
  "banner_image": "/storage/banners/spring_sale.jpg",
  "featured_products_title": "精选推荐",
  "new_arrivals_title": "新品上市",
  "sale_products_title": "特价促销",
  "featured_products_count": 8,
  "new_arrivals_count": 6,
  "sale_products_count": 6,
  "show_categories": true,
  "last_updated": "2025-03-22T10:15:30Z"
}
```

### 产品列表

```json
{
  "products": [
    {
      "id": 101,
      "name": "舒适休闲鞋",
      "sku": "SX-101",
      "price": 599.00,
      "discount_percentage": 15,
      "final_price": 509.15,
      "image": "/storage/products/101/main.jpg",
      "stock": 25,
      "is_featured": true,
      "is_new_arrival": false,
      "is_sale": true
    },
    // 更多产品...
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 48
  },
  "last_updated": "2025-03-22T11:30:15Z"
}
```

### 购物车

```json
{
  "items": [
    {
      "id": 15,
      "product_id": 101,
      "quantity": 1,
      "product": {
        "id": 101,
        "name": "舒适休闲鞋",
        "sku": "SX-101",
        "price": 599.00,
        "discount_percentage": 15,
        "final_price": 509.15,
        "image": "/storage/products/101/main.jpg"
      }
    }
  ],
  "total_items": 1,
  "subtotal": 509.15,
  "tax": 40.73,
  "shipping": 0,
  "total": 549.88,
  "last_updated": "2025-03-22T14:45:22Z"
}
``` 
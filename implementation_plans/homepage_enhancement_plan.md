# 首页功能增强实施计划

## 1. 首页库存实时更新优化

### 已实现部分
- 库存变动事件系统 `StockChangedEvent`
- 库存变动监听器 `HomepageStockListener`
- 自动移除低库存产品功能
- 库存变动通知系统

### 待优化部分
- **实时库存标识功能**：
  - 在前端显示实时库存状态标签（充足/紧张/售罄）
  - 针对低库存产品提供特殊视觉提示
  - 当库存降至阈值时显示"库存紧张"标签
  - 为缺货产品提供"到货通知"功能

- **库存预警级别系统**：
  - 定义多级库存预警（充足、正常、紧张、危险、缺货）
  - 根据不同预警级别采取不同策略（保留/隐藏/替换展示位）

### 实施步骤
1. 添加库存状态计算逻辑到`HomepageStockService`
2. 修改前端模板，支持实时库存状态显示
3. 增强`HomepageStockListener`，支持多级库存预警
4. 开发库存状态变更日志系统
5. 添加"货到通知"功能

## 2. 产品展示效果分析系统

### 功能设计
- **点击行为追踪**：
  - 跟踪首页不同区域产品点击率
  - 记录用户点击路径和停留时间
  - 对比不同位置产品转化率

- **A/B测试框架**：
  - 支持首页布局、样式的A/B测试
  - 评估不同展示方式的效果差异
  - 自动优化产品展示位置

- **展示效果仪表板**：
  - 产品展示位置热图
  - 时间段点击率分析
  - 用户行为路径可视化

### 实施步骤
1. 创建前端点击追踪系统
2. 设计产品展示分析数据库表
3. 开发后台分析仪表板
4. 实现A/B测试框架
5. 构建展示效果优化建议系统

## 3. 促销活动自动同步

### 功能设计
- **促销信息自动更新**：
  - 当促销开始/结束时自动更新首页
  - 促销倒计时动态显示
  - 新促销活动自动加入轮播区

- **促销产品优先级系统**：
  - 根据促销力度、库存、销量自动调整展示优先级
  - 为高利润促销产品提供更好展示位置
  - 不同促销类型的展示策略差异化

- **促销展示调度器**：
  - 预设促销上线/下线时间
  - 自动轮换促销内容
  - 针对不同时段优化促销展示

### 实施步骤
1. 创建`PromotionSyncService`服务
2. 开发促销展示调度任务系统
3. 实现促销优先级计算算法
4. 修改促销管理界面，支持展示策略设置
5. 添加促销效果分析模块

## 4. Redis缓存系统集成

### 功能设计
- **多级缓存架构**：
  - 页面片段缓存（部分页面缓存）
  - 完整页面缓存（全页面缓存）
  - 数据对象缓存（底层数据缓存）

- **智能缓存失效策略**：
  - 基于事件的缓存精确失效
  - 基于时间的缓存自动过期
  - 分级缓存更新机制

- **缓存预热与重建机制**：
  - 系统启动时缓存预热
  - 缓存失效后的平滑重建
  - 防止缓存雪崩的随机过期时间

### 实施步骤
1. 配置Redis缓存连接
2. 开发`HomepageCacheService`服务
3. 在关键点集成缓存失效触发
4. 实现缓存命中率监控
5. 添加缓存预热命令

## 技术实现细节

### 1. 实时库存标识功能

```php
/**
 * 计算产品库存状态
 *
 * @param int $productId
 * @return string 'in_stock', 'low_stock', 'out_of_stock'
 */
public function getProductStockStatus(int $productId): string
{
    $product = Product::find($productId);
    if (!$product) {
        return 'unknown';
    }
    
    $totalStock = $this->getProductStock($productId);
    $threshold = $this->getMinStockThreshold();
    
    if ($totalStock <= 0) {
        return 'out_of_stock';
    } elseif ($totalStock < $threshold) {
        return 'low_stock';
    } else {
        return 'in_stock';
    }
}
```

### 2. 点击行为追踪JavaScript

```javascript
// productClickTracker.js
class ProductTracker {
    static init() {
        document.addEventListener('DOMContentLoaded', () => {
            const productCards = document.querySelectorAll('[data-product-card]');
            
            productCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    const productId = card.dataset.productId;
                    const position = card.dataset.position;
                    const section = card.dataset.section;
                    
                    this.trackClick(productId, position, section);
                });
            });
        });
    }
    
    static trackClick(productId, position, section) {
        fetch('/api/track/product-click', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                product_id: productId,
                position: position,
                section: section,
                timestamp: new Date().toISOString()
            })
        });
    }
}

ProductTracker.init();
```

### 3. 促销同步服务

```php
/**
 * 同步促销活动到首页
 */
public function syncPromotionsToHomepage(): void
{
    // 获取当前活动的促销
    $activePromotions = Promotion::where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->where('is_active', true)
        ->get();
    
    // 获取首页促销区域
    $saleSection = HomepageSection::where('type', 'sale_products')
        ->where('is_active', true)
        ->first();
    
    if (!$saleSection) {
        return;
    }
    
    // 清除现有促销产品
    DB::table('homepage_section_product')
        ->where('homepage_section_id', $saleSection->id)
        ->delete();
    
    // 添加新促销产品
    foreach ($activePromotions as $promotion) {
        $products = $promotion->products()
            ->where('is_active', true)
            ->get();
        
        foreach ($products as $product) {
            // 更新产品促销标志
            $product->show_in_sale = true;
            $product->save();
            
            // 添加到首页促销区
            DB::table('homepage_section_product')->insert([
                'homepage_section_id' => $saleSection->id,
                'product_id' => $product->id,
                'position' => 0, // 将由优先级系统稍后排序
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    // 更新排序
    $this->updatePromotionProductSorting($saleSection->id);
    
    // 清除缓存
    Cache::forget('homepage_sale_products');
    Cache::forget('homepage_sections');
}
```

### 4. Redis缓存服务

```php
/**
 * 从缓存获取首页产品，如果缓存不存在则重建
 */
public function getCachedHomepageProducts(int $sectionId): Collection
{
    $cacheKey = "homepage_section_{$sectionId}_products";
    
    return Cache::remember($cacheKey, now()->addHours(3), function () use ($sectionId) {
        return $this->getHomepageSectionProductsWithStockAndDiscount(
            HomepageSection::findOrFail($sectionId)
        );
    });
}

/**
 * 清除与产品相关的所有缓存
 */
public function clearProductRelatedCaches(int $productId): void
{
    // 查找产品所在的所有首页部分
    $sectionIds = DB::table('homepage_section_product')
        ->where('product_id', $productId)
        ->pluck('homepage_section_id');
    
    // 清除每个部分的缓存
    foreach ($sectionIds as $sectionId) {
        Cache::forget("homepage_section_{$sectionId}_products");
    }
    
    // 清除产品状态缓存
    Cache::forget("product_{$productId}_stock_status");
    
    // 清除通用缓存
    Cache::forget('homepage_featured_products');
    Cache::forget('homepage_new_arrivals');
    Cache::forget('homepage_sale_products');
    Cache::forget('homepage_products');
    Cache::forget('homepage_sections');
}
```

## 实施时间线

- 首页库存实时更新优化：1周
- 产品展示效果分析系统：2周
- 促销活动自动同步：1周
- Redis缓存系统集成：1周

总计约5周时间完成所有增强功能。

## 测试计划

1. 单元测试：测试各组件独立功能
2. 集成测试：验证组件间交互
3. 负载测试：评估高流量下系统性能
4. A/B测试：评估新功能效果

## 优先级排序

1. Redis缓存系统（提升性能）
2. 首页库存实时更新优化（改善用户体验）
3. 促销活动自动同步（增加转化率）
4. 产品展示效果分析（长期优化工具） 
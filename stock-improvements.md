# 商品库存计算改进

## 问题描述

前端应用展示产品模板时，商品库存数量显示不准确：
- 之前的代码仅使用了`product`表中的`stock_quantity`字段或备用的`stock`字段
- 这种方式无法正确反映分布在多个仓库/门店的库存总量
- 商品的真实库存应该是`stocks`表中所有相关记录的数量总和

## 实施的改进

### 1. 添加`getTotalStock`方法到`Product`模型

```php
/**
 * 获取产品的总库存数量（从stock表中汇总）
 * 
 * @return int
 */
public function getTotalStock(): int
{
    try {
        // 获取所有库存记录并汇总数量
        $totalQuantity = $this->stocks()->sum('quantity');
        
        // 如果没有库存记录，则使用product表中的stock字段
        if ($totalQuantity <= 0) {
            return (int)$this->stock;
        }
        
        return (int)$totalQuantity;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Failed to get total stock for product', [
            'product_id' => $this->id,
            'error' => $e->getMessage()
        ]);
        
        // 出错时返回product表中的stock字段
        return (int)$this->stock;
    }
}
```

### 2. 修改API控制器中的商品库存计算逻辑

在`ProductTemplateController.php`和`HomepageController.php`的`formatTemplate`方法中：

```php
'stock_quantity' => $product->getTotalStock(), // 使用getTotalStock方法获取总库存
```

替代了原来的代码：

```php
'stock_quantity' => (int)$product->stock_quantity ?: (int)$product->stock,
```

## 实现优势

1. **数据准确性**：
   - 库存数量现在反映了所有仓库/门店的实际库存总和
   - 当商品分布在多个位置时，能正确合并计算总数

2. **数据完整性**：
   - 使用`stocks`表作为主要数据源，更符合系统设计
   - 保留`product.stock`字段作为备用，确保数据连续性

3. **容错处理**：
   - 添加了异常处理，防止库存查询失败导致系统错误
   - 当查询出错时，提供合理的默认值

## 后续建议

1. **数据同步机制**：
   - 考虑建立一个定时任务，将`stocks`表的汇总数据同步到`product.stock`字段
   - 这样可以提高查询性能，减少每次需要计算的开销

2. **缓存优化**：
   - 对频繁查询的产品库存数据进行缓存
   - 在库存变动时主动更新缓存

3. **库存预警**：
   - 基于新的总库存计算方法，完善库存预警机制
   - 当总库存低于最小库存阈值时触发通知 
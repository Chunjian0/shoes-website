# 商品参数选择与库存显示指南

## 概述

在电子商务应用中，能够根据用户选择的参数组合显示正确的商品库存和技术参数是提升用户体验的关键因素。本指南说明了我们如何在前端应用中实现这一功能。

## 实现流程

1. **参数选择 → 商品匹配 → 信息展示**
   - 用户选择产品参数（如颜色、尺寸等）
   - 系统匹配对应的具体商品变体
   - 动态显示该变体的库存、价格和技术参数

## 主要改进

### 1. 商品匹配逻辑优化

```javascript
// 查找匹配当前选择参数的产品
const findMatchingProduct = (templateData, params) => {
  // 获取产品列表
  const products = templateData.linked_products || templateData.related_products;
  
  // 1. 使用parameter_group匹配参数
  const exactMatch = products.find(product => {
    if (product.parameter_group) {
      // 解析parameter_group字符串 (格式如 "color=red;size=small")
      const productParams = product.parameter_group.split(';').reduce((acc, pair) => {
        const [key, value] = pair.split('=');
        if (key && value) {
          acc[key.trim()] = value.trim();
        }
        return acc;
      }, {});
      
      // 检查所有已选参数是否匹配
      for (const [key, value] of Object.entries(params)) {
        if (productParams[key.toLowerCase()] !== value.toLowerCase()) {
          return false;
        }
      }
      
      return true;
    }
    
    // 2. 兼容使用variations字段的情况
    if (product.variations) {
      for (const [key, value] of Object.entries(params)) {
        if (product.variations[key] !== value) {
          return false;
        }
      }
      return true;
    }
    
    return false;
  });
  
  // 找到匹配商品，确保数据完整性
  if (exactMatch) {
    return {
      ...exactMatch,
      price: exactMatch.price || 0,
      original_price: exactMatch.original_price || exactMatch.price || 0,
      stock_quantity: exactMatch.stock_quantity || 0,
      discount_percentage: exactMatch.discount_percentage || 0,
      parameters: exactMatch.parameters || {}
    };
  }
  
  // 没有找到匹配商品，返回第一个商品
  return null;
}
```

### 2. 后端API改进

1. **库存计算**:
   - 添加`getTotalStock()`方法到`Product`模型
   - 聚合`stocks`表中的库存数量
   - 确保API响应包含准确的库存数据

2. **参数数据**:
   - 确保`parameters`字段包含在每个linked_product中
   - 字段结构: `parameters: Record<string, string>`

### 3. 前端显示优化

1. **商品信息展示**:
   ```jsx
   {selectedProduct && (
     <div className="mb-6">
       <div className="flex items-baseline mb-2">
         <span className="text-2xl font-bold text-blue-600">${selectedProduct.price.toFixed(2)}</span>
         {selectedProduct.original_price && (
           <span className="ml-2 text-lg line-through text-gray-500">
             ${selectedProduct.original_price.toFixed(2)}
           </span>
         )}
         <div className="ml-auto">
           {selectedProduct.stock_quantity > 0 ? (
             <span className="bg-green-100 text-green-800">
               Stock: {selectedProduct.stock_quantity} units
             </span>
           ) : (
             <span className="bg-red-100 text-red-800">
               Out of stock
             </span>
           )}
         </div>
       </div>
     </div>
   )}
   ```

2. **技术参数展示**:
   ```jsx
   {/* 商品特定参数 */}
   {selectedProduct.parameters && Object.keys(selectedProduct.parameters).length > 0 && (
     <div className="specifications-section">
       <h3>Product Specifications</h3>
       {Object.entries(selectedProduct.parameters).map(([key, value]) => (
         <div key={key} className="spec-item">
           <span className="spec-label">{key}</span>
           <span className="spec-value">{value}</span>
         </div>
       ))}
     </div>
   )}
   ```

## 服务端数据准备

### API 返回数据结构

```json
{
  "id": 1,
  "title": "Optical Frame",
  "description": "...",
  "parameters": [
    { "name": "Color", "values": ["Red", "Blue", "Black"] },
    { "name": "Size", "values": ["S", "M", "L"] }
  ],
  "linked_products": [
    {
      "id": 101,
      "name": "Optical Frame - Red S",
      "sku": "OF-RS-101",
      "price": 99.99,
      "original_price": 129.99,
      "discount_percentage": 23,
      "stock_quantity": 15,
      "parameter_group": "Color=Red;Size=S",
      "parameters": {
        "material": "Titanium",
        "weight": "25g",
        "warranty": "2 years"
      }
    },
    {
      "id": 102,
      "name": "Optical Frame - Blue M",
      "sku": "OF-BM-102",
      "price": 89.99,
      "stock_quantity": 8,
      "parameter_group": "Color=Blue;Size=M",
      "parameters": {
        "material": "Plastic",
        "weight": "22g",
        "warranty": "1 year"
      }
    }
  ]
}
```

## 最佳实践

1. **数据完整性检查**
   - 始终为缺失值提供合理默认值
   - 在后端和前端都实施验证

2. **性能考虑**
   - 使用记忆化（memoization）减少重复计算
   - 合理使用缓存减少不必要的API请求

3. **用户体验优化**
   - 库存不足时，明确提示用户
   - 动态显示参数变化带来的价格和库存变化
   - 高亮显示当前选择的参数

## 调试提示

添加调试模式，在开发环境中显示：
- 当前选择的参数
- 匹配的商品信息
- API响应数据
- 库存计算逻辑 
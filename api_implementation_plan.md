# 电子商务前端-后端API连接计划

## 问题分析

目前前端使用模拟数据，需要连接到真实的后端API。几个关键路由已存在但返回模拟数据，需要修改这些路由以使用控制器方法返回实际数据库数据。

## 需要修改的API端点

1. `/products/featured` - 获取精选产品
2. `/products/new-arrivals` - 获取新品
3. `/products/sale` - 获取促销产品
4. `/product-categories` - 获取所有产品类别

## 当前路由状态

1. `/products/featured` - 当前返回硬编码的模拟数据，需要使用`ProductController@getFeaturedProducts`
2. `/products/new-arrivals` - 当前返回硬编码的模拟数据，需要使用`ProductController@getNewArrivals`
3. `/products/sale` - 当前返回硬编码的模拟数据，需要使用`ProductController@getPromotionProducts`
4. `/product-categories` - 已连接到`ProductCategoryController@index`，但可能需要检查返回格式

## 控制器方法状态

1. `ProductController@getFeaturedProducts` - 已实现，从数据库获取带`is_featured=true`标记的产品
2. `ProductController@getNewArrivals` - 已实现，从数据库获取最新创建的产品
3. `ProductController@getPromotionProducts` - 已实现，获取带折扣或标记为促销的产品
4. `ProductCategoryController@index` - 已实现，获取所有活跃的产品类别

## 响应格式比较

### 当前模拟数据格式:
```json
{
  "success": true,
  "products": [
    {
      "id": 1,
      "name": "精选产品 1",
      "sku": "SKU-F001",
      "category_id": 1,
      "category_name": "运动鞋",
      "brand": "测试品牌",
      "price": 1099,
      "sale_price": null,
      "discount_percentage": 0,
      "is_featured": true,
      "is_new": false,
      "is_sale": false,
      "images": ["https://placehold.co/300x300?text=Featured+1"],
      "is_active": true
    }
  ]
}
```

### 控制器方法格式:
```json
{
  "status": "success",
  "data": {
    "products": [...],
    "pagination": {
      "total": 10,
      "per_page": 12,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

## 执行步骤

1. ✓ 分析现有API路由和控制器方法
2. ✓ 检查响应格式差异
3. ✓ 制定实现计划
4. ✓ 修改`routes/api.php`文件，更新路由指向正确的控制器方法
5. ✓ 创建API适配器控制器以统一响应格式
   - ✓ `ProductApiAdapter` - 用于适配产品API响应
   - ✓ `CategoryApiAdapter` - 用于适配类别API响应
6. ✓ 更新路由使用新的适配器控制器
7. ✓ 创建测试脚本验证API响应格式
8. □ 最终测试前端与后端的端到端连接

## 适配器控制器说明

我们创建了两个适配器控制器，用于确保API响应格式与前端期望一致：

1. `ProductApiAdapter`
   - 包装`ProductController`的方法调用
   - 转换响应格式从`{ "status": "success", "data": { "products": [...] } }`到`{ "success": true, "products": [...] }`
   - 处理错误情况，保持统一的错误响应格式

2. `CategoryApiAdapter`
   - 包装`ProductCategoryController`的方法调用
   - 转换响应格式从`{ "status": "success", "data": { "categories": [...] } }`到`{ "success": true, "categories": [...] }`
   - 提供统一的错误处理机制

这种适配器模式允许后端继续使用其现有的响应格式，同时确保前端能接收到预期格式的数据，减少了前端的适配工作。

## 测试脚本

为了验证API响应格式，我们创建了`test_api_formats.php`测试脚本。此脚本会：

1. 请求所有相关API端点
2. 检查HTTP状态码
3. 验证响应JSON格式是否包含必要的字段（`success`、`products`或`categories`）
4. 显示基本的响应统计信息（如产品数量和字段列表）

运行此脚本可以快速验证所有API端点是否返回了与前端期望一致的格式。使用方法：
```
php test_api_formats.php
```

输出示例：
```
=== API响应格式测试 ===

测试 精选产品 (/api/products/featured):
  状态码: 200
  成功标志: 成功
  产品数量: 8
  产品示例字段: id, name, sku, brand, price, selling_price, image, images, ...

...

=== 测试完成 ===
```

## 注意事项

- 需要确保返回的数据格式与前端期望的格式一致，特别是产品字段的命名和嵌套结构
- 需要保持分页参数一致，如果前端期望特定的分页结构
- 需要处理可能的错误情况，确保API返回统一格式的错误消息 
# 后端API修改总结

## 问题描述

前端应用在获取产品模板详情时，发现模板中的关联产品（linked_products）缺少关键信息：
- 价格（price）值为0
- 原价（original_price）缺失或为0
- 折扣百分比（discount_percentage）缺失或为0
- 库存数量（stock_quantity）为0
- 技术参数（parameters）缺失

这导致前端无法正确显示产品价格和折扣信息。

## 修改内容

我们对以下文件进行了修改：

1. **ProductTemplateController.php**
   ```php
   private function formatTemplate($template)
   {
       // 现在获取完整的价格信息
       $price = (float)$product->selling_price; // 使用selling_price作为主要价格
       $originalPrice = (float)$product->price;  // 原始价格
       $discountPercentage = (float)$product->discount_percentage; // 折扣百分比
       
       // 对于有折扣但没有原始价格的情况，根据折扣计算原始价格
       if ($discountPercentage > 0 && $originalPrice <= 0 && $price > 0) {
           $originalPrice = round($price / (1 - $discountPercentage / 100), 2);
       }
       
       // 对于有原始价格但没有折扣的情况，计算折扣百分比
       if ($originalPrice > $price && $price > 0 && $discountPercentage <= 0) {
           $discountPercentage = round((1 - $price / $originalPrice) * 100, 2);
       }
       
       return [
           // 其他属性...
           'price' => $price,
           'original_price' => $originalPrice,
           'discount_percentage' => $discountPercentage,
           'stock_quantity' => (int)$product->stock_quantity ?: (int)$product->stock,
           'parameters' => $product->parameters ?: []
       ];
   }
   ```

2. **HomepageController.php**
   - 同样修改了`formatTemplate`方法，确保所有通过主页API获取的产品模板数据都包含完整的价格和库存信息
   - 添加了对`selling_price`和`price`字段的优先级处理
   - 添加了对缺失库存数据的后备处理

## 修改效果

1. **数据完整性**：
   - 产品模板的关联产品现在包含正确的价格、原价和折扣信息
   - 库存数量正确显示或默认为0
   - 技术参数现在作为JSON对象返回

2. **价格计算逻辑**：
   - 当有折扣但缺少原价时，根据折扣百分比自动计算原价
   - 当有原价但缺少折扣时，自动计算折扣百分比
   - 当价格数据不完整时提供合理的默认值和回退方案

3. **前端显示改进**：
   - 产品价格正确显示
   - 折扣商品能正确显示原价和折扣信息
   - 库存状态正确显示

## 备注

1. 这些修改只更新了API响应数据的格式化方式，不需要更改数据库结构或模型定义。

2. 前端需要相应更新以正确处理新的数据格式，特别是：
   - 处理可能的0价格或库存
   - 适应新的参数结构
   - 确保在价格/库存数据缺失时有适当的后备显示

3. 在完整解决方案中，应考虑添加:
   - 数据验证
   - 更完善的错误处理
   - 缓存机制优化 
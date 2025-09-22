# 购物车处理问题修复计划

## 问题分析

通过代码检查，我们发现了以下问题：

1. 前端在TemplateDetailPage中正确设置了`product_id: selectedProductId`
2. cartService.ts的addToCart传递过程中正确处理了参数
3. 但实际发送到API的请求中，显示的参数是`product_id: 1`而不是预期的`product_id: 5`
4. 问题可能出在前端的数据处理或API调用过程中

通过调试输出发现，前端发送了：
```json
{
  "product_id": 1,
  "quantity": 1,
  "specifications": {
    "color": "yellow",
    "size": "big"
  },
  "template_id": 1
}
```

但实际上应该发送：
```json
{
  "product_id": 5,
  "quantity": 1,
  "specifications": {
    "color": "yellow",
    "size": "big"
  },
  "template_id": 1
}
```

## 修复方案

我们需要对购物车处理逻辑进行全面重构，确保：

1. 前端正确传递参数到后端
2. 后端完全负责验证和比较逻辑
3. 减少前端的复杂比较和处理

### 前端修改

1. **简化API请求参数准备**：
   - 修改`cartService.ts`中的`addToCart`函数，确保始终使用正确的product_id
   - 添加调试日志，记录关键点的数据流

2. **强化TemplateDetailPage**：
   - 在`handleAddToCart`函数中添加明确的日志，跟踪product_id
   - 确保`selectedProductId`始终正确传递

3. **增强错误处理**：
   - 添加更友好的用户提示
   - 在发生错误时提供明确的反馈

### 后端修改

1. **增强日志记录**：
   - 在关键处理步骤添加详细日志
   - 记录请求原始参数，便于调试

2. **验证确保product_id正确**：
   - 后端应该负责所有比较和验证逻辑
   - 确保收到的product_id直接用于查询和更新

3. **排除干扰因素**：
   - 检查是否有中间件或拦截器修改了请求数据
   - 确保product_id参数不被覆盖

## 具体修改步骤

1. 修改`TemplateDetailPage.tsx`中的`handleAddToCart`函数
2. 修改`cartService.ts`中的`addToCart`函数，简化逻辑
3. 更新`api.ts`中购物车相关API调用
4. 在`CartController.php`中添加更明确的日志和类型强制转换

## 预期结果

修复完成后，当选择产品ID=5的产品添加到购物车时，系统将：
1. 正确发送product_id=5到后端
2. 后端正确保存product_id=5到数据库
3. 购物车中显示正确的产品和规格
4. 用户收到明确的成功/失败提示 
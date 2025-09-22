# 首页API响应清理总结

## 清理内容

成功从API响应中删除了以下冗余字段：

1. **重复的图片URL字段**
   - `banner_image` - 已被`banners`数组中的`image_url`取代
   - `offer_image` - 与`offer_image_url`功能重复

2. **过时的设置字段**
   - `new_products_days` - 与`new_product_days`功能重复
   - `homepage_min_stock_threshold` - 已不再使用
   - `homepage_new_products_days` - 与`new_product_days`功能重复
   - `new_products_display_days` - 与`new_product_days`功能重复

3. **已废弃的单一banner字段**
   - `banner_title`
   - `banner_subtitle`
   - `banner_button_text`
   - `banner_button_link`
   - `banner_image_url`

4. **不再使用的通用设置**
   - `site_title`
   - `site_description`

## 保留的向后兼容字段

为确保前端代码继续正常工作，保留了23个必要的兼容性字段，包括：

- 精选产品相关设置 (`featured_products_*`)
- 优惠相关设置 (`offer_*`)
- 轮播相关设置 (`carousel_*`)
- 通用设置 (`products_per_page`, `new_product_days`, 等)

## 实现方式

1. **白名单过滤**：采用了白名单方式，仅保留指定的必要字段，而不是尝试去除特定字段
2. **结构化数据完整性**：确保结构化`data`对象包含所有必要的数据段：`banners`, `carousel`, `featured_products`, `offer`和`settings`
3. **无缝过渡**：修改方式确保现有前端代码不会因为API响应变化而中断

## 测试结果

测试验证表明：
- ✅ 所有已识别的冗余字段都已成功删除
- ✅ 保留了所有必要的向后兼容字段
- ✅ 结构化数据完整性得到保证

## 后续步骤

1. **前端适配**：长期计划应该是更新前端代码，使其完全使用结构化的`data`对象中的数据
2. **完全清理**：一旦前端适配完成，可以考虑完全移除扁平结构字段，只保留结构化的`data`对象
3. **API版本控制**：考虑引入正式的API版本控制（例如`/api/v2/homepage/settings`），以便未来可以进行更重大的改变 
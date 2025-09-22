# 首页API优化计划

## 当前状况

从API响应分析，当前的API返回了许多可能不再需要或重复的字段。这些字段包括：

### 冗余/过时字段

1. 单一banner字段：
   - `banner_title`, `banner_subtitle`, `banner_button_text`, `banner_button_link`, `banner_image_url`
   - 这些已被`banners`数组取代，不再需要

2. 重复的图片URL字段：
   - `banner_image` (与 `banner_image_url` 重复)
   - `offer_image` (与 `offer_image_url` 重复)

3. 过时的设置：
   - `site_title`, `site_description` (已在管理界面中移除)
   - `new_products_days` (与 `new_product_days` 重复)
   - `homepage_min_stock_threshold` (与系统中的`stock_threshold`功能重复)
   - `homepage_new_products_days` (与 `new_product_days` 重复)

## 优化方案

### 1. API响应结构优化

新的API响应结构应分为以下主要部分：

```json
{
  "success": true,
  "data": {
    "banners": [
      {
        "id": 1,
        "title": "Banner Title",
        "subtitle": "Banner Subtitle",
        "button_text": "Button Text",
        "button_link": "/link",
        "image_url": "http://example.com/image.jpg",
        "order": 1,
        "is_active": true
      }
    ],
    "carousel": {
      "autoplay": true,
      "delay": 5000,
      "transition": "slide",
      "show_navigation": true,
      "show_indicators": true
    },
    "featured_products": {
      "title": "Featured Products Collection",
      "subtitle": "Discover our selection of premium quality shoes",
      "button_text": "View All Featured Products",
      "button_link": "/products?featured=true"
    },
    "offer": {
      "title": "Summer Sale",
      "subtitle": "50% Off Store-wide, This Week Only",
      "button_text": "View Sale",
      "button_link": "/products?sale=true",
      "image_url": "http://example.com/sale.jpg"
    },
    "settings": {
      "products_per_page": 12,
      "new_product_days": 30,
      "layout": "standard",
      "show_brands": true,
      "show_promotion": true,
      "auto_add_new_products": true,
      "auto_add_sale_products": true
    }
  }
}
```

### 2. 移除项目

以下字段应从API响应中移除：

1. 所有单一banner字段：
   - `banner_title`
   - `banner_subtitle`
   - `banner_button_text`
   - `banner_button_link`
   - `banner_image_url`
   - `banner_image`

2. 重复的图片字段：
   - `offer_image`

3. 过时的设置字段：
   - `site_title`
   - `site_description`
   - `new_products_days`
   - `homepage_min_stock_threshold`
   - `homepage_new_products_days`

### 3. 字段重组

1. 将所有`featured_products_`前缀的字段组合到`featured_products`对象中
2. 将所有`carousel_`前缀的字段组合到`carousel`对象中
3. 将所有`offer_`前缀的字段组合到`offer`对象中
4. 将其他设置字段组合到`settings`对象中

### 4. 实现计划

1. 修改`HomepageSettingsAdapter`控制器的`getHomepageSettings`方法
2. 重构默认值设置方式
3. 创建新的结构化的响应数据
4. 保留必要的向后兼容性

## 前后端兼容性考虑

由于前端React应用依赖于API的响应格式，我们需要确保API的变化不会破坏前端功能。有两种方式可以处理这个问题：

1. **渐进式更新**：保留旧的字段结构，同时引入新的结构，让前端有时间适应
2. **版本化API**：创建一个新的API端点(v2)，同时保留旧的端点

对于本次更新，我们将采用渐进式更新，确保现有前端可以继续工作，同时为前端的更新提供更好的数据结构。 
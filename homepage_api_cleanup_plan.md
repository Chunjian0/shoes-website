# 首页API响应清理计划

## 当前数据结构分析

当前API响应同时包含了两种数据结构：
1. 扁平结构（顶层字段）
2. 嵌套结构（`data`对象内的字段）

## 需要删除的冗余字段

### 1. 重复的图片URL字段
- `banner_image` - 已被`banners`数组取代，且与`banner_image_url`重复
- `offer_image` - 与`offer_image_url`重复

### 2. 过时的设置字段
- `new_products_days` - 与`new_product_days`重复
- `homepage_min_stock_threshold` - 已不再使用
- `homepage_new_products_days` - 与`new_product_days`重复
- `new_products_display_days` - 与`new_product_days`重复

### 3. 旧版单一banner字段
- ~~`banner_title`~~ - 已被移除，仅保留了`banners`数组
- ~~`banner_subtitle`~~ - 已被移除，仅保留了`banners`数组
- ~~`banner_button_text`~~ - 已被移除，仅保留了`banners`数组
- ~~`banner_button_link`~~ - 已被移除，仅保留了`banners`数组
- ~~`banner_image_url`~~ - 已被移除，仅保留了`banners`数组

### 4. 其他可能不再需要的设置
- `site_title` - 如果系统已不再使用此设置
- `site_description` - 如果系统已不再使用此设置

## 清理方案

1. **修改`HomepageSettingsAdapter.php`**:
   - 从响应中移除重复的图片URL字段
   - 从响应中移除过时的设置字段
   - 评估是否需要保留`site_title`和`site_description`

2. **保留向后兼容性**:
   - 保留结构化的`data`对象
   - 确保Banner信息主要从`banners`数组中获取

## 实施建议

由于前端已经进行了适配，可以安全地移除这些冗余字段而不影响功能。具体步骤：

1. 更新`HomepageSettingsAdapter.php`中的`getHomepageSettings`方法
2. 只保留必要的扁平结构字段以确保向后兼容
3. 长期规划完全迁移到结构化的`data`对象 

{
  "featured_products_title": "Featured Products",
  "featured_products_subtitle": "Our carefully selected premium eyewear known for exceptional quality and style",
  "featured_products_button_text": "View All Featured Products",
  "featured_products_button_link": "/products?featured=true",
  "featured_products_banner_title": "Featured Products Collection",
  "featured_products_banner_subtitle": "Discover our selection of premium quality shoes",
  "offer_title": "Summer Sale",
  "offer_subtitle": "50% Off Store-wide, This Week Only",
  "offer_button_text": "View Sale",
  "offer_button_link": "/products?sale=true",
  "offer_image_url": "media/offers/default-sale.jpg",
  "carousel_autoplay": "1",
  "carousel_delay": "5000",
  "carousel_transition": "slide",
  "carousel_show_navigation": "1",
  "carousel_show_indicators": "1",
  "products_per_page": "12",
  "new_product_days": "30",
  "layout": "standard",
  "show_brands": "1",
  "show_promotion": "1",
  "auto_add_new_products": "1",
  "auto_add_sale_products": "1",
  "banner_image": "https://placehold.co/1920x600/e6f7ff/0099cc?text=Premium+Footwear+Collection",
  "offer_image": "https://placehold.co/800x600/fff5e6/ff9900?text=Summer+Sale",
  "new_products_days": "30",
  "homepage_min_stock_threshold": "5",
  "homepage_new_products_days": "30",
  "new_products_display_days": "30",
  "media_id": null,
  "offer_media_id": null,
  "site_title": "Optic System",
  "site_description": "Your one-stop shop for quality optical products",
  "banners": [
    {
      "id": 1,
      "title": "hihi",
      "subtitle": "asdasd",
      "button_text": "view",
      "button_link": "/product",
      "is_active": true,
      "order": 1,
      "image_url": "http://localhost:2268/storage/banners/fN9zpZUgrMJhBqB5w8QB6howt1AU2aw4KSvZOAlj.png",
      "image": "http://localhost:2268/storage/banners/fN9zpZUgrMJhBqB5w8QB6howt1AU2aw4KSvZOAlj.png"
    },
    {
      "id": 2,
      "title": "asda",
      "subtitle": "daasdas",
      "button_text": "dasda",
      "button_link": "/product11",
      "is_active": true,
      "order": 2,
      "image_url": "http://localhost:2268/storage/banners/GARnwfmKDIqsV8gCsHerUndLAtzZCeX91s9VsgNX.png",
      "image": "http://localhost:2268/storage/banners/GARnwfmKDIqsV8gCsHerUndLAtzZCeX91s9VsgNX.png"
    }
  ],
  "success": true,
  "data": {
    "banners": [
      {
        "id": 1,
        "title": "hihi",
        "subtitle": "asdasd",
        "button_text": "view",
        "button_link": "/product",
        "is_active": true,
        "order": 1,
        "image_url": "http://localhost:2268/storage/banners/fN9zpZUgrMJhBqB5w8QB6howt1AU2aw4KSvZOAlj.png",
        "image": "http://localhost:2268/storage/banners/fN9zpZUgrMJhBqB5w8QB6howt1AU2aw4KSvZOAlj.png"
      },
      {
        "id": 2,
        "title": "asda",
        "subtitle": "daasdas",
        "button_text": "dasda",
        "button_link": "/product11",
        "is_active": true,
        "order": 2,
        "image_url": "http://localhost:2268/storage/banners/GARnwfmKDIqsV8gCsHerUndLAtzZCeX91s9VsgNX.png",
        "image": "http://localhost:2268/storage/banners/GARnwfmKDIqsV8gCsHerUndLAtzZCeX91s9VsgNX.png"
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
      "image_url": "media/offers/default-sale.jpg"
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
# 首页管理系统功能总结

## 主要功能概述

首页管理系统(`http://localhost:2268/admin/homepage`)提供了一个灵活的界面，用于管理电商网站首页的各个区块。系统允许管理员创建、编辑、排序和删除不同类型的首页区块，包括横幅/轮播图、特色产品、分类展示、促销产品等。

## 区块类型与详细功能

### 1. 横幅区块 (Banner)

**功能：**
- 支持单图横幅和多图轮播
- 每个图片可设置标题、副标题、按钮文本和链接
- 轮播设置包括：自动播放、延迟时间、过渡效果、指示器和导航按钮显示控制

**参数格式：**
```json
{
  "images": [
    {
      "id": "banner_1234",
      "image": "/storage/banners/image1.png",
      "title": "Banner Title",
      "subtitle": "Banner Subtitle",
      "button_text": "Shop Now",
      "button_link": "/products"
    }
  ],
  "settings": {
    "autoplay": true,
    "delay": 5000,
    "transition": "slide",
    "show_indicators": true,
    "show_navigation": true,
    "show_as_carousel": true
  }
}
```

### 2. 特色产品区块 (Featured Products)

**功能：**
- 展示精选产品
- 设置区块标题和副标题
- 选择需要显示的产品

**参数格式：**
```json
{
  "product_ids": [1, 2, 3, 4]
}
```

### 3. 分类展示区块 (Category Showcase)

**功能：**
- 展示重要产品分类
- 设置区块标题和副标题
- 选择需要显示的分类

**参数格式：**
```json
{
  "category_ids": [1, 2, 3]
}
```

### 4. 促销产品区块 (Sale Products)

**功能：**
- 展示打折商品
- 设置区块标题和副标题
- 选择需要显示的促销产品
- 自动计算并显示折扣率

**参数格式：**
```json
{
  "product_ids": [5, 6, 7, 8]
}
```

### 5. 文本内容区块 (Text Content)

**功能：**
- 添加纯文本内容
- 支持富文本编辑

**参数格式：**
```json
{
  "content": "<p>Rich text content here</p>"
}
```

### 6. 自定义HTML区块 (Custom HTML)

**功能：**
- 添加自定义HTML代码
- 完全控制区块的HTML结构

**参数格式：**
```json
{
  "html": "<div class=\"custom-section\">Custom HTML content</div>"
}
```

## API接口规格

### 获取所有首页区块

- **端点**: `/api/admin/homepage/sections`
- **方法**: GET
- **响应格式**:
```json
{
  "sections": [
    {
      "id": 1,
      "title": "Welcome to Our Store",
      "subtitle": "Find the best products",
      "type": "banner",
      "content": {...},
      "layout": "full-width",
      "position": 1,
      "is_active": true,
      "background_color": null,
      "text_color": null,
      "padding": null,
      "custom_class": null
    }
  ]
}
```

### 创建新区块

- **端点**: `/api/admin/homepage/sections`
- **方法**: POST
- **请求格式**:
```json
{
  "title": "New Section Title",
  "subtitle": "Section subtitle",
  "type": "banner",
  "content": {...},
  "layout": "full-width",
  "is_active": true,
  "background_color": "#ffffff",
  "text_color": "#000000",
  "padding": "2rem",
  "custom_class": "my-custom-class"
}
```

### 更新区块

- **端点**: `/api/admin/homepage/sections/{id}`
- **方法**: PUT
- **请求格式**: 与创建区块相同

### 删除区块

- **端点**: `/api/admin/homepage/sections/{id}`
- **方法**: DELETE

### 重新排序区块

- **端点**: `/api/admin/homepage/sections/reorder`
- **方法**: POST
- **请求格式**:
```json
{
  "order": [3, 1, 4, 2]
}
```

### 上传横幅图片

- **端点**: `/api/admin/homepage/upload-banner`
- **方法**: POST
- **请求格式**: Multipart Form Data
  - `image`: 文件
  - `section_id`: 区块ID

### 获取产品列表(用于选择)

- **端点**: `/api/admin/products`
- **方法**: GET
- **参数**: `?search=keyword&category=id&limit=20&page=1`

### 获取分类列表(用于选择)

- **端点**: `/api/admin/categories`
- **方法**: GET
- **参数**: `?search=keyword&parent=id&limit=20&page=1`

## 特殊功能

### 自动管理

- 新品自动管理：自动将新添加的产品添加到新品区块
- 促销产品自动管理：自动将有折扣的产品添加到促销区块

### 缓存管理

- 首页区块数据会被缓存以提高性能
- 当任何区块更新时，系统会自动清除缓存

## 系统数据结构

### HomepageSection 表结构

- `id`: 唯一标识符
- `title`: 区块标题
- `subtitle`: 区块副标题
- `type`: 区块类型(banner, featured_products, category_showcase, sale_products, text, custom)
- `content`: JSON格式的内容数据
- `layout`: 布局类型(full-width, contained, boxed, side-by-side, grid-2, grid-3, grid-4, carousel)
- `position`: 显示顺序
- `is_active`: 是否激活
- `background_color`: 背景颜色
- `text_color`: 文本颜色
- `padding`: 内边距
- `custom_class`: 自定义CSS类
- `created_at`: 创建时间
- `updated_at`: 更新时间
- `deleted_at`: 软删除时间

### 关联表结构

- `homepage_section_product`: 区块与产品的多对多关联
- `homepage_section_category`: 区块与分类的多对多关联 
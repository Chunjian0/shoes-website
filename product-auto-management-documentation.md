# 产品自动管理功能实现

## 功能概述

我们实现了产品的自动管理功能，包括：

1. **新品自动管理**：
   - 自动将新创建的产品添加到新品区域
   - 设置新品展示天数
   - 超过展示天数后自动移除新品标记

2. **促销产品自动管理**：
   - 自动将有折扣的产品添加到促销区域
   - 设置最小折扣百分比
   - 折扣结束后自动移除促销标记

3. **统一产品加载和前端筛选机制**：
   - 一次性加载所有产品信息
   - 前端实现产品筛选和分页
   - 支持多种筛选条件：类别、品牌、价格、折扣等

## 前端实现

### 前端界面

1. **新品自动管理设置界面**：
   - 自动添加开关
   - 新品展示天数设置
   - 自动移除开关

2. **促销产品自动管理设置界面**：
   - 自动添加开关
   - 最小折扣百分比设置
   - 自动移除开关

3. **产品筛选和分页组件**：
   - 产品列表显示
   - 前端分页控件
   - 筛选条件选择器

### JavaScript功能

1. **产品加载机制**：
   - `loadAvailableProducts` - 一次性加载所有产品信息
   - `displayAvailableProducts` - 显示产品并应用前端分页
   - `filterProducts` - 根据选择的条件筛选产品

2. **自动管理设置**：
   - `renderNewProductsAutoSettings` / `renderSaleProductsAutoSettings` - 渲染设置界面
   - `setupNewProductsAutoSettings` / `setupSaleProductsAutoSettings` - 设置事件监听
   - `saveNewProductsSettings` / `saveSaleProductsSettings` - 保存设置到后端
   - `loadAutoManagementSettings` - 加载现有设置

3. **选项卡初始化**：
   - `initializeNewProductsTab` / `initializeSaleProductsTab` - 初始化各选项卡内容和功能

## 后端实现

### API接口

1. **自动管理设置接口**：
   - `GET /api/homepage/auto-management-settings` - 获取自动管理设置
   - `POST /api/homepage/new-products/auto-management` - 更新新品自动管理设置
   - `POST /api/homepage/sale-products/auto-management` - 更新促销产品自动管理设置
   - `POST /admin/homepage/update-new-products-days` - 更新新品天数设置

2. **产品管理接口**：
   - `GET /admin/homepage/available-products` - 获取所有可用产品
   - `GET /admin/homepage/new-arrivals` - 获取新品列表
   - `GET /admin/homepage/sale-products` - 获取促销产品列表

### 命令行工具

1. **新品自动管理命令**：
   - `products:manage-new` - 自动管理新品，包括添加新品和移除过期新品
   
2. **促销产品自动管理命令**：
   - `products:manage-sale` - 自动管理促销产品，包括添加有折扣产品和移除无折扣产品

### 计划任务

- 每天凌晨2点自动管理新品
- 每天凌晨3点自动管理促销产品

## 数据库设置

使用以下设置来存储自动管理配置：

- `auto_add_new_products` - 是否自动添加新品
- `new_product_days` - 新品展示天数
- `auto_remove_new_products` - 是否自动移除过期新品
- `auto_add_sale_products` - 是否自动添加促销产品
- `auto_remove_sale_products` - 是否自动移除无折扣产品
- `min_discount_percent` - 最小折扣百分比

## 使用说明

1. 访问管理后台的首页管理界面
2. 切换到"新品"选项卡，配置新品自动管理设置
3. 切换到"促销产品"选项卡，配置促销产品自动管理设置
4. 系统将根据设置自动管理产品的显示

## 维护与调试

- 命令执行日志存储在 `storage/logs/manage-new-products.log` 和 `storage/logs/manage-sale-products.log`
- 可以手动运行 `php artisan products:manage-new` 和 `php artisan products:manage-sale` 命令进行测试
- 所有设置更改都会记录在系统日志中 
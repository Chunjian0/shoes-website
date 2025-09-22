# 主页改进任务计划

## 当前功能实现

### 模板切换实现
- HomePage.tsx 组件中支持多种模板 (CLASSIC, MINIMAL, MODERN, BOLD)
- 通过 activeTemplate 状态控制当前显示的模板
- 模板切换通过 TEMPLATE_CHANGE_EVENT 事件监听器实现
- getTemplateFromLayout 函数将字符串转换为 LayoutTemplate 枚举

### 产品加载功能
- 通过 homepageService.getHomePageData 方法获取所有数据
- 数据包括：settings, featuredProducts, newProducts, saleProducts, templates
- 使用 Redis 缓存优化性能，设置缓存时间为 5 分钟
- 产品数据从模板中提取：extractProductsFromTemplates 函数

### 轮播图设置
- 轮播图数据来自 settings.banners
- 轮播控制参数来自 settings.carousel
- 支持自动播放、导航和指示器
- MinimalTemplate 中通过 renderCarousel 函数渲染轮播图

## 任务计划

### 1. 删除其他模板，固定使用 Minimal 模板
- 修改 HomePage.tsx 组件，移除模板选择逻辑
- 直接渲染 MinimalTemplate 组件
- 保留所有必要的数据传递

### 2. 优化 API 加载速度
- 实现 Promise.all 并发加载多个 API 请求
- 优化 Redis 缓存策略
- 减少冗余数据传输
- 添加错误恢复机制

### 3. 美化产品模块
- 重新设计 TemplateCard 组件，增强视觉效果
- 为 Featured Products, New Arrivals, On Sale 部分添加动画效果
- 确保卡片布局响应式和美观
- 添加交互反馈效果

### 4. 交互动画和多设备支持
- 添加滚动动画、悬停效果和过渡动画
- 确保在所有设备上（移动端、平板、桌面）正常工作
- 添加循环动画效果
- 优化触摸手势支持

### 5. 保持 Header 功能一致
- 确保原有的搜索、用户菜单、购物车功能正常工作
- 不修改 Header 的样式和结构 
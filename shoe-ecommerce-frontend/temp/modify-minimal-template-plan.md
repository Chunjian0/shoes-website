# 修改MinimalTemplate使用EnhancedTemplateCard的计划

## 1. 问题分析

目前`MinimalTemplate.tsx`中的产品展示部分存在以下问题：
- 直接展示的是产品(Product)而不是产品模板(ProductTemplate)
- UI样式不够现代和美观
- 没有显示折扣信息
- 没有充分利用API提供的产品模板数据

## 2. 解决方案

1. 使用新创建的`EnhancedTemplateCard`组件替换当前的产品卡片渲染方式
2. 正确展示产品模板数据，包括折扣、价格范围和产品变体
3. 确保在移动设备上有良好的显示效果
4. 支持动画和交互效果

## 3. 修改步骤

### 步骤1: 导入EnhancedTemplateCard组件
在MinimalTemplate.tsx顶部添加导入：
```tsx
import EnhancedTemplateCard from '../enhanced/EnhancedTemplateCard';
```

### 步骤2: 修改Featured Products部分
在renderFeaturedSection方法中，将产品渲染逻辑替换为使用EnhancedTemplateCard：

1. 首先确保使用templates.featured数据
2. 使用EnhancedTemplateCard组件渲染产品模板
3. 添加适当的动画和网格布局

### 步骤3: 修改New Arrivals部分
在renderNewArrivalsSection方法中，同样应用EnhancedTemplateCard：

1. 使用templates.newArrival数据
2. 配置正确的高亮类和动画

### 步骤4: 修改Sale部分
在renderSaleSection方法中，应用相同的修改：

1. 使用templates.sale数据
2. 应用适当的视觉样式和动画效果

### 步骤5: 调整响应式布局
确保在所有屏幕尺寸上展示良好：

1. 调整网格列数
2. 添加合适的间距和边距
3. 确保移动设备上的触摸交互

## 4. 具体修改详情

### renderFeaturedSection方法修改

```jsx
{templates.featured && templates.featured.length > 0 ? (
  <motion.div 
    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
    initial={{ opacity: 0 }}
    whileInView={{ opacity: 1 }}
    viewport={{ once: true, margin: "-100px" }}
    transition={{ staggerChildren: 0.1 }}
  >
    {templates.featured.map((template, index) => (
      <EnhancedTemplateCard
        key={template.id}
        template={template}
        index={index}
        highlightClass="border-blue-100 hover:border-blue-200"
        mobileView={isMobile}
      />
    ))}
  </motion.div>
) : (
  // 没有产品时的显示...
)}
```

### renderNewArrivalsSection方法修改

```jsx
{templates.newArrival && templates.newArrival.length > 0 ? (
  <motion.div 
    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
    initial={{ opacity: 0 }}
    whileInView={{ opacity: 1 }}
    viewport={{ once: true, margin: "-100px" }}
    transition={{ staggerChildren: 0.1 }}
  >
    {templates.newArrival.map((template, index) => (
      <EnhancedTemplateCard
        key={template.id}
        template={template}
        index={index}
        highlightClass="border-green-100 hover:border-green-200"
        mobileView={isMobile}
      />
    ))}
  </motion.div>
) : (
  // 没有产品时的显示...
)}
```

### renderSaleSection方法修改

```jsx
{templates.sale && templates.sale.length > 0 ? (
  <motion.div 
    className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
    initial={{ opacity: 0 }}
    whileInView={{ opacity: 1 }}
    viewport={{ once: true, margin: "-100px" }}
  >
    {templates.sale.map((template, index) => (
      <EnhancedTemplateCard
        key={template.id}
        template={template}
        index={index}
        highlightClass="border-red-100 hover:border-red-200"
        mobileView={isMobile}
      />
    ))}
  </motion.div>
) : (
  // 没有产品时的显示...
)}
```

## 5. 优化注意事项

1. 确保在没有产品模板数据时显示合适的空状态UI
2. 检查动画性能，如果在低端设备上有问题，考虑降级
3. 确保图片加载状态正确处理
4. 可能需要调整卡片大小和间距以适应不同的屏幕尺寸
5. 考虑添加更多的可访问性支持(ARIA标签等)

## 6. 完成后的验证

1. 确认所有三个部分(Featured, New Arrivals, Sale)都正确显示产品模板
2. 验证折扣标签、价格和"多变体"指示器正确显示
3. 测试在不同设备上的显示效果
4. 验证动画和交互是否流畅
5. 确认卡片跳转链接工作正常 
# Layout替换计划

## 背景
我们已经创建了新的高端Header和Footer组件，放置在 `src/components/layout/` 下。现在需要替换老的Header和Footer组件，确保所有页面使用新的组件。

## 已完成
1. ✅ 修改`App.tsx`的导入路径，指向新的组件
   ```typescript
   import Header from './components/layout/Header';
   import Footer from './components/layout/Footer';
   ```

2. ✅ 确认`Layout.tsx`的导入路径也已经指向新组件
   ```typescript
   import Header from './components/layout/Header';
   import Footer from './components/layout/Footer';
   ```

## 待完成
1. 修改以下直接使用旧`Layout`组件的页面，替换为Fragment或直接返回内容：
   - ProductDetailPage.tsx
   - 可能还有其他页面

2. 确保所有页面都可以正确显示新的Header和Footer

## 实施步骤
1. 修改`ProductDetailPage.tsx`，删除对Layout的直接引用
2. 检查并修改其他可能使用了Layout的页面
3. 测试页面渲染和导航，确保Header和Footer在每个页面都正确显示 
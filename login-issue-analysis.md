# 前端登录问题分析

## 问题描述
前端登录界面在发送登录请求时报错：
```
POST http://localhost:3000/api/customer/login 404 (Not Found)
```

## 问题原因分析
通过检查代码，发现以下问题：

1. **API路径不匹配**：
   - 前端请求路径: `/api/customer/login`
   - 后端定义路径: `/customer/login`（实际前缀应为`/api/customer/login`）

2. **Vite代理配置问题**：
   - 当前Vite代理配置会将`/api`前缀替换为空字符串，导致请求变为`/customer/login`
   - 后端API路由定义在`api.php`中，默认已包含`/api`前缀，因此完整路径应为`/api/customer/login`

## 解决方案

修改Vite代理配置，取消rewrite规则，保留`/api`前缀。

Laravel中的`api.php`路由文件中定义的所有路由默认已经带有`/api`前缀，无需做任何修改。

## 详细修复步骤：

1. ✅ 修改`shoe-ecommerce-frontend/vite.config.ts`文件，移除rewrite规则
   ```javascript
   proxy: {
     '/api': {
       target: 'http://localhost:2268',
       changeOrigin: true,
       secure: false,
       // rewrite: (path) => path.replace(/^\/api/, ''), // 移除此行
       // ...
     }
   }
   ```

2. ✅ 确认后端API路由是正确配置的：
   - 在`routes/api.php`中，所有路由自动带有`/api`前缀
   - 正确的登录路由是`/api/customer/login`

3. ✅ 重启前端开发服务器，测试登录功能
   ```
   cd shoe-ecommerce-frontend
   npm run dev
   ```

## 实施计划

1. ✅ 分析问题原因
2. ✅ 确定解决方案
3. ✅ 修改Vite配置
4. ✅ 测试登录功能
5. ✅ 验证其他API请求是否正常

## 结果

修复Vite配置后，登录请求现在可以正确路由：
- 前端发送请求到 `/api/customer/login`
- Vite代理将请求转发到 `http://localhost:2268/api/customer/login`
- 这与Laravel后端路由匹配，登录功能现在正常工作

## 总结

问题的根本原因是Vite代理配置中的`rewrite`规则导致API前缀被错误地处理：

1. 前端请求 `/api/customer/login`
2. Vite代理重写规则 `rewrite: (path) => path.replace(/^\/api/, '')` 将请求路径改为 `/customer/login`
3. 后端接收请求，但找不到匹配 `/customer/login` 的路由（正确路由是 `/api/customer/login`）

通过移除重写规则，让代理配置保留 `/api` 前缀，请求现在能够正确匹配到后端定义的路由，解决了这个问题。

## 学习要点

1. **代理配置重要性**：在前后端分离项目中，代理配置对于API请求的正确路由至关重要
2. **API路径一致性**：确保前端请求路径与后端API路由定义一致
3. **调试技巧**：通过检查网络请求和代理日志，可以快速定位路由问题
4. **Laravel路由约定**：Laravel的`routes/api.php`中定义的路由默认带有`/api`前缀 
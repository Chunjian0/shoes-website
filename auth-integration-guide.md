# 认证功能集成指南

本指南详细说明如何测试和确认前后端认证功能的正确实现，以及如何解决可能遇到的问题。

## 1. 测试环境准备

### 后端环境要求
- PHP 8.0+
- Laravel 9.0+
- 正确配置的数据库连接
- 已安装并配置 Laravel Sanctum

### 前端环境要求
- Node.js 14.0+
- React 17.0+
- 正确配置的API基础URL

## 2. 测试文件说明

我们提供了以下测试文件来验证认证功能：

- `auth-test-index.php` - 测试工具入口页面
- `api-test.php` - PHP脚本，自动测试所有API端点
- `auth-test.html` - 交互式HTML测试页面，手动测试API端点

## 3. 运行测试

### 后端API测试
1. 将测试文件放置在您的Laravel项目根目录
2. 在浏览器中访问 `http://your-domain.com/auth-test-index.php`
3. 选择"运行PHP测试"按钮来自动执行后端测试
4. 查看测试结果，确认所有API端点正常工作

### 前端交互测试
1. 在浏览器中访问 `http://your-domain.com/auth-test-index.php`
2. 选择"运行HTML测试"按钮
3. 在交互式界面中手动测试各个功能
4. 查看请求和响应数据，确认API正常工作

## 4. 测试覆盖的功能

### 用户认证
- 用户注册 (`POST /api/register`)
- 用户登录 (`POST /api/login`)
- 用户登出 (`POST /api/logout`)

### 用户资料
- 获取用户资料 (`GET /api/user/profile`)
- 更新用户资料 (`PUT /api/user/profile`)
- 修改密码 (`PUT /api/user/password`)

### 会话管理
- 检查会话状态 (`GET /api/auth/check`)
- 会话Ping保活 (`GET /api/auth/ping`)

## 5. 故障排查

### API返回401未授权
- 检查请求头是否包含有效的认证令牌
- 检查令牌是否已过期
- 确认用户账户是否有效

### API返回验证错误(422)
- 检查请求数据是否符合验证规则
- 查看具体的验证错误消息

### 跨域(CORS)问题
- 配置Laravel的CORS中间件允许前端域名
- 在Laravel的`cors.php`配置文件中添加允许的域名
- 确保`config/cors.php`中设置了`'supports_credentials' => true`

### 令牌过期问题
- 调整Sanctum配置中的令牌过期时间
- 实现令牌自动刷新机制

## 6. 与前端集成

### 前端集成步骤
1. 配置`api.ts`中的API基础URL
2. 确保认证令牌存储在`localStorage`中
3. 在API请求中添加认证头部
4. 实现自动处理401错误和令牌过期的逻辑

### React集成代码示例

```typescript
// 在API拦截器中添加令牌
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  }
);

// 处理认证错误
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // 清除token和用户信息
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      // 重定向到登录页面
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### 保护路由示例

```tsx
<Route 
  path="/account" 
  element={
    <ProtectedRoute>
      <AccountPage />
    </ProtectedRoute>
  } 
/>
```

## 7. 安全建议

### 最佳实践
- 使用HTTPS传输所有请求
- 实现CSRF保护
- 设置令牌合理的过期时间
- 实现密码强度要求
- 启用两步验证
- 限制登录尝试次数

### CSRF保护配置
确保Laravel中的CSRF保护正确配置，并在前端正确处理CSRF令牌：

```php
// 在 app/Http/Kernel.php 中确保有这个中间件
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        // ...
    ],
];
```

```typescript
// 前端初始化CSRF保护
export const initCSRF = async (): Promise<void> => {
  try {
    await axios.get('/api/csrf-cookie', { withCredentials: true });
  } catch (error) {
    console.error('CSRF初始化失败:', error);
  }
};
```

## 8. 性能优化

### 前端优化
- 实现令牌本地缓存
- 添加请求防抖和节流
- 优化会话Ping频率

### 后端优化
- 缓存用户资料
- 优化数据库查询
- 设置合理的令牌有效期

## 9. 测试清单

在发布前，确保执行以下测试：

- [ ] 用户可以成功注册
- [ ] 用户可以成功登录
- [ ] 认证令牌正确存储在前端
- [ ] 认证令牌正确发送到后端
- [ ] 受保护的路由只对认证用户可用
- [ ] 令牌过期时正确处理
- [ ] 用户可以修改个人资料
- [ ] 用户可以修改密码
- [ ] 会话Ping保活机制正常工作
- [ ] 用户可以正常登出

## 10. 常见问题

### Q: 前端提示"Network Error"
A: 请检查API基础URL配置是否正确，后端服务器是否正常运行。

### Q: 登录成功但无法访问受保护资源
A: 检查认证令牌是否正确传递，以及令牌格式是否为`Bearer {token}`。

### Q: 令牌很快就过期
A: 在`config/sanctum.php`中调整`expiration`值，增加令牌有效期。

### Q: 会话状态在页面刷新后丢失
A: 确保正确存储和读取`localStorage`中的令牌和用户信息。

### Q: API返回CSRF令牌不匹配错误
A: 确保已调用`initCSRF`函数初始化CSRF保护，并且请求使用`withCredentials: true`。 
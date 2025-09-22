# 鞋类电商系统前端结构

## 前端架构概述

本文档详细描述鞋类电商系统的前端客户门户架构和实现。

### 技术栈

- **框架**: React 18.x
- **类型系统**: TypeScript
- **状态管理**: Redux Toolkit
- **路由**: React Router v6
- **样式**: Tailwind CSS 3.x
- **HTTP客户端**: Axios
- **构建工具**: Vite
- **表单处理**: React Hook Form
- **数据验证**: Yup/Zod
- **通知系统**: React-Toastify
- **测试**: Jest + React Testing Library

### 目录结构

```
shoe-ecommerce-frontend/
├── public/
│   ├── assets/
│   └── favicon.ico
├── src/
│   ├── components/      # 可复用UI组件
│   ├── contexts/        # React Context API相关文件
│   ├── hooks/           # 自定义React钩子
│   ├── layouts/         # 布局组件 
│   ├── pages/           # 页面组件
│   ├── services/        # API服务和数据获取
│   ├── store/           # Redux状态管理
│   │   ├── index.ts
│   │   └── slices/      # Redux切片
│   ├── types/           # TypeScript类型定义
│   ├── utils/           # 工具函数和辅助方法
│   ├── App.tsx          # 应用入口组件
│   ├── main.tsx         # 应用入口点
│   └── vite-env.d.ts    # Vite环境类型声明
├── .eslintrc.js         # ESLint配置
├── .prettierrc          # Prettier配置
├── index.html           # HTML模板
├── package.json         # 项目依赖
├── tailwind.config.js   # Tailwind配置
├── tsconfig.json        # TypeScript配置
└── vite.config.ts       # Vite配置
```

## 组件结构

### 核心组件

前端应用由以下核心组件构成：

#### 1. 布局组件 (Layouts)

- **Layout**: 主布局，包含Header、Footer和内容区域
- **Header**: 顶部导航栏，包含Logo、搜索栏、用户菜单和购物车
- **Footer**: 页脚，包含站点导航、联系信息和社交媒体链接
- **Sidebar**: 侧边栏，用于产品页面的过滤功能

#### 2. 页面组件 (Pages)

- **HomePage**: 网站首页，展示精选产品、新品和促销
- **ProductsPage**: 产品列表页面，支持过滤和分页
- **ProductDetailPage**: 产品详情页面，显示产品信息、规格和评价
- **CartPage**: 购物车页面，管理购物车商品
- **CheckoutPage**: 结账页面，处理订单和支付
- **AccountPage**: 用户账户页面，管理个人信息和订单
- **LoginPage/RegisterPage**: 用户认证页面

#### 3. 可复用UI组件 (Components)

- **ProductCard**: 产品卡片组件，用于列表中展示单个产品
- **ProductGallery**: 产品图片展示组件，支持多图片和放大功能
- **SearchBar**: 搜索栏组件
- **LoadingSpinner**: 加载指示器
- **Pagination**: 分页组件
- **Button**: 自定义按钮组件
- **Modal**: 模态框组件
- **Toast**: 通知提示组件
- **Select**: 自定义下拉选择组件
- **Rating**: 评分组件

## 状态管理

使用Redux Toolkit管理全局状态，主要的切片（slices）包括：

### Redux Slices

- **productSlice**: 管理产品数据，包括产品列表、分类、详情和搜索
  ```typescript
  interface ProductState {
    products: Product[];
    featuredProducts: Product[];
    newProducts: Product[];
    saleProducts: Product[];
    popularProducts: Product[];
    relatedProducts: Product[];
    categories: Category[];
    currentProduct: Product | null;
    productStock: ProductStock[];
    productReviews: {
      data: ProductReview[];
      total: number;
      average_rating: number;
    };
    loading: boolean;
    error: string | null;
    filters: ProductFilters;
    totalProducts: number;
    currentPage: number;
    totalPages: number;
    perPage: number;
  }
  ```

- **cartSlice**: 管理购物车数据，包括商品、数量和价格计算
  ```typescript
  interface CartState {
    items: CartItem[];
    totalItems: number;
    totalQuantity: number;
    subtotal: number;
    discount: number;
    coupon: string | null;
    shippingFee: number;
    total: number;
    loading: boolean;
    error: string | null;
  }
  ```

- **authSlice**: 管理用户认证状态和个人信息
  ```typescript
  interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
    token: string | null;
    loading: boolean;
    error: string | null;
  }
  ```

- **uiSlice**: 管理UI相关状态，如模态框、加载状态和通知
  ```typescript
  interface UIState {
    isLoading: boolean;
    isMobileMenuOpen: boolean;
    isSearchOpen: boolean;
    isFilterOpen: boolean;
    sortOption: string;
    darkMode: boolean;
    notifications: Notification[];
    modal: {
      isOpen: boolean;
      type: string | null;
      data: any | null;
    };
  }
  ```

## Context API

除了Redux，应用还使用Context API进行状态管理：

- **HomepageSettingsContext**: 管理首页设置数据，如横幅、促销和特色产品
  ```typescript
  interface HomepageSettingsContextType {
    settings: HomepageSettings;
    loading: boolean;
    error: Error | null;
    lastUpdate: SettingsUpdateEvent | null;
    refreshSettings: () => Promise<void>;
  }
  ```

- **AuthContext**: 提供认证相关功能和状态
  ```typescript
  interface AuthContextType {
    user: User | null;
    login: (credentials: LoginCredentials) => Promise<void>;
    register: (userData: RegisterData) => Promise<void>;
    logout: () => Promise<void>;
    isAuthenticated: boolean;
    loading: boolean;
    error: string | null;
  }
  ```

## API服务

`src/services/api.ts`定义了与后端API通信的服务：

```typescript
// API服务实例化
export const apiService = {
  // 认证相关
  auth: {
    register: (data: any) => request({ url: '/customer/register', method: 'POST', data }),
    login: (data: any) => request({ url: '/customer/login', method: 'POST', data }),
    logout: () => request({ url: '/customer/logout', method: 'POST' }),
    getProfile: () => request({ url: '/customer/profile', method: 'GET' }),
    // ...其他认证API
  },

  // 产品相关
  products: {
    getList: (params?: any) => request({ url: '/products', method: 'GET', params }),
    getById: (id: number) => request({ url: `/products/${id}`, method: 'GET' }),
    getFeatured: (params?: any) => request({ url: '/products/featured', method: 'GET', params }),
    getNewArrivals: (params?: any) => request({ url: '/products/new-arrivals', method: 'GET', params }),
    // ...其他产品API
  },

  // 购物车相关
  cart: {
    get: () => request({ url: '/cart', method: 'GET' }),
    add: (data: any) => request({ url: '/cart/items', method: 'POST', data }),
    update: (itemId: number, data: any) => request({ url: `/cart/items/${itemId}`, method: 'PUT', data }),
    remove: (itemId: number) => request({ url: `/cart/items/${itemId}`, method: 'DELETE' }),
    // ...其他购物车API
  },

  // 订单相关
  orders: { /* ... */ },

  // 支付相关
  payments: { /* ... */ },

  // 地址相关
  addresses: { /* ... */ },

  // 评价相关
  reviews: { /* ... */ },

  // 收藏相关
  wishlist: { /* ... */ },

  // 首页设置相关
  homepage: {
    getSettings: () => request({ url: '/homepage/settings', method: 'GET' })
  }
};
```

## 前端缓存系统

使用自定义Redis服务模拟类实现本地缓存：

```typescript
class RedisService {
  private cache: Map<string, CacheItem<any>>;
  private defaultExpiry: number = 5 * 60 * 1000; // 默认5分钟过期
  
  // 获取缓存项
  async get<T>(key: string): Promise<T | null> { /* ... */ }
  
  // 设置缓存项
  async set<T>(key: string, value: T, expiry?: number): Promise<boolean> { /* ... */ }
  
  // 获取或设置缓存
  async getOrSet<T>(key: string, callback: () => Promise<T>, expiry?: number): Promise<T> { /* ... */ }
}
```

此缓存系统主要用于：

1. 首页设置和布局配置
2. 产品分类数据
3. 用户浏览历史
4. 频繁访问的API响应

## 主要页面功能

### 1. 首页 (HomePage)

HomePage组件负责展示网站首页，包含以下部分：

- **轮播横幅**：展示促销活动和特色产品
- **分类导航**：展示产品分类，方便用户快速进入分类页面
- **特色产品**：展示精选产品，使用`fetchFeaturedProducts` Action获取数据
- **新品上市**：展示最新产品，使用`fetchNewProducts` Action获取数据
- **促销产品**：展示特价商品，使用`fetchSaleProducts` Action获取数据
- **促销横幅**：展示特别促销信息
- **通讯订阅**：允许用户订阅新闻通讯

首页使用`useHomepageSettings` Hook获取首页设置数据，这些设置数据来自后端API，并通过Context API在组件间共享。

### 2. 产品列表页 (ProductsPage)

ProductsPage组件展示产品列表，支持过滤、排序和分页功能：

- **过滤侧边栏**：包含分类、价格范围、品牌等过滤条件
- **排序选项**：支持按价格、最新、热门等排序
- **产品网格**：使用ProductCard组件展示产品列表
- **分页控件**：支持浏览多页产品

页面使用`fetchProducts` Action获取产品数据，并通过Redux管理状态。

### 3. 产品详情页 (ProductDetailPage)

ProductDetailPage组件展示单个产品的详细信息：

- **产品图片展示**：支持多图片和放大功能
- **产品信息**：名称、价格、描述、规格等
- **尺码和颜色选择**：允许用户选择尺码和颜色
- **数量选择**：允许用户选择购买数量
- **添加到购物车**：允许用户将产品添加到购物车
- **产品描述、规格和评价标签页**
- **相关产品推荐**

页面使用`fetchProductDetails`、`fetchProductStock`和`fetchProductReviews` Actions获取产品详情、库存和评价数据。

### 4. 购物车页面 (CartPage)

CartPage组件展示用户的购物车内容：

- **购物车商品列表**：显示商品图片、名称、价格、数量和小计
- **商品数量调整**：允许用户调整购买数量
- **移除商品**：允许用户从购物车移除商品
- **总计信息**：显示小计、运费、折扣和总计
- **结账按钮**：允许用户进入结账流程

页面使用`fetchCart`、`updateCartItem`、`removeFromCart`等Actions管理购物车数据。

## 路由系统

使用React Router v6实现路由系统：

```typescript
function App() {
  return (
    <Routes>
      <Route path="/" element={<Layout />}>
        <Route index element={<HomePage />} />
        <Route path="products" element={<ProductsPage />} />
        <Route path="products/:id" element={<ProductDetailPage />} />
        <Route path="cart" element={<CartPage />} />
        <Route path="checkout" element={<ProtectedRoute><CheckoutPage /></ProtectedRoute>} />
        <Route path="account" element={<ProtectedRoute><AccountPage /></ProtectedRoute>} />
        <Route path="login" element={<LoginPage />} />
        <Route path="register" element={<RegisterPage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  );
}
```

使用`ProtectedRoute`组件保护需要认证的路由：

```typescript
const ProtectedRoute = ({ children }) => {
  const { isAuthenticated, loading } = useSelector(state => state.auth);
  const location = useLocation();

  if (loading) {
    return <LoadingSpinner />;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return children;
};
```

## UI设计规范

### 设计理念

设计风格强调简约、时尚和专业，同时保持良好的可用性和可访问性，为用户提供现代、简洁且用户友好的购物体验。

### 色彩系统

- **主色调**：深蓝色 (#1E3A8A) - 传达专业、信任和稳定
- **辅助色**：浅灰色 (#F3F4F6) - 用于背景和分隔
- **强调色**：橙色 (#F97316) - 用于按钮、链接和重要元素
- **中性色**：白色 (#FFFFFF) 和不同深度的灰色 - 用于文本和背景
- **功能色彩**：
  - 成功绿 (#10B981)
  - 警告黄 (#FBBF24)
  - 错误红 (#EF4444)
  - 信息蓝 (#3B82F6)

### 排版

- **主要字体**：系统默认无衬线字体栈
- **基础字体大小**：16px (1rem)
- **字体粗细**：400(常规)、500(中等)、600(粗体)、700(特粗)
- **标题层级**：H1(2.5rem) 至 H6(1rem)

### 组件设计

- **按钮**：主要、次要、文本和图标按钮，统一样式
- **表单元素**：统一输入框、下拉菜单、复选框样式
- **卡片**：产品卡片和信息卡片，带轻微阴影和圆角
- **导航**：主导航、面包屑导航和分页控件
- **提示和通知**：轻提示(Toast)、对话框和警告提示，替换所有的alert和confirm

### 自定义Select组件

```html
<div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
    <div class="p-4">
        <!-- 选择组件头部 -->
        <div class="flex items-center justify-between mb-4">...</div>
        <!-- 选择器部分 -->
        <div class="recipient-selector hidden" id="selector-product_created">...</div>
        <!-- 已选择项显示 -->
        <div class="mt-3 selected-recipients" id="selected-product_created">...</div>
    </div>
</div>
```

### 响应式设计

- **断点**：
  - 移动设备：< 640px
  - 平板竖屏：>= 640px
  - 平板横屏：>= 768px
  - 小型桌面：>= 1024px
  - 中型桌面：>= 1280px
  - 大型桌面：>= 1536px

- **布局适应**：
  - 流动网格系统
  - 可折叠的导航菜单
  - 调整的字体大小和间距
  - 优化的触摸目标

## API对接与数据流

### 数据流向

前端应用中的数据流向遵循以下模式：

1. 用户操作触发Action
2. Action通过API服务发起HTTP请求
3. API响应数据更新Redux状态
4. 组件通过Selector从Redux获取数据
5. 组件根据数据重新渲染UI

### API请求处理

使用Axios作为HTTP客户端，并实现了请求和响应拦截器：

```typescript
// 请求拦截器
api.interceptors.request.use(
  (config) => {
    // 添加认证令牌
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// 响应拦截器
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    // 处理401错误，自动登出
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);
```

## 实施现状与未来发展

### 已完成功能

- ✅ 用户认证（登录/注册）
- ✅ 产品浏览和搜索
- ✅ 产品详情页
- ✅ 购物车基础功能
- ✅ 首页设置和展示

### 进行中功能

- ⏳ 结账流程
- ⏳ 订单管理
- ⏳ 用户中心完善

### 计划功能

- 产品评价和评分
- 优惠券和促销
- 产品推荐系统
- 社交分享
- 通知和提醒

## 开发最佳实践

### 性能优化

1. **代码分割**：使用React.lazy和Suspense实现按需加载
2. **图片优化**：使用懒加载和适当的图片格式
3. **缓存策略**：使用自定义Redis服务缓存频繁请求的数据
4. **减少渲染**：使用useMemo和useCallback优化组件渲染

### 状态管理

1. **集中式状态**：使用Redux管理全局状态
2. **局部状态**：使用useState和useReducer管理组件状态
3. **上下文状态**：使用Context API管理特定功能域的状态

### 错误处理

1. **全局错误边界**：捕获和显示组件树中的JavaScript错误
2. **API错误处理**：统一处理API错误，并显示适当的用户提示
3. **输入验证**：使用表单验证库防止无效数据提交

### 代码风格

1. **TypeScript类型**：为所有组件和函数定义明确的类型
2. **组件结构**：采用功能组件和Hooks
3. **命名约定**：使用一致的命名约定（如PascalCase用于组件）
4. **文件结构**：按功能域组织文件和目录 
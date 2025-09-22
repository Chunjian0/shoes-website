# 鞋类电商系统后端结构

## 后端架构概述

本文档详细描述鞋类电商系统的后端API和管理系统架构及实现。

### 技术栈

- **框架**: Laravel 10.x
- **PHP版本**: PHP 8.1+
- **数据库**: MySQL 8.0
- **缓存**: Redis
- **认证**: Laravel Sanctum (API令牌)
- **队列**: Laravel Queue + Redis
- **任务调度**: Laravel Scheduler
- **文件存储**: Laravel Storage (本地/S3)
- **邮件服务**: Laravel Mail + SMTP
- **验证**: Laravel Validator
- **API文档**: Swagger/OpenAPI
- **测试**: PHPUnit

### 目录结构

```
shoe-ecommerce-backend/
├── app/
│   ├── Console/            # 控制台命令
│   ├── Exceptions/         # 异常处理
│   ├── Http/
│   │   ├── Controllers/    # 控制器
│   │   ├── Middleware/     # 中间件
│   │   └── Requests/       # 表单请求验证
│   ├── Jobs/               # 队列任务
│   ├── Mail/               # 邮件模板
│   ├── Models/             # 数据模型
│   ├── Notifications/      # 通知
│   ├── Observers/          # 模型观察者
│   ├── Policies/           # 授权策略
│   ├── Providers/          # 服务提供者
│   ├── Repositories/       # 数据仓库
│   └── Services/           # 业务服务
├── bootstrap/              # 框架引导文件
├── config/                 # 配置文件
├── database/
│   ├── factories/          # 模型工厂
│   ├── migrations/         # 数据库迁移
│   └── seeders/            # 数据填充
├── public/                 # 公共访问目录
│   ├── images/             # 图片资源
│   │   └── products/       # 产品图片
│   ├── js/                 # JavaScript文件
│   └── css/                # CSS样式表
├── resources/
│   ├── js/                 # Vue组件
│   ├── sass/               # SCSS样式
│   └── views/              # Blade模板
├── routes/
│   ├── api.php             # API路由
│   ├── channels.php        # 广播频道
│   ├── console.php         # 控制台路由
│   └── web.php             # Web路由
├── storage/                # 存储目录
│   ├── app/                # 应用存储
│   ├── framework/          # 框架存储
│   └── logs/               # 日志文件
├── tests/                  # 测试文件
├── .env                    # 环境变量
├── artisan                 # Artisan命令行工具
├── composer.json           # Composer配置
└── package.json            # NPM配置
```

## 数据库设计

系统使用MySQL关系型数据库，采用规范化设计原则，主要包含以下数据表：

### 核心数据表

#### 用户与认证

- **users**: 用户基本信息
  - `id`: 主键
  - `name`: 用户姓名
  - `email`: 用户邮箱(唯一)
  - `password`: 加密密码
  - `role`: 用户角色(customer/admin)
  - `phone`: 手机号码
  - `avatar`: 头像路径
  - `status`: 账户状态
  - 时间戳

- **personal_access_tokens**: API访问令牌
  - `id`: 主键
  - `tokenable_type`: 令牌关联模型
  - `tokenable_id`: 关联ID
  - `name`: 令牌名称
  - `token`: 哈希令牌(唯一)
  - `abilities`: 权限范围
  - `last_used_at`: 最后使用时间
  - 时间戳

- **password_reset_tokens**: 密码重置令牌
  - `email`: 邮箱(主键)
  - `token`: 重置令牌
  - `created_at`: 创建时间

#### 产品与库存

- **products**: 产品信息
  - `id`: 主键
  - `name`: 产品名称
  - `slug`: SEO友好链接(唯一)
  - `description`: 产品描述
  - `price`: 原价
  - `sale_price`: 促销价
  - `category_id`: 分类ID(外键)
  - `brand_id`: 品牌ID(外键)
  - `featured`: 是否精选
  - `status`: 产品状态(active/inactive)
  - `meta_title`: SEO标题
  - `meta_description`: SEO描述
  - 时间戳

- **product_variants**: 产品变体(尺码/颜色组合)
  - `id`: 主键
  - `product_id`: 产品ID(外键)
  - `sku`: 库存单位(唯一)
  - `color_id`: 颜色ID(外键)
  - `size_id`: 尺码ID(外键)
  - `price`: 变体价格
  - `sale_price`: 变体促销价
  - `stock_quantity`: 库存数量
  - 时间戳

- **product_images**: 产品图片
  - `id`: 主键
  - `product_id`: 产品ID(外键)
  - `image_path`: 图片路径
  - `is_primary`: 是否主图
  - `sort_order`: 排序顺序
  - 时间戳

- **categories**: 产品分类
  - `id`: 主键
  - `name`: 分类名称
  - `slug`: SEO友好链接(唯一)
  - `parent_id`: 父分类ID(自引用)
  - `description`: 分类描述
  - `image_path`: 分类图片
  - `status`: 状态
  - `sort_order`: 排序顺序
  - 时间戳

- **brands**: 产品品牌
  - `id`: 主键
  - `name`: 品牌名称
  - `slug`: SEO友好链接(唯一)
  - `description`: 品牌描述
  - `logo_path`: 品牌Logo
  - `status`: 状态
  - 时间戳

- **attributes**: 产品属性
  - `id`: 主键
  - `name`: 属性名称
  - `type`: 属性类型(text/number/boolean/select)
  - 时间戳

- **attribute_values**: 属性值
  - `id`: 主键
  - `attribute_id`: 属性ID(外键)
  - `value`: 属性值
  - 时间戳

- **product_attribute_values**: 产品属性值关联
  - `product_id`: 产品ID(外键)
  - `attribute_value_id`: 属性值ID(外键)

#### 购物车与订单

- **carts**: 购物车
  - `id`: 主键
  - `user_id`: 用户ID(外键,可空)
  - `session_id`: 会话ID(匿名用户)
  - `status`: 状态
  - 时间戳

- **cart_items**: 购物车商品
  - `id`: 主键
  - `cart_id`: 购物车ID(外键)
  - `product_variant_id`: 产品变体ID(外键)
  - `quantity`: 数量
  - 时间戳

- **orders**: 订单
  - `id`: 主键
  - `order_number`: 订单号(唯一)
  - `user_id`: 用户ID(外键)
  - `status`: 订单状态
  - `subtotal`: 小计
  - `tax`: 税费
  - `shipping_fee`: 运费
  - `discount`: 折扣
  - `total`: 总计
  - `shipping_address_id`: 收货地址ID(外键)
  - `billing_address_id`: 账单地址ID(外键)
  - `payment_method`: 支付方式
  - `payment_status`: 支付状态
  - `notes`: 订单备注
  - 时间戳

- **order_items**: 订单商品
  - `id`: 主键
  - `order_id`: 订单ID(外键)
  - `product_variant_id`: 产品变体ID(外键)
  - `quantity`: 数量
  - `price`: 单价
  - `subtotal`: 小计
  - 时间戳

- **order_statuses**: 订单状态历史
  - `id`: 主键
  - `order_id`: 订单ID(外键)
  - `status`: 状态
  - `comment`: 状态备注
  - `user_id`: 操作用户ID(外键)
  - 时间戳

#### 用户地址与收藏

- **addresses**: 用户地址
  - `id`: 主键
  - `user_id`: 用户ID(外键)
  - `name`: 收件人姓名
  - `phone`: 联系电话
  - `address_line1`: 地址行1
  - `address_line2`: 地址行2
  - `city`: 城市
  - `state`: 省/州
  - `postal_code`: 邮编
  - `country`: 国家
  - `is_default_shipping`: 是否默认收货地址
  - `is_default_billing`: 是否默认账单地址
  - 时间戳

- **wishlists**: 收藏列表
  - `id`: 主键
  - `user_id`: 用户ID(外键)
  - `product_id`: 产品ID(外键)
  - 时间戳

#### 评价与评分

- **reviews**: 产品评价
  - `id`: 主键
  - `product_id`: 产品ID(外键)
  - `user_id`: 用户ID(外键)
  - `order_item_id`: 订单商品ID(外键)
  - `rating`: 评分(1-5)
  - `title`: 评价标题
  - `comment`: 评价内容
  - `status`: 状态(pending/approved/rejected)
  - 时间戳

- **review_images**: 评价图片
  - `id`: 主键
  - `review_id`: 评价ID(外键)
  - `image_path`: 图片路径
  - 时间戳

#### 营销与促销

- **coupons**: 优惠券
  - `id`: 主键
  - `code`: 优惠码(唯一)
  - `description`: 描述
  - `discount_type`: 折扣类型(percentage/fixed)
  - `discount_value`: 折扣值
  - `min_order_value`: 最低订单金额
  - `max_discount_value`: 最大折扣金额
  - `start_date`: 开始日期
  - `end_date`: 结束日期
  - `usage_limit`: 使用次数限制
  - `used_count`: 已使用次数
  - `status`: 状态
  - 时间戳

- **homepage_settings**: 首页设置
  - `id`: 主键
  - `section_name`: 版块名称
  - `section_type`: 版块类型
  - `content`: 内容(JSON)
  - `status`: 状态
  - `sort_order`: 排序顺序
  - 时间戳

### 模型关系

系统采用Laravel Eloquent ORM管理数据模型关系：

#### User模型关系

```php
class User extends Authenticatable
{
    // 一对多关系: 用户拥有多个地址
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    
    // 一对多关系: 用户拥有多个订单
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    // 一对多关系: 用户拥有多个评价
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    // 多对多关系: 用户收藏多个产品
    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }
    
    // 一对一关系: 用户拥有一个购物车
    public function cart()
    {
        return $this->hasOne(Cart::class)->where('status', 'active');
    }
}
```

#### Product模型关系

```php
class Product extends Model
{
    // 一对多关系: 产品有多个变体
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    
    // 一对多关系: 产品有多个图片
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    
    // 多对一关系: 产品属于一个分类
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    // 多对一关系: 产品属于一个品牌
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    
    // 多对多关系: 产品有多个属性值
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values');
    }
    
    // 一对多关系: 产品有多个评价
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    // 获取主图
    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first();
    }
    
    // 获取平均评分
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('status', 'approved')->avg('rating') ?? 0;
    }
}
```

#### Order模型关系

```php
class Order extends Model
{
    // 多对一关系: 订单属于一个用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // 一对多关系: 订单有多个商品
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // 一对多关系: 订单有多个状态历史
    public function statusHistory()
    {
        return $this->hasMany(OrderStatus::class);
    }
    
    // 多对一关系: 订单有一个收货地址
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }
    
    // 多对一关系: 订单有一个账单地址
    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }
}
```

#### Category模型关系

```php
class Category extends Model
{
    // 一对多关系: 分类有多个产品
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    // 自引用关系: 分类有多个子分类
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    
    // 自引用关系: 分类属于一个父分类
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
```

## API设计与实现

后端提供RESTful API，为前端和移动应用提供数据服务。API遵循REST架构风格，使用标准HTTP方法和状态码。

### API架构

API采用分层架构设计：

1. **控制器层**：处理HTTP请求，验证输入，调用服务层
2. **服务层**：包含业务逻辑，处理复杂操作
3. **仓库层**：负责数据访问，封装查询逻辑
4. **模型层**：定义数据结构和关系

### API路由

在`routes/api.php`中定义API路由：

```php
// API版本前缀
Route::prefix('v1')->group(function () {
    // 公开API (无需认证)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('homepage/settings', [HomepageController::class, 'getSettings']);
    
    // 认证相关路由
    Route::post('customer/register', [AuthController::class, 'register']);
    Route::post('customer/login', [AuthController::class, 'login']);
    Route::post('customer/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('customer/reset-password', [AuthController::class, 'resetPassword']);
    
    // 需认证API
    Route::middleware('auth:sanctum')->group(function () {
        // 用户管理
        Route::post('customer/logout', [AuthController::class, 'logout']);
        Route::get('customer/profile', [CustomerController::class, 'profile']);
        Route::put('customer/profile', [CustomerController::class, 'updateProfile']);
        Route::put('customer/password', [CustomerController::class, 'updatePassword']);
        
        // 地址管理
        Route::apiResource('addresses', AddressController::class);
        
        // 购物车管理
        Route::get('cart', [CartController::class, 'show']);
        Route::post('cart/items', [CartController::class, 'addItem']);
        Route::put('cart/items/{id}', [CartController::class, 'updateItem']);
        Route::delete('cart/items/{id}', [CartController::class, 'removeItem']);
        
        // 订单管理
        Route::apiResource('orders', OrderController::class);
        Route::post('orders/{order}/pay', [OrderController::class, 'processPayment']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        
        // 收藏管理
        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist', [WishlistController::class, 'store']);
        Route::delete('wishlist/{product}', [WishlistController::class, 'destroy']);
        
        // 评价管理
        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
        Route::put('reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
    });
    
    // 管理员API (需要admin角色)
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        // 产品管理
        Route::apiResource('products', Admin\ProductController::class);
        Route::post('products/{product}/images', [Admin\ProductImageController::class, 'store']);
        Route::delete('products/images/{image}', [Admin\ProductImageController::class, 'destroy']);
        Route::post('products/{product}/variants', [Admin\ProductVariantController::class, 'store']);
        
        // 分类管理
        Route::apiResource('categories', Admin\CategoryController::class);
        
        // 品牌管理
        Route::apiResource('brands', Admin\BrandController::class);
        
        // 订单管理
        Route::get('orders', [Admin\OrderController::class, 'index']);
        Route::get('orders/{order}', [Admin\OrderController::class, 'show']);
        Route::put('orders/{order}/status', [Admin\OrderController::class, 'updateStatus']);
        
        // 用户管理
        Route::get('users', [Admin\UserController::class, 'index']);
        Route::get('users/{user}', [Admin\UserController::class, 'show']);
        Route::put('users/{user}/status', [Admin\UserController::class, 'updateStatus']);
        
        // 首页设置管理
        Route::get('homepage/settings', [Admin\HomepageController::class, 'index']);
        Route::post('homepage/settings', [Admin\HomepageController::class, 'store']);
        Route::put('homepage/settings/{id}', [Admin\HomepageController::class, 'update']);
        Route::delete('homepage/settings/{id}', [Admin\HomepageController::class, 'destroy']);
        
        // 优惠券管理
        Route::apiResource('coupons', Admin\CouponController::class);
        
        // 统计和报表
        Route::get('dashboard/stats', [Admin\DashboardController::class, 'stats']);
        Route::get('reports/sales', [Admin\ReportController::class, 'sales']);
        Route::get('reports/products', [Admin\ReportController::class, 'products']);
    });
});
```

### 控制器实现

控制器处理HTTP请求并返回响应，遵循单一职责原则：

#### 产品控制器示例：

```php
class ProductController extends Controller
{
    protected $productService;
    
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    
    /**
     * 获取产品列表
     */
    public function index(Request $request)
    {
        // 验证请求参数
        $request->validate([
            'category' => 'nullable|exists:categories,id',
            'brand' => 'nullable|exists:brands,id',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|gt:price_min',
            'sort' => 'nullable|in:newest,price_asc,price_desc,popularity',
            'per_page' => 'nullable|integer|min:10|max:50',
            'page' => 'nullable|integer|min:1',
        ]);
        
        // 获取产品数据
        $products = $this->productService->getProducts($request->all());
        
        // 返回分页响应
        return response()->json($products);
    }
    
    /**
     * 获取产品详情
     */
    public function show($id)
    {
        try {
            $product = $this->productService->getProductDetails($id);
            return response()->json($product);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
    
    /**
     * 获取精选产品
     */
    public function featured(Request $request)
    {
        $limit = $request->input('limit', 8);
        $products = $this->productService->getFeaturedProducts($limit);
        return response()->json($products);
    }
    
    /**
     * 获取新品
     */
    public function newArrivals(Request $request)
    {
        $limit = $request->input('limit', 8);
        $products = $this->productService->getNewProducts($limit);
        return response()->json($products);
    }
    
    /**
     * 获取促销产品
     */
    public function onSale(Request $request)
    {
        $limit = $request->input('limit', 8);
        $products = $this->productService->getOnSaleProducts($limit);
        return response()->json($products);
    }
}
```

#### 认证控制器示例：

```php
class AuthController extends Controller
{
    /**
     * 用户注册
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'customer',
            'status' => 'active',
        ]);
        
        // 创建令牌
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }
    
    /**
     * 用户登录
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        
        // 检查凭据
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials',
            ], 401);
        }
        
        $user = User::where('email', $request->email)->firstOrFail();
        
        // 检查账户状态
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not active',
            ], 403);
        }
        
        // 删除旧令牌
        $user->tokens()->delete();
        
        // 创建新令牌
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
    
    /**
     * 用户登出
     */
    public function logout(Request $request)
    {
        // 删除当前令牌
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
    
    /**
     * 忘记密码请求
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        
        // 生成密码重置令牌
        $token = Password::createToken(
            User::where('email', $request->email)->first()
        );
        
        // 发送密码重置邮件
        $status = Password::sendResetLink(
            $request->only('email')
        );
        
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent']);
        }
        
        return response()->json(['message' => 'Unable to send reset link'], 500);
    }
    
    /**
     * 重置密码
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );
        
        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset']);
        }
        
        return response()->json(['message' => 'Invalid reset token'], 400);
    }
}
```

#### 购物车控制器示例：

```php
class CartController extends Controller
{
    protected $cartService;
    
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    /**
     * 获取购物车
     */
    public function show(Request $request)
    {
        $cart = $this->cartService->getCart($request->user()->id);
        return response()->json($cart);
    }
    
    /**
     * 添加商品到购物车
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);
        
        try {
            $cartItem = $this->cartService->addToCart(
                $request->user()->id,
                $request->product_variant_id,
                $request->quantity
            );
            
            return response()->json($cartItem, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * 更新购物车商品
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        
        try {
            $cartItem = $this->cartService->updateCartItem(
                $request->user()->id,
                $id,
                $request->quantity
            );
            
            return response()->json($cartItem);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cart item not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * 移除购物车商品
     */
    public function removeItem(Request $request, $id)
    {
        try {
            $this->cartService->removeFromCart($request->user()->id, $id);
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cart item not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
```

### 资源转换

使用Laravel API Resource类转换模型数据为API响应：

```php
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'discount_percentage' => $this->when($this->sale_price, function () {
                return round((1 - $this->sale_price / $this->price) * 100);
            }),
            'primary_image' => $this->whenLoaded('primaryImage', function () {
                return $this->primaryImage->image_path;
            }),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'average_rating' => $this->average_rating,
            'review_count' => $this->review_count,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'attributes' => AttributeValueResource::collection($this->whenLoaded('attributeValues')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### API响应格式

API响应采用统一格式，包含状态码、消息和数据：

```json
{
    "status": "success",
    "message": "Operation successful",
    "data": {
        // 实际数据
    },
    "meta": {
        // 分页、过滤等元数据
    }
}
```

对于错误响应：

```json
{
    "status": "error",
    "message": "Error message",
    "errors": {
        // 详细错误信息
    }
}
```

### API中间件

实现了以下API中间件：

- **认证中间件**：验证API令牌
- **角色中间件**：检查用户角色权限
- **API日志中间件**：记录API请求和响应
- **限流中间件**：限制API请求频率
- **CORS中间件**：处理跨域请求 
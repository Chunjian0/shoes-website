<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\Api\SupplierController as ApiSupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\NotificationLogController;
use App\Http\Controllers\Api\MessageTemplateApiController;
use App\Http\Controllers\Api\ProductAnalyticsController;
use App\Http\Controllers\Api\ProductApiAdapter;
use App\Http\Controllers\Api\CategoryApiAdapter;
use App\Http\Controllers\Api\HomepageSettingsAdapter;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\ProductTemplateController;
use App\Http\Controllers\Api\TemplateApiController;
use App\Http\Controllers\ProductTemplateController as WebProductTemplateController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerController as ApiCustomerController;
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health Check Endpoint - 健康检查端点
Route::get('/health', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'API system is running normally',
        'timestamp' => now()->toDateTimeString(),
        'environment' => config('app.env')
    ]);
});

// CSRF Cookie
Route::get('/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
})->middleware('web');

/*
|--------------------------------------------------------------------------
| Customer API Routes - 前端登录认证API
|--------------------------------------------------------------------------
*/

// Public Customer Routes (Previously in web group, but might not need web if using tokens)
// Consider if these need web middleware at all if frontend is pure SPA with tokens
Route::prefix('customer')->group(function () {
    Route::post('/register', [ApiCustomerController::class, 'register']);
    Route::post('/register-auto', [ApiCustomerController::class, 'createWithAutoPassword']);
    Route::post('/login', [ApiCustomerController::class, 'login']);
    Route::post('/logout', [ApiCustomerController::class, 'logout'])->middleware('auth:sanctum'); // Logout needs auth
    // Keep verification/password reset public
    Route::post('/verify-email', [ApiCustomerController::class, 'verifyEmail']);
    Route::post('/send-verification-code', [ApiCustomerController::class, 'sendVerificationCode']);
    Route::post('/forgot-password', [ApiCustomerController::class, 'forgotPassword']);
    Route::post('/reset-password', [ApiCustomerController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes (using Sanctum Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Customer Profile (Protected by auth:sanctum)
    Route::prefix('customer')->group(function () {
        Route::get('/profile', [ApiCustomerController::class, 'profile']);
        Route::put('/profile', [ApiCustomerController::class, 'updateProfile']); // Use this for address update
        Route::put('/password', [ApiCustomerController::class, 'updatePassword']);
    });

    // Cart Routes (Protected by auth:sanctum)
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::post('/create', [CartController::class, 'createCart']);
    });

    // Checkout Routes (Protected by auth:sanctum)
    Route::prefix('checkout')->group(function () {
        Route::post('/prepare', [CheckoutController::class, 'prepare'])->name('api.checkout.prepare');
        Route::post('/process', [CheckoutController::class, 'process'])->name('api.checkout.process');
        Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('api.checkout.buy-now');
    });

    // Order Routes (Protected by auth:sanctum)
    Route::prefix('orders')->group(function () {
        Route::get('/{orderId}', [OrderController::class, 'show'])->name('api.orders.show');
        Route::get('/', [OrderController::class, 'index'])->name('api.orders.index');
    });


    // Other protected routes...
    // Route::get('/auth/check', [AuthController::class, 'checkSession']);
    // Route::get('/auth/ping', [AuthController::class, 'ping']);

});

/*
|--------------------------------------------------------------------------
| 后台管理API路由 (需要Sanctum认证)
|--------------------------------------------------------------------------
*/

// 用户认证 - 后台用户路由
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication routing - 这些是后台管理界面用的路由
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::middleware(['web', 'auth'])->group(function () {
    // Get current user information
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('roles', 'permissions')
        ]);
    });
    

    // -- START: Add name prefix for conflicting resources --
    Route::name('api.')->group(function() {
        Route::apiResource('customers', CustomerController::class); // 名称会变成 api.customers.*
        // Optometry data management
        // Route::apiResource('prescriptions', PrescriptionController::class); // 名称会变成 api.prescriptions.*
        // Product-related routing
        // Route::get('/catalog/products', [ProductController::class, 'apiIndex']); // 这个 GET 路由可能也需要放入name group 或改名/移除
        Route::apiResource('catalog/products', ProductController::class)->except(['index']); // 名称会变成 api.catalog.products.*
        // Supplier Management
        Route::apiResource('suppliers', SupplierController::class); // 名称会变成 api.suppliers.*
        // Procurement Management
        Route::apiResource('purchases', PurchaseController::class); // 名称会变成 api.purchases.*
    });
    // -- END: Add name prefix --

    // Product classification related routing
    Route::get('/product-categories', [ProductCategoryController::class, 'getCategories']);
    Route::get('/product-categories/{category}/parameters', [ProductCategoryController::class, 'parameters']);
    
    // Product-related routing
    Route::get('/catalog/products', [ProductController::class, 'apiIndex']); // KEEPING OUTSIDE FOR NOW

    // Supplier Management
    // Route::apiResource('suppliers', SupplierController::class); // Moved inside name group
    Route::get('suppliers-list', [SupplierController::class, 'getSuppliers']); // KEEPING OUTSIDE FOR NOW

    // Supplier Product Management Routing
    Route::middleware(['auth:sanctum', 'web'])->prefix('suppliers/{supplier}')->group(function () {
        
        Route::get('agreements', [SupplierController::class, 'agreements']);
        Route::post('agreements', [SupplierController::class, 'storeAgreement']);
        Route::put('agreements/{agreement}', [SupplierController::class, 'updateAgreement']);
        Route::delete('agreements/{agreement}', [SupplierController::class, 'destroyAgreement']);
    });
    

    // Procurement Management
    // Route::apiResource('purchases', PurchaseController::class); // REMOVED - Definition is inside the 'api.' named group now
    Route::post('purchases/{purchase}/approve', [PurchaseController::class, 'approve']);
    Route::post('purchases/{purchase}/reject', [PurchaseController::class, 'reject']);

    // Notification Management
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show', 'store']);
    Route::post('notifications/{notification}/resend', [NotificationController::class, 'resend']);

    // Obtain supplier delivery cycle
    Route::get('/suppliers/{supplier}/lead-time', function (App\Models\Supplier $supplier) {
        return response()->json([
            'lead_time' => $supplier->lead_time ?? 0
        ]);
    })->middleware('auth:sanctum');

    // Supplier Product Price Agreement
    Route::get('/suppliers/{supplier}/products/{product}/agreements', [ApiSupplierController::class, 'getProductAgreements']);

    // storehouse API
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('api.warehouses.list');
    Route::get('/warehouses-list', [WarehouseController::class, 'index']);

    // Employee search API
    Route::get('/employees/search', [EmployeeController::class, 'search']);

    // Product search API
    Route::get('/products', [ProductController::class, 'getProducts']);

    // Address related route
    Route::get('/warehouses/{warehouse}/address', [WarehouseController::class, 'getAddress']);
    Route::get('/company/address', [CompanyProfileController::class, 'getAddress']);

    // Product and supplier-related routing
    Route::get('/suppliers/{supplier}/products/{product}/price-agreement', [SupplierController::class, 'getPriceAgreement']);
});

// Message Template Routes
Route::get('/message-templates', [MessageTemplateController::class, 'getTemplatesApi']);
Route::get('/message-templates/{id}/variables', [MessageTemplateController::class, 'getTemplateVariablesApi']);

// 测试邮件发送
Route::post('/test/send-email', [TestController::class, 'sendTestEmail']);

// 通知日志API
Route::get('/notification-logs', [NotificationLogController::class, 'index']);
Route::get('/notification-logs/{id}', [NotificationLogController::class, 'show']);
Route::post('/notification-logs', [NotificationLogController::class, 'store']);
Route::put('/notification-logs/{id}', [NotificationLogController::class, 'update']);

// 消息模板API
Route::get('/message-templates', [MessageTemplateApiController::class, 'getTemplates']);
Route::get('/message-templates/{id}/variables', [MessageTemplateApiController::class, 'getTemplateVariables']);

// Product Routes (Public)
Route::prefix('products')->group(function () {
    Route::get('/featured', [App\Http\Controllers\Api\ProductController::class, 'getFeaturedProducts']);
    Route::get('/new-arrivals', [App\Http\Controllers\Api\ProductController::class, 'getNewArrivals']);
    Route::get('/promotion', [App\Http\Controllers\Api\ProductController::class, 'getPromotionProducts']);
    Route::get('/{id}', [App\Http\Controllers\Api\ProductController::class, 'getProductDetails']);
    Route::get('/{id}/stock', [App\Http\Controllers\Api\ProductController::class, 'getProductStock']);
    Route::get('/{id}/images', [App\Http\Controllers\Api\ProductController::class, 'getProductImages']);
});

// Product Category Routes (Public)
Route::prefix('product-categories')->group(function () {
    // 使用适配器控制器获取产品类别，确保响应格式与前端期望一致
    Route::get('/', [App\Http\Controllers\Api\CategoryApiAdapter::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\CategoryApiAdapter::class, 'show']);
    Route::get('/{id}/parameters', [App\Http\Controllers\Api\ProductCategoryController::class, 'parameters']);
});

// 测试调试端点（仅用于开发环境）
Route::prefix('debug')->middleware(['web'])->group(function () {
    Route::get('/latest-verification-code', function (\Illuminate\Http\Request $request) {
        if (app()->environment('production')) {
            return response()->json(['message' => 'Not available in production'], 403);
        }
        
        $email = $request->query('email');
        if (empty($email)) {
            return response()->json(['error' => 'Email is required'], 422);
        }
        
        $code = \App\Models\VerificationCode::where('email', $email)
            ->latest()
            ->first();
            
        if (!$code) {
            return response()->json(['error' => 'No verification code found for this email'], 404);
        }
        
        return response()->json([
            'email' => $code->email,
            'code' => $code->code,
            'created_at' => $code->created_at,
            'expires_at' => $code->expires_at,
            'is_used' => $code->is_used,
            'is_expired' => $code->expires_at < now(),
            'current_time' => now()
        ]);
    });
});

// 添加新的产品和设置API路由
Route::get('/products/all', [App\Http\Controllers\Api\ProductController::class, 'getAllProducts']);

// 产品分析路由
Route::prefix('analytics')->group(function () {
    Route::post('/impression/batch', [ProductAnalyticsController::class, 'recordImpressionBatch']);
    Route::post('/click', [ProductAnalyticsController::class, 'recordClick']);
    Route::get('/product/{id}/stats', [ProductAnalyticsController::class, 'getProductStats']);
    Route::get('/dashboard/summary', [ProductAnalyticsController::class, 'getDashboardSummary']);
    Route::get('/sections/performance', [ProductAnalyticsController::class, 'getSectionsPerformance']);
});

// 添加一个临时的API路由用于测试获取首页设置和其他数据
Route::get('/homepage/settings', [App\Http\Controllers\Api\HomepageSettingsAdapter::class, 'getHomepageSettings']);

// 添加保存首页设置的API路由
Route::post('/homepage/settings', [App\Http\Controllers\Api\HomepageSettingsAdapter::class, 'saveHomepageSettings']);

// 测试API路由，返回一些产品数据
// 使用适配器控制器获取精选产品，确保响应格式与前端期望一致
Route::get('/products/featured', [App\Http\Controllers\Api\ProductApiAdapter::class, 'getFeaturedProducts']);

// 使用适配器控制器获取新品，确保响应格式与前端期望一致
Route::get('/products/new-arrivals', [App\Http\Controllers\Api\ProductApiAdapter::class, 'getNewArrivals']);

// 使用适配器控制器获取促销产品，确保响应格式与前端期望一致
Route::get('/products/sale', [App\Http\Controllers\Api\ProductApiAdapter::class, 'getPromotionProducts']);

// 添加一个测试路由返回系统状态，方便调试
Route::get('/system/status', function () {
    return response()->json([
        'success' => true,
        'timestamp' => now()->toIsoString(),
        'environment' => app()->environment(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'debug_mode' => (bool)config('app.debug'),
        'cors' => [
            'supports_credentials' => (bool)config('cors.supports_credentials'),
            'allowed_origins' => config('cors.allowed_origins'),
            'paths' => config('cors.paths'),
        ],
        'sanctum' => [
            'stateful_domains' => config('sanctum.stateful'),
        ],
        'session' => [
            'driver' => config('session.driver'),
            'cookie' => config('session.cookie'),
            'domain' => config('session.domain'),
            'secure' => (bool)config('session.secure'),
            'same_site' => config('session.same_site'),
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Homepage API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('homepage')->group(function () {
    Route::get('/data', [App\Http\Controllers\Api\HomepageController::class, 'getAllData']);
    Route::get('/featured-templates', [App\Http\Controllers\Api\HomepageController::class, 'getFeaturedTemplates']);
    Route::get('/new-arrival-templates', [App\Http\Controllers\Api\HomepageController::class, 'getNewArrivalTemplates']);
    Route::get('/sale-templates', [App\Http\Controllers\Api\HomepageController::class, 'getSaleTemplates']);
    Route::get('/banners', [App\Http\Controllers\Api\HomepageController::class, 'getBanners']);
    Route::get('/settings', [App\Http\Controllers\Api\HomepageController::class, 'getSettings']);
});

/*
|--------------------------------------------------------------------------
| Product Templates API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('product-templates')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ProductTemplateController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\ProductTemplateController::class, 'show']);
    Route::get('/{id}/products', [App\Http\Controllers\Api\ProductTemplateController::class, 'getProducts']);
    Route::get('/{id}/parameter-groups/{group}/products', [App\Http\Controllers\Api\ProductTemplateController::class, 'getProductsByParameterGroup']);
    Route::get('/{id}/next-parameter-combo', [ProductTemplateController::class, 'getNextParameterCombo']);
    Route::get('/{id}/next-unlinked-parameter', [ProductTemplateController::class, 'getNextUnlinkedParameter']);
});

// 测试路由，用于检查API是否可访问
Route::get('/test', function() {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now()->toIso8601String()
    ]);
});

// 商品分析相关API
Route::post('/analytics/impression/batch', [ProductAnalyticsController::class, 'recordImpressionBatch']);
Route::post('/analytics/click', [ProductAnalyticsController::class, 'recordClick']);

// 产品模板API
Route::get('/product-templates/{id}/next-unlinked-parameter', [TemplateApiController::class, 'nextUnlinkedParameter']);

// 产品分类的参数列表
Route::get('/product-categories/{id}/parameters', 'App\Http\Controllers\ProductCategoryController@getParameters');

// 添加不需要认证的参数组合路由
Route::get('/product-templates/{template}/next-parameter-combo', [App\Http\Controllers\ProductTemplateController::class, 'nextUnlinkedParameterCombo']);

// 公共API路由（无需认证）
Route::get('/product-templates/{template}/next-parameter-combo', [ProductTemplateController::class, 'nextUnlinkedParameterCombo']);
Route::get('/product-templates/{template}/all-parameter-combos', [ProductTemplateController::class, 'allParameterCombos']);
Route::get('/product-templates/{template}/parameter-combo-data', [ProductTemplateController::class, 'parameterComboData']);

// 需要认证的API路由
Route::middleware(['auth:sanctum', 'web'])->group(function () {
    // 产品相关路由
    Route::get('/products', [ApiProductController::class, 'index']);
    Route::get('/products/{product}/suppliers', [ApiProductController::class, 'suppliers']);
    
    // 分类相关路由
    Route::get('/product-categories/{category}/parameters', [ProductCategoryController::class, 'parameters']);
});

// 产品模板API - 无需认证的SPA支持路由
Route::get('/product-templates/{template}/next-parameter-combo', [WebProductTemplateController::class, 'nextUnlinkedParameterCombo']);
Route::get('/product-templates/{template}/all-parameter-combos', [WebProductTemplateController::class, 'allParameterCombos']);
Route::get('/product-templates/{template}/parameter-combo-data', [WebProductTemplateController::class, 'parameterComboData']);

// 认证相关路由
Route::middleware('auth:sanctum')->group(function () {
    // 用户资料
    Route::get('/user', function (Request $request) {
        // Consider returning customer data if the guard is 'customer'
        // Or check $request->user()->tokenCan(...) if needed
        return $request->user(); 
    });
    
    // Customer Address Routes (Moved here, using auth:sanctum)
    // Route::prefix('customer/addresses')->name('api.customer.addresses.')->group(function () {
    //     Route::get('/', [AddressController::class, 'index'])->name('index');
    //     Route::post('/', [AddressController::class, 'store'])->name('store');
    // });
    
    // 会话管理 (These might not be relevant for pure token auth)
    Route::get('/auth/check', [AuthController::class, 'checkSession']);
    Route::get('/auth/ping', [AuthController::class, 'ping']);
});

// 公共认证路由
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// 两步验证
Route::post('/auth/verify', [AuthController::class, 'verify']);
Route::post('/auth/resend-code', [AuthController::class, 'resendVerificationCode']);

/**
 * 批量请求处理
 */
Route::post('/batch', 'App\Http\Controllers\Api\BatchRequestController@process');

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
// use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryParameterController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Services\InventoryMailService;
use App\Models\Product;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\QualityInspectionController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PurchaseRefundController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CouponTemplateController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\Stock\StockMovementController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\EInvoiceController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\MediaDebugController;
use App\Http\Controllers\Admin\NotificationSettingsController;
use App\Http\Controllers\ProductTemplateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/health', function () {
        // Optionally check database connection or other dependencies
        // \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response('OK', 200);
    });
});


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // 添加通知历史路由 (位于settings组外)
    Route::get('/notification-history', [SettingsController::class, 'notificationHistory'])->name('notification-history');

    // Shop switch routes
    Route::get('/stores/current', [StoreController::class, 'current'])->name('stores.current');
    Route::post('/stores/switch', [StoreController::class, 'switch'])->name('stores.switch');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');

    // Shop switch routes (Renamed to avoid conflict)
    Route::get('/stores/switch/{store_id}', [App\Http\Controllers\StoreController::class, 'switch'])->name('stores.switch.link');

    // Company setting up routing
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('company-profile', [CompanyProfileController::class, 'edit'])
            ->name('company-profile.edit');
        Route::put('company-profile', [CompanyProfileController::class, 'update'])
            ->name('company-profile.update');

        // System setting routing
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        Route::get('search-employees', [SettingsController::class, 'searchEmployees'])->name('search-employees');

        // Notification Setting Up Routing
        Route::get('/notifications', [NotificationSettingController::class, 'index'])->name('notifications.index');
        Route::put('/notifications', [NotificationSettingController::class, 'update'])->name('notifications.update');
        Route::post('/notifications/toggle-email', [NotificationSettingController::class, 'toggleEmail'])->name('notifications.toggle-email');
    });

    // Set up a routing group
    Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
        // Redirect the old company settings page to the new
        Route::get('/company', function() {
            return redirect()->route('settings.company-profile.edit');
        })->name('company');
        
        Route::get('/system', [SettingsController::class, 'system'])->name('system');
        Route::put('/system', [SettingsController::class, 'updateSystem'])->name('system.update');
        
        // 自动采购设置路由
        Route::get('/auto-purchase', [SettingsController::class, 'autoPurchase'])->name('auto-purchase');
        Route::put('/auto-purchase', [SettingsController::class, 'updateAutoPurchase'])->name('update-auto-purchase');

        // 通知设置
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::put('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::post('/notifications/toggle-email', [SettingsController::class, 'toggleEmail'])->name('notifications.toggle-email');

        // 消息模板管理
        Route::get('/message-templates', [MessageTemplateController::class, 'index'])->name('message-templates');
        Route::get('/message-templates/create', [MessageTemplateController::class, 'create'])->name('message-templates.create');
        Route::post('/message-templates', [MessageTemplateController::class, 'store'])->name('message-templates.store');
        Route::get('/message-templates/{type}/{id}/edit', [MessageTemplateController::class, 'edit'])->name('message-templates.edit');
        Route::put('/message-templates/{id}', [MessageTemplateController::class, 'update'])->name('message-templates.update');
        Route::post('/message-templates/preview', [MessageTemplateController::class, 'preview'])->name('message-templates.preview');
    });

    // Add tocheck.companyMiddleware to routes that require company information
    Route::middleware(['check.company', 'store'])->group(function () {
        Route::get('purchases/{purchase}/export-pdf', [PurchaseController::class, 'exportPdf'])
            ->name('purchases.export-pdf');
    });

    Route::middleware(['store'])->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::get('customers/{customer}/details', [CustomerController::class, 'getCustomerDetails'])->name('customers.details');
        // Route::resource('prescriptions', PrescriptionController::class);
        Route::resource('products', ProductController::class);
        Route::post('admin/products/update-discount', [ProductController::class, 'updateDiscount'])->name('products.update-discount');
        // 批量折扣设置
        Route::get('products/discounts/bulk', [ProductController::class, 'bulkDiscountForm'])->name('products.discounts.bulk');
        Route::post('products/discounts/bulk', [ProductController::class, 'bulkDiscountUpdate'])->name('products.discounts.bulk.update');
        Route::resource('product-categories', ProductCategoryController::class, ['parameters' => [
            'product-categories' => 'category'
        ]]);

        // 产品模板路由
        Route::delete('product-templates/unlink-parameter-combo', [ProductTemplateController::class, 'unlinkParameterCombo'])->name('product-templates.unlink-parameter-combo');
        Route::post('product-templates/link-parameter-combo', [ProductTemplateController::class, 'linkParameterCombo'])->name('product-templates.link-parameter-combo');
        Route::post('product-templates/next-parameter-combo', [ProductTemplateController::class, 'nextParameterCombination'])->name('product-templates.next-parameter-combo');
        Route::post('product-templates/link-product', [ProductTemplateController::class, 'linkProduct'])->name('product-templates.link-product');
        Route::post('product-templates/{productTemplate}/remove-from-featured', [ProductTemplateController::class, 'removeFromFeatured'])->name('product-templates.remove-from-featured');
        Route::post('product-templates/{productTemplate}/remove-from-new-arrival', [ProductTemplateController::class, 'removeFromNewArrival'])->name('product-templates.remove-from-new-arrival');
        Route::post('product-templates/{productTemplate}/remove-from-sale', [ProductTemplateController::class, 'removeFromSale'])->name('product-templates.remove-from-sale');
        // 必须放在资源路由定义之前
        Route::resource('product-templates', ProductTemplateController::class);

        // 产品分类参数管理
        Route::prefix('product-categories/{category}/parameters')->name('category-parameters.')->middleware(['auth'])->group(function () {
            Route::get('/', [CategoryParameterController::class, 'index'])->name('index');
            Route::get('/create', [CategoryParameterController::class, 'create'])->name('create');
            Route::post('/', [CategoryParameterController::class, 'store'])->name('store');
            Route::get('/{parameter}/edit', [CategoryParameterController::class, 'edit'])->name('edit');
            Route::put('/{parameter}', [CategoryParameterController::class, 'update'])->name('update');
            Route::delete('/{parameter}', [CategoryParameterController::class, 'destroy'])->name('destroy');
        });

        // Get classification parameters
        Route::get('/product-categories/{category}/parameters', [ProductCategoryController::class, 'parameters'])
            ->name('product-categories.parameters');

        // Inventory Management Routing
        Route::prefix('stock')->name('stock.')->group(function () {
            // 库存管理
            Route::get('/', [StockController::class, 'index'])->name('index');
            Route::get('/create', [StockController::class, 'create'])->name('create');
            Route::post('/', [StockController::class, 'store'])->name('store');
            Route::get('/{stock}', [StockController::class, 'show'])->name('show');
            Route::get('/{stock}/edit', [StockController::class, 'edit'])->name('edit');
            Route::put('/{stock}', [StockController::class, 'update'])->name('update');
            Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');
            
            // 库存移动
            Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');
            
            // 低库存警报
            Route::get('/low-stock', [StockController::class, 'lowStock'])->name('low-stock');
            
            // 缺货警报
            Route::get('/out-of-stock', [StockController::class, 'outOfStock'])->name('out-of-stock');
        });

        // Test mail sending
        Route::get('/test-mail', function () {
            $mailService = app(InventoryMailService::class);
            
            // Create a test product
            $product = new Product([
                'name' => 'Test products',
                'sku' => 'TEST001',
                'inventory_count' => 5,
                'min_stock' => 10,
            ]);
            $product->id = 1;
            
            // Simulated classification data
            $category = new \App\Models\ProductCategory([
                'name' => 'Test classification'
            ]);
            $product->setRelation('category', $category);
            
            // Test inventory warning email
            $mailService->sendLowStockNotification($product);
            
            return 'The test email has been sent, please check the inbox.';
        });

        // Supplier-managed routing
        Route::resource('suppliers', SupplierController::class);

        // Supplier Product Management Routing
        Route::prefix('suppliers/{supplier}')->group(function () {
            Route::get('products', [SupplierController::class, 'products'])->name('suppliers.products.index');
            Route::post('products', [SupplierController::class, 'storeProduct'])->name('suppliers.products.store');
            Route::put('products/{product}', [SupplierController::class, 'updateProduct'])->name('suppliers.products.update');
            Route::delete('products/{product}', [SupplierController::class, 'destroyProduct'])->name('suppliers.products.destroy');
        });

        // Supplier price agreement management routing
        Route::prefix('suppliers/{supplier}')->group(function () {
            Route::get('agreements', [SupplierController::class, 'agreements'])->name('suppliers.agreements.index');
            Route::post('agreements', [SupplierController::class, 'storeAgreement'])->name('suppliers.agreements.store');
            Route::put('agreements/{agreement}', [SupplierController::class, 'updateAgreement'])->name('suppliers.agreements.update');
            Route::delete('agreements/{agreement}', [SupplierController::class, 'destroyAgreement'])->name('suppliers.agreements.destroy');
        });

        // Procurement Management Routing
        Route::middleware(['auth'])->group(function () {
            // Purchase Order
            Route::resource('purchases', PurchaseController::class);
            Route::post('purchases/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchases.approve');
            Route::post('/purchases/{purchase}/reject', [PurchaseController::class, 'reject'])->name('purchases.reject');
            Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
            Route::post('purchases/{purchase}/confirm-received', [PurchaseController::class, 'confirmReceived'])->name('purchases.confirm-received');
            Route::post('/purchases/{purchase}/send-to-supplier', [PurchaseController::class, 'sendToSupplier'])
                ->name('purchases.send-to-supplier');

            // Payment history
            Route::resource('purchases.payments', PaymentController::class)->except(['edit', 'update']);
        });

        // Inventory Router
        Route::prefix('stock')->name('stock.')->middleware(['auth'])->group(function () {
            Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
            Route::post('inventory/{inventory}/complete', [InventoryController::class, 'complete'])->name('inventory.complete');
            Route::post('inventory/{inventory}/cancel', [InventoryController::class, 'cancel'])->name('inventory.cancel');
            Route::get('inventory/{inventory}/export', [InventoryController::class, 'export'])->name('inventory.export.single');
            Route::resource('inventory', InventoryController::class);
        });

        // Warehouse management routing
        Route::resource('warehouses', WarehouseController::class);
        Route::get('warehouses/{warehouse}/address', [PurchaseController::class, 'getWarehouseAddress'])->name('warehouses.address');

        // Quality inspection routing
        Route::middleware(['auth'])->group(function () {
            Route::resource('quality-inspections', QualityInspectionController::class);
            Route::post('quality-inspections/{quality_inspection}/approve', [QualityInspectionController::class, 'approve'])->name('quality-inspections.approve');
            Route::post('quality-inspections/{quality_inspection}/reject', [QualityInspectionController::class, 'reject'])->name('quality-inspections.reject');
            Route::get('/purchases/{purchase}/items', [QualityInspectionController::class, 'getPurchaseItems'])
                ->name('purchases.items');
        });

        // Purchase return route
        Route::middleware(['auth'])->group(function () {
            Route::resource('purchase-returns', PurchaseReturnController::class);
            Route::post('purchase-returns/{purchase_return}/approve', [PurchaseReturnController::class, 'approve'])->name('purchase-returns.approve');
            Route::post('purchase-returns/{purchase_return}/reject', [PurchaseReturnController::class, 'reject'])->name('purchase-returns.reject');
            Route::post('purchase-returns/{purchase_return}/complete', [PurchaseReturnController::class, 'complete'])->name('purchase-returns.complete');
            
            // Refund management
            Route::resource('purchase-returns.refunds', PurchaseRefundController::class)->shallow();
        });

        // Media routes
        Route::prefix('media')->name('media.')->group(function () {
            Route::post('/', [MediaController::class, 'store'])->name('store');
            Route::post('associate', [MediaController::class, 'associate'])->name('associate');
            Route::delete('{media}', [MediaController::class, 'destroy'])->name('destroy');
        });

        // Employee management routing
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{employee}/avatar', [EmployeeController::class, 'updateAvatar'])->name('employees.avatar.update');
        Route::delete('employees/{employee}/avatar', [EmployeeController::class, 'deleteAvatar'])->name('employees.avatar.delete');

        // Notification Setting Up Routing
        Route::middleware(['auth', 'permission:manage notification settings'])->group(function () {
            // Route::get('/settings/notifications', [NotificationSettingController::class, 'index'])->name('settings.notifications.index');
            // Route::put('/settings/notifications', [NotificationSettingController::class, 'update'])->name('settings.notifications.update');
        });

        
        // Shopping Cart Routes
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::get('/cart/customer/{customer_id}', [CartController::class, 'getCustomerCart'])->name('cart.customer');
        Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
        Route::post('/cart/update/{id}', [CartController::class, 'updateCartItem'])->name('cart.update');
        Route::delete('/cart/remove/{id}', [CartController::class, 'removeCartItem'])->name('cart.remove');
        Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
        Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');
        
        // Checkout Routes
        Route::get('/checkout', App\Livewire\Checkout::class)->name('checkout.index')->middleware('auth:customer');
        Route::get('/checkout/success', function() {
            if (!session('order_success')) {
                return redirect()->route('dashboard');
            }
            return view('checkout.success');
        })->name('checkout.success')->middleware('auth:customer');
        
        // Order Routes
        Route::get('/orders', [SalesOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [SalesOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/update-status', [SalesOrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/{order}/update-payment-status', [SalesOrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');
        Route::post('/orders/{order}/cancel', [SalesOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/orders/{order}/export-pdf', [SalesOrderController::class, 'exportPdf'])->name('orders.exportPdf');

        // Return Routes
        Route::get('/orders/{order}/returns/create', [ReturnController::class, 'create'])->name('returns.create');
        Route::post('/orders/{order}/returns', [ReturnController::class, 'store'])->name('returns.store');
        Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
        Route::get('/returns/{return}', [ReturnController::class, 'show'])->name('returns.show');
        Route::post('/returns/{return}/process', [ReturnController::class, 'process'])->name('returns.process');

        // E-Invoice Routes
        Route::get('/invoices', [EInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [EInvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [EInvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [EInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/edit', [EInvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [EInvoiceController::class, 'update'])->name('invoices.update');
        Route::post('/invoices/{invoice}/submit', [EInvoiceController::class, 'submit'])->name('invoices.submit');
        Route::get('/invoices/{invoice}/check-status', [EInvoiceController::class, 'checkStatus'])->name('invoices.checkStatus');
        Route::get('/invoices/{invoice}/download-pdf', [EInvoiceController::class, 'downloadPdf'])->name('invoices.downloadPdf');
        Route::get('/orders/{order}/create-invoice', [EInvoiceController::class, 'createFromOrder'])->name('orders.createInvoice');
    });

    // Profile routing
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // 调试路由
    Route::middleware(['auth'])->prefix('debug')->name('debug.')->group(function () {
        Route::get('/test-upload', [DebugController::class, 'testUpload'])->name('test-upload');
        Route::post('/upload', [DebugController::class, 'handleUpload'])->name('handle-upload');
        Route::get('/check-csrf', [DebugController::class, 'checkCsrf'])->name('check-csrf');
    });

    // Test route for validation
    Route::get('/test-validation', function () {
        return view('test-validation');
    })->middleware(['auth']);

    // Test routes
    Route::post('/test-email', [TestController::class, 'sendTestEmail'])->name('test.email');

    // 促销活动管理路由
    Route::resource('promotions', App\Http\Controllers\PromotionController::class);
    Route::post('/promotions/{promotion}/toggle-status', [App\Http\Controllers\PromotionController::class, 'toggleStatus'])->name('promotions.toggle-status');

    // Test routes
    Route::get('/test-verification', function () {
        return view('test-verification');
    })->name('test-verification');

    // 管理员路由
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // 管理员仪表盘
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'adminDashboard'])->name('dashboard');
        
        // 通知设置
        Route::get('/settings/notifications', [NotificationSettingsController::class, 'index'])->name('settings.notifications');
        Route::post('/settings/notifications', [NotificationSettingsController::class, 'update'])->name('settings.notifications.update');
        
        // 首页管理路由
        Route::get('/homepage', [App\Http\Controllers\Admin\HomepageController::class, 'index'])->name('homepage.index');
        Route::put('/homepage/stock-threshold', [App\Http\Controllers\Admin\HomepageController::class, 'updateStockThreshold'])->name('homepage.stock-threshold.update');
        Route::post('/homepage/run-stock-filter', [App\Http\Controllers\Admin\HomepageController::class, 'runStockFilter'])->name('homepage.run-stock-filter');
        Route::post('/products/update-featured/{product}', [App\Http\Controllers\Admin\HomepageController::class, 'updateFeatured'])->name('products.update-featured');
        Route::post('/products/update-new-arrival/{product}', [App\Http\Controllers\Admin\HomepageController::class, 'updateNewArrival'])->name('products.update-new-arrival');
        Route::post('/products/update-sale/{product}', [App\Http\Controllers\Admin\HomepageController::class, 'updateSale'])->name('products.update-sale');
        
        // 新增批量添加产品到首页区域的路由
        Route::post('/homepage/add-featured-products', [App\Http\Controllers\Admin\HomepageController::class, 'addFeaturedProducts'])->name('homepage.add-featured-products');
        Route::post('/homepage/add-new-arrival-products', [App\Http\Controllers\Admin\HomepageController::class, 'addNewArrivalProducts'])->name('homepage.add-new-arrival-products');
        Route::post('/homepage/add-sale-products', [App\Http\Controllers\Admin\HomepageController::class, 'addSaleProducts'])->name('homepage.add-sale-products');
        
        // 产品排序更新路由
        Route::post('/homepage/update-featured-order', [App\Http\Controllers\Admin\HomepageController::class, 'updateFeaturedOrder'])->name('homepage.update-featured-order');
        Route::post('/homepage/update-new-arrival-order', [App\Http\Controllers\Admin\HomepageController::class, 'updateNewArrivalOrder'])->name('homepage.update-new-arrival-order');
        Route::post('/homepage/update-sale-order', [App\Http\Controllers\Admin\HomepageController::class, 'updateSaleOrder'])->name('homepage.update-sale-order');
        
        // 产品搜索API（用于模态框中展示可选产品）
        Route::get('/products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');

        // Banner管理路由
        Route::get('/banners/list', [App\Http\Controllers\Admin\HomepageController::class, 'getBannersList'])->name('banners.list');
        Route::post('/banners/update-order', [App\Http\Controllers\Admin\HomepageController::class, 'updateBannerOrder'])->name('banners.update-order');
        Route::post('/banners/{banner}/toggle-active', [App\Http\Controllers\Admin\HomepageController::class, 'toggleBannerActive'])->name('banners.toggle-active');
        Route::post('/banners/quick-create', [App\Http\Controllers\Admin\HomepageController::class, 'quickCreateBanner'])->name('banners.quick-create');
        Route::delete('/banners/{banner}', [App\Http\Controllers\Admin\HomepageController::class, 'destroyBanner'])->name('banners.destroy');
        Route::post('/carousel/settings', [App\Http\Controllers\Admin\HomepageController::class, 'updateCarouselSettings'])->name('carousel.settings.update');
        Route::get('/carousel/settings', [App\Http\Controllers\Admin\HomepageController::class, 'getCarouselSettings'])->name('carousel.settings.get');

        // 添加主页设置相关路由
        Route::post('/homepage/settings', [App\Http\Controllers\Admin\HomepageController::class, 'updateHomepageSettings'])->name('homepage.settings.update');
        Route::get('/homepage/settings', [App\Http\Controllers\Admin\HomepageController::class, 'getHomepageSettings'])->name('homepage.settings.get');

        // 新增产品列表路由
        Route::get('/products/list', [App\Http\Controllers\Admin\HomepageController::class, 'getProductsList'])->name('products.list');
    });

    // 分析管理路由
    Route::prefix('admin/analytics')->name('admin.analytics.')->middleware(['auth', 'role:admin|super-admin|manager'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/products/{productId}', [App\Http\Controllers\Admin\AnalyticsController::class, 'productAnalytics'])->name('product');
        Route::get('/ab-tests', [App\Http\Controllers\Admin\AnalyticsController::class, 'abTests'])->name('ab-tests');
    });

    // 添加缺失的Email验证路由
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
});

// 管理员路由
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    // 设置路由
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
});

// 仅在调试模式下提供日志查看
if (config('app.debug')) {
    Route::get('/debug/logs', function () {
        $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
        
        if (!file_exists($logFile)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }
        
        $logContent = file_get_contents($logFile);
        $logEntries = [];
        
        // 解析日志条目
        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] .*?(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s';
        preg_match_all($pattern, $logContent, $matches);
        
        foreach ($matches[0] as $entry) {
            $logEntries[] = $entry;
        }
        
        // 按时间逆序排列
        $logEntries = array_reverse($logEntries);
        
        return view('debug.logs', [
            'logEntries' => $logEntries,
            'today' => date('Y-m-d')
        ]);
    })->name('debug.logs');
    
    // 产品模板控制器调试
    Route::get('/debug/product-templates', [App\Http\Controllers\ProductTemplateController::class, 'debug'])->name('debug.product-templates');
}
// 产品参数值链接路由
Route::get('/products/link-parameter-value', [ProductController::class, 'linkParameterValue'])->name('products.link-parameter-value');
Route::post('/products/store-parameter-value-link', [ProductController::class, 'storeParameterValueLink'])->name('products.store-parameter-value-link');
Route::delete('/products/unlink-parameter-value', [ProductController::class, 'unlinkParameterValue'])->name('products.unlink-parameter-value');

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
*/
Route::prefix('test')->group(function () {
    Route::get('/settings', [App\Http\Controllers\TestSettingsController::class, 'index']);
    Route::get('/settings/{group}', [App\Http\Controllers\TestSettingsController::class, 'getGroup']);
    Route::post('/settings', [App\Http\Controllers\TestSettingsController::class, 'saveTest']);
    
    // 测试页面
    Route::get('/settings-page', function () {
        return view('test-settings');
    });
});

Route::prefix('admin/homepage')->middleware(['auth'])->name('admin.homepage.')->group(function () {
    // Existing index route
    Route::get('/', [App\Http\Controllers\Admin\HomepageController::class, 'index'])->name('index'); 

    // Route to get available product templates for modal
    Route::get('available-products/{type}', [App\Http\Controllers\Admin\HomepageController::class, 'getAvailableProducts'])->name('availableProducts');

    // Route to add selected product templates to a section
    Route::post('add-products/{type}', [App\Http\Controllers\Admin\HomepageController::class, 'addProducts'])->name('addProducts');

    // --- NEW: Route to get products for a specific section (for refresh) ---
    Route::get('section-products/{type}', [App\Http\Controllers\Admin\HomepageController::class, 'getSectionProducts'])->name('sectionProducts');

    // Existing settings update route etc.
    Route::put('/stock-threshold', [App\Http\Controllers\Admin\HomepageController::class, 'updateStockThreshold'])->name('stock-threshold.update');
    Route::post('/run-stock-filter', [App\Http\Controllers\Admin\HomepageController::class, 'runStockFilter'])->name('run-stock-filter');
    Route::post('/settings', [App\Http\Controllers\Admin\HomepageController::class, 'updateHomepageSettings'])->name('settings.update'); // Assumed from JS
    // Add other routes like remove, update order etc. if they aren't already defined elsewhere
});

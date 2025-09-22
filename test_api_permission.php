<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\DB;

// 启动Laravel应用程序
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 检查数据库中的路由
$routes = Route::getRoutes();
echo "Checking routes with '/products' pattern:\n";
foreach ($routes as $route) {
    if (strpos($route->uri(), 'products') !== false) {
        $middlewares = $route->gatherMiddleware();
        $method = implode('|', $route->methods());
        echo "- {$method} {$route->uri()}: " . implode(', ', $middlewares) . "\n";
    }
}

// 获取管理员用户并手动登录
$admin = \App\Models\User::where('email', 'ethankhoo09@gmail.com')->first();
Auth::login($admin);
echo "\nLogged in as: " . Auth::user()->name . "\n";

// 创建请求对象模拟API请求
$request = Request::create('/api/products', 'GET');
$request->headers->set('Accept', 'application/json');

// 手动运行CheckPermission中间件
echo "\nTesting CheckPermission middleware with 'view products' permission:\n";
$middleware = new CheckPermission();
$response = $middleware->handle($request, function ($req) {
    return response()->json(['success' => true]);
}, 'view products');

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content: " . $response->getContent() . "\n";

// 检查所有api/products路由的中间件
echo "\nMiddleware applied to api/products routes:\n";
foreach ($routes as $route) {
    if ($route->uri() === 'api/products' && in_array('GET', $route->methods())) {
        $middlewares = $route->gatherMiddleware();
        echo "- GET api/products: " . implode(', ', $middlewares) . "\n";
        
        // 检查每个中间件
        foreach ($middlewares as $middleware) {
            echo "  - {$middleware}\n";
            
            // 检查权限中间件
            if (strpos($middleware, 'permission:') === 0) {
                $permission = substr($middleware, strlen('permission:'));
                echo "    Permission checked: {$permission}\n";
                echo "    Admin has this permission: " . ($admin->hasPermissionTo($permission) ? 'YES' : 'NO') . "\n";
            }
        }
    }
}

echo "\nDone.\n"; 
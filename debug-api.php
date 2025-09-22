<?php

// 清除先前的日志
$debugLogFile = __DIR__ . '/storage/logs/api-debug.log';
file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - API 调试开始\n");

// 记录详细的接口调用情况
function debugLog($message, $data = []) {
    global $debugLogFile;
    $output = date('Y-m-d H:i:s') . " - $message";
    if (!empty($data)) {
        $output .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $output .= "\n";
    file_put_contents($debugLogFile, $output, FILE_APPEND);
}

// 输出所有路由
debugLog("所有API路由:");
$routes = [];
foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
    if (strpos($route->uri, 'api/') === 0) {
        $routes[] = [
            'uri' => $route->uri,
            'methods' => $route->methods,
            'action' => $route->action['uses'] ?? 'Closure'
        ];
    }
}
debugLog("API路由数量: " . count($routes), $routes);

// 检查特定路由
debugLog("检查登录路由");
$loginRoutes = [];
foreach ($routes as $route) {
    if (strpos($route['uri'], 'login') !== false) {
        $loginRoutes[] = $route;
    }
}
debugLog("登录相关路由:", $loginRoutes);

// 模拟一个登录请求
debugLog("测试登录请求");
try {
    // 创建请求对象
    $request = new \Illuminate\Http\Request();
    $request->replace([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);
    
    // 创建控制器实例
    $authController = new \App\Http\Controllers\Api\AuthController();
    
    // 调用登录方法
    $response = $authController->login($request);
    
    // 记录响应
    debugLog("登录响应:", [
        'status' => $response->getStatusCode(),
        'content' => json_decode($response->getContent(), true)
    ]);
} catch (\Exception $e) {
    debugLog("登录请求失败:", [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

// 检查AuthController
try {
    $reflector = new \ReflectionClass(\App\Http\Controllers\Api\AuthController::class);
    $methods = $reflector->getMethods();
    $methodNames = [];
    foreach ($methods as $method) {
        if ($method->isPublic() && !$method->isConstructor()) {
            $methodNames[] = $method->getName();
        }
    }
    debugLog("AuthController可用方法:", $methodNames);
} catch (\Exception $e) {
    debugLog("获取AuthController方法失败:", [
        'message' => $e->getMessage()
    ]);
}

// 检查CustomerController
try {
    $reflector = new \ReflectionClass(\App\Http\Controllers\Api\CustomerController::class);
    $methods = $reflector->getMethods();
    $methodNames = [];
    foreach ($methods as $method) {
        if ($method->isPublic() && !$method->isConstructor()) {
            $methodNames[] = $method->getName();
        }
    }
    debugLog("CustomerController可用方法:", $methodNames);
} catch (\Exception $e) {
    debugLog("获取CustomerController方法失败:", [
        'message' => $e->getMessage()
    ]);
}

// 输出环境信息
debugLog("环境信息:", [
    'PHP版本' => PHP_VERSION,
    'Laravel版本' => app()->version(),
    'APP_URL' => env('APP_URL'),
    'CORS配置' => [
        'allowed_origins' => config('cors.allowed_origins', []),
        'supports_credentials' => config('cors.supports_credentials', false)
    ],
    'Session配置' => [
        'driver' => config('session.driver'),
        'cookie' => config('session.cookie'),
        'domain' => config('session.domain')
    ]
]);

echo "API调试已完成，请查看日志: $debugLogFile"; 
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use ReflectionClass;

class DebugApiRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug API routes and login issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 清除先前的日志
        $debugLogFile = storage_path('logs/api-debug.log');
        file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - API 调试开始\n");

        // 记录详细的接口调用情况
        $this->debugLog($debugLogFile, "所有API路由:");
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            if (strpos($route->uri, 'api/') === 0) {
                $routes[] = [
                    'uri' => $route->uri,
                    'methods' => $route->methods,
                    'action' => is_string($route->action['uses'] ?? null) 
                        ? $route->action['uses'] 
                        : 'Closure'
                ];
            }
        }
        $this->debugLog($debugLogFile, "API路由数量: " . count($routes), $routes);

        // 检查特定路由
        $this->debugLog($debugLogFile, "检查登录路由");
        $loginRoutes = [];
        foreach ($routes as $route) {
            if (strpos($route['uri'], 'login') !== false || 
                (is_string($route['action']) && strpos($route['action'], 'login') !== false)) {
                $loginRoutes[] = $route;
            }
        }
        $this->debugLog($debugLogFile, "登录相关路由:", $loginRoutes);
        
        // 检查特定的路由
        $customerLoginRoute = null;
        foreach (Route::getRoutes() as $route) {
            if ($route->uri == 'api/customer/login') {
                $customerLoginRoute = [
                    'uri' => $route->uri,
                    'methods' => $route->methods,
                    'action' => $route->action['uses'] ?? 'Closure',
                    'middlewares' => $route->action['middleware'] ?? []
                ];
                break;
            }
        }
        $this->debugLog($debugLogFile, "客户登录路由详情:", $customerLoginRoute);

        // 检查AuthController
        try {
            $reflector = new ReflectionClass(AuthController::class);
            $methods = $reflector->getMethods();
            $methodNames = [];
            foreach ($methods as $method) {
                if ($method->isPublic() && !$method->isConstructor()) {
                    $methodNames[] = $method->getName();
                }
            }
            $this->debugLog($debugLogFile, "AuthController可用方法:", $methodNames);
        } catch (\Exception $e) {
            $this->debugLog($debugLogFile, "获取AuthController方法失败:", [
                'message' => $e->getMessage()
            ]);
        }

        // 检查CustomerController
        try {
            $reflector = new ReflectionClass(CustomerController::class);
            $methods = $reflector->getMethods();
            $methodNames = [];
            foreach ($methods as $method) {
                if ($method->isPublic() && !$method->isConstructor()) {
                    $methodNames[] = $method->getName();
                }
            }
            $this->debugLog($debugLogFile, "CustomerController可用方法:", $methodNames);
        } catch (\Exception $e) {
            $this->debugLog($debugLogFile, "获取CustomerController方法失败:", [
                'message' => $e->getMessage()
            ]);
        }

        // 测试AuthController的login方法
        $this->debugLog($debugLogFile, "测试AuthController.login方法");
        try {
            // 创建请求对象
            $request = new Request();
            $request->replace([
                'email' => 'test@example.com',
                'password' => 'password123'
            ]);
            
            // 创建控制器实例
            $authController = new AuthController();
            
            // 调用登录方法
            $response = $authController->login($request);
            
            // 记录响应
            $this->debugLog($debugLogFile, "登录响应:", [
                'status' => $response->getStatusCode(),
                'content' => json_decode($response->getContent(), true)
            ]);
        } catch (\Exception $e) {
            $this->debugLog($debugLogFile, "登录请求失败:", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // 测试CustomerController的login方法
        $this->debugLog($debugLogFile, "测试CustomerController.login方法");
        try {
            // 创建请求对象
            $request = new Request();
            $request->replace([
                'email' => 'test@example.com',
                'password' => 'password123'
            ]);
            
            // 创建控制器实例
            $customerController = new CustomerController();
            
            // 调用登录方法
            $response = $customerController->login($request);
            
            // 记录响应
            $this->debugLog($debugLogFile, "登录响应:", [
                'status' => $response->getStatusCode(),
                'content' => json_decode($response->getContent(), true)
            ]);
        } catch (\Exception $e) {
            $this->debugLog($debugLogFile, "登录请求失败:", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // 输出环境信息
        $this->debugLog($debugLogFile, "环境信息:", [
            'PHP版本' => PHP_VERSION,
            'Laravel版本' => app()->version(),
            'APP_URL' => config('app.url'),
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

        $this->info("API调试已完成，请查看日志: $debugLogFile");
    }

    /**
     * 记录调试信息到日志文件
     */
    private function debugLog($debugLogFile, $message, $data = [])
    {
        $output = date('Y-m-d H:i:s') . " - $message";
        if (!empty($data)) {
            $output .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        $output .= "\n";
        file_put_contents($debugLogFile, $output, FILE_APPEND);
        
        // 同时输出到控制台
        $this->line($message);
        if (!empty($data)) {
            if (is_array($data) && count($data) > 10) {
                $this->line("数据过多，已记录到日志文件");
            } else {
                $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
} 
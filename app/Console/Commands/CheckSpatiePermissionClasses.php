<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ReflectionClass;

class CheckSpatiePermissionClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:spatie-permission-classes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all available classes in Spatie Permission package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Spatie Permission package classes...');
        
        // 尝试按照文档中的标准命名空间查找中间件
        $expectedMiddlewares = [
            '\Spatie\Permission\Middlewares\RoleMiddleware',
            '\Spatie\Permission\Middleware\RoleMiddleware',
            '\Spatie\Permission\Http\Middleware\RoleMiddleware',
            '\Spatie\Permission\Middlewares\PermissionMiddleware',
            '\Spatie\Permission\Middleware\PermissionMiddleware',
            '\Spatie\Permission\Http\Middleware\PermissionMiddleware',
            '\Spatie\Permission\Middlewares\RoleOrPermissionMiddleware',
            '\Spatie\Permission\Middleware\RoleOrPermissionMiddleware',
            '\Spatie\Permission\Http\Middleware\RoleOrPermissionMiddleware',
        ];
        
        $this->info('Checking expected middleware classes:');
        foreach($expectedMiddlewares as $middleware) {
            if(class_exists($middleware)) {
                $this->info("[FOUND] $middleware");
                
                // 获取类的详细信息
                $reflection = new ReflectionClass($middleware);
                $this->info("  - Namespace: " . $reflection->getNamespaceName());
                $this->info("  - Methods: " . implode(', ', array_map(function($method) {
                    return $method->getName();
                }, $reflection->getMethods())));
            } else {
                $this->error("[NOT FOUND] $middleware");
            }
        }
        
        // 检查已安装的Spatie Permission包信息
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $this->info("\nInstalled Spatie Permission package:");
        if(isset($composer['require']['spatie/laravel-permission'])) {
            $this->info("Version requirement: " . $composer['require']['spatie/laravel-permission']);
        } else {
            $this->error("spatie/laravel-permission not found in composer.json");
        }
        
        // 遍历vendor目录查找实际的中间件文件
        $this->info("\nSearching for middleware files in vendor directory...");
        $vendorDir = base_path('vendor/spatie/laravel-permission');
        if(is_dir($vendorDir)) {
            $middlewareFiles = $this->findFiles($vendorDir, 'Middleware');
            foreach($middlewareFiles as $file) {
                $this->info("[FILE] $file");
                // 尝试推断类名
                $className = $this->getClassNameFromFile($file);
                if($className) {
                    $this->info("  - Class: $className");
                }
            }
        } else {
            $this->error("Vendor directory not found: $vendorDir");
        }
        
        return 0;
    }
    
    /**
     * 递归查找包含特定字符串的文件
     */
    private function findFiles($dir, $search) {
        $results = [];
        $files = scandir($dir);
        
        foreach($files as $file) {
            if($file === '.' || $file === '..') continue;
            
            $path = $dir . '/' . $file;
            if(is_dir($path)) {
                $results = array_merge($results, $this->findFiles($path, $search));
            } else {
                if(strpos($file, $search) !== false || strpos($path, $search) !== false) {
                    $results[] = $path;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * 从文件中提取类名
     */
    private function getClassNameFromFile($file) {
        $content = file_get_contents($file);
        if(preg_match('/namespace\s+([^;]+);.*?class\s+(\w+)/s', $content, $matches)) {
            return $matches[1] . '\\' . $matches[2];
        }
        return null;
    }
}

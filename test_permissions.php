<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// 启动Laravel应用程序
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Permission system...\n";

// 尝试查找管理员用户
$admin = \App\Models\User::where('email', 'ethankhoo09@gmail.com')->first();

if (!$admin) {
    echo "Admin user not found!\n";
    exit;
}

echo "Found admin user: {$admin->name} ({$admin->email})\n";

// 检查用户角色
$roles = $admin->getRoleNames();
echo "User roles: " . implode(', ', $roles->toArray()) . "\n";

// 检查 view products 权限
$permName = 'view products';
$hasPermission = $admin->hasPermissionTo($permName);
echo "Has permission '{$permName}': " . ($hasPermission ? 'YES' : 'NO') . "\n";

// 检查已分配的所有权限
$allPermissions = $admin->getAllPermissions();
echo "All permissions: " . count($allPermissions) . "\n";
foreach ($allPermissions as $permission) {
    echo "- {$permission->name}\n";
}

// 检查 view_products (使用下划线格式)的权限
$permName = 'view_products';
$hasPermission = $admin->hasPermissionTo($permName);
echo "Has permission '{$permName}': " . ($hasPermission ? 'YES' : 'NO') . "\n";

// 检查数据库中的权限
$permissions = \Spatie\Permission\Models\Permission::all();
echo "\nAll permissions in database: " . $permissions->count() . "\n";
foreach ($permissions->take(10) as $permission) {
    echo "- {$permission->id}: {$permission->name} (guard: {$permission->guard_name})\n";
}

echo "\nDone.\n"; 
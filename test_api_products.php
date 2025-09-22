<?php
// test_api_products.php - 测试API产品路由访问

echo "Testing /api/products route...\n\n";

// 获取当前项目的URL路径
$projectPath = "optic-system"; // 基于当前部署位置

// 列出所有包含products的路由及其中间件
$routes = [];
exec('php artisan route:list', $routes);
$productRoutes = array_filter($routes, function($line) {
    return strpos($line, 'products') !== false;
});

echo "Routes containing 'products':\n";
foreach ($productRoutes as $route) {
    echo "- " . trim($route) . "\n";
}

// 直接调用API测试
echo "\nTesting direct API call:\n";
$url = "http://localhost/Laravel/{$projectPath}/api/products";
echo "URL: {$url}\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
// 添加Accept头，避免返回HTML视图
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: " . $status . "\n";
echo "Content: " . $response . "\n\n";

echo "Done.\n"; 
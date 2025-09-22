<?php

// 检查是否存在banner创建功能
$quickCreateUrl = '/admin/banners/quick-create';
$appUrl = config('app.url', 'http://localhost');
$fullUrl = $appUrl . $quickCreateUrl;

echo '<h1>Banner Creation Test</h1>';
echo '<p>当前URL: ' . $_SERVER['REQUEST_URI'] . '</p>';
echo '<p>Banner创建URL: ' . $quickCreateUrl . '</p>';

// 检查HomepageController是否存在quickCreateBanner方法
try {
    $controller = new \App\Http\Controllers\Admin\HomepageController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('quickCreateBanner');
    echo '<p style="color:green">HomepageController中的quickCreateBanner方法存在</p>';
} catch (\Exception $e) {
    echo '<p style="color:red">HomepageController中的quickCreateBanner方法不存在或无法访问: ' . $e->getMessage() . '</p>';
}

// 检查路由是否存在
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$routeExists = false;

foreach ($routes as $route) {
    if ($route->uri() == 'admin/banners/quick-create' && in_array('POST', $route->methods())) {
        $routeExists = true;
        $controllerAction = $route->getAction()['controller'] ?? 'Unknown';
        echo '<p style="color:green">路由存在，指向控制器方法: ' . $controllerAction . '</p>';
        break;
    }
}

if (!$routeExists) {
    echo '<p style="color:red">未找到路由: ' . $quickCreateUrl . '</p>';
}

// 显示测试表单
echo '<h2>测试表单</h2>';
echo '<form action="' . $quickCreateUrl . '" method="POST" enctype="multipart/form-data">';
echo csrf_field();
echo '<div><label>标题: <input type="text" name="title" value="Test Banner"></label></div>';
echo '<div><label>副标题: <input type="text" name="subtitle" value="Test Subtitle"></label></div>';
echo '<div><label>按钮文本: <input type="text" name="button_text" value="Click Me"></label></div>';
echo '<div><label>按钮链接: <input type="text" name="button_link" value="/test"></label></div>';
echo '<div><label>激活状态: <input type="checkbox" name="is_active" value="1" checked></label></div>';
echo '<div><label>图片: <input type="file" name="file"></label></div>';
echo '<div><button type="submit">提交测试</button></div>';
echo '</form>'; 
<?php
// Bootstrap Laravel
require '../vendor/autoload.php';
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// 获取CSRF Token
$token = csrf_token();

// 构造表单
$form = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Test Link Form</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test Parameter Link</h1>
    
    <form id="link-form" method="POST" action="/products/store-parameter-value-link">
        <input type="hidden" name="_token" value="$token">
        
        <div class="form-group">
            <label for="product_id">Product ID:</label>
            <input type="number" id="product_id" name="product_id" required>
        </div>
        
        <div class="form-group">
            <label for="template_id">Template ID:</label>
            <input type="number" id="template_id" name="template_id" required>
        </div>
        
        <div class="form-group">
            <label for="parameter_group">Parameter Group:</label>
            <input type="text" id="parameter_group" name="parameter_group" required>
        </div>
        
        <div class="form-group">
            <label for="parameter_value">Parameter Value:</label>
            <input type="text" id="parameter_value" name="parameter_value" required>
        </div>
        
        <button type="submit">Link Parameter</button>
    </form>
    
    <h2>Available Products</h2>
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>SKU</th>
        </tr>
HTML;

// 获取产品列表
$products = App\Models\Product::all();
foreach ($products as $product) {
    $form .= "<tr>
        <td>{$product->id}</td>
        <td>{$product->name}</td>
        <td>{$product->sku}</td>
    </tr>";
}

$form .= "</table>

    <h2>Available Templates</h2>
    <table border=\"1\" style=\"width: 100%; border-collapse: collapse;\">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Parameters</th>
        </tr>";

// 获取模板列表
$templates = App\Models\ProductTemplate::all();
foreach ($templates as $template) {
    $params = $template->parameters;
    if (is_string($params)) {
        $params = json_decode($params, true);
    }
    $params = json_encode($params, JSON_PRETTY_PRINT);
    
    $form .= "<tr>
        <td>{$template->id}</td>
        <td>{$template->name}</td>
        <td><pre>" . htmlspecialchars($params) . "</pre></td>
    </tr>";
}

$form .= "</table>

</body>
</html>";

echo $form;
?> 
// Bootstrap Laravel
require '../vendor/autoload.php';
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// 获取CSRF Token
$token = csrf_token();

// 构造表单
$form = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Test Link Form</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test Parameter Link</h1>
    
    <form id="link-form" method="POST" action="/products/store-parameter-value-link">
        <input type="hidden" name="_token" value="$token">
        
        <div class="form-group">
            <label for="product_id">Product ID:</label>
            <input type="number" id="product_id" name="product_id" required>
        </div>
        
        <div class="form-group">
            <label for="template_id">Template ID:</label>
            <input type="number" id="template_id" name="template_id" required>
        </div>
        
        <div class="form-group">
            <label for="parameter_group">Parameter Group:</label>
            <input type="text" id="parameter_group" name="parameter_group" required>
        </div>
        
        <div class="form-group">
            <label for="parameter_value">Parameter Value:</label>
            <input type="text" id="parameter_value" name="parameter_value" required>
        </div>
        
        <button type="submit">Link Parameter</button>
    </form>
    
    <h2>Available Products</h2>
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>SKU</th>
        </tr>
HTML;

// 获取产品列表
$products = App\Models\Product::all();
foreach ($products as $product) {
    $form .= "<tr>
        <td>{$product->id}</td>
        <td>{$product->name}</td>
        <td>{$product->sku}</td>
    </tr>";
}

$form .= "</table>

    <h2>Available Templates</h2>
    <table border=\"1\" style=\"width: 100%; border-collapse: collapse;\">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Parameters</th>
        </tr>";

// 获取模板列表
$templates = App\Models\ProductTemplate::all();
foreach ($templates as $template) {
    $params = $template->parameters;
    if (is_string($params)) {
        $params = json_decode($params, true);
    }
    $params = json_encode($params, JSON_PRETTY_PRINT);
    
    $form .= "<tr>
        <td>{$template->id}</td>
        <td>{$template->name}</td>
        <td><pre>" . htmlspecialchars($params) . "</pre></td>
    </tr>";
}

$form .= "</table>

</body>
</html>";

echo $form;
?> 
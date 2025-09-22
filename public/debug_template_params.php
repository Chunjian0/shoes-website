<?php
// Bootstrap Laravel
require '../vendor/autoload.php';
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// 获取模板ID参数
$templateId = $_GET['id'] ?? 1;

// 使用Laravel的ORM获取模板
$template = App\Models\ProductTemplate::with('linkedProducts')->find($templateId);

if (!$template) {
    echo "Template with ID {$templateId} not found.";
    exit;
}

// 头部
echo "<!DOCTYPE html>
<html>
<head>
    <title>Template Parameters Debug</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .parameter { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .linked-product { background: #e6f7e6; padding: 10px; margin-top: 10px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Template Parameters Debug</h1>
    <p><strong>Template ID:</strong> {$template->id}</p>
    <p><strong>Template Name:</strong> {$template->name}</p>
    
    <h2>Raw Parameters Data</h2>
    <pre>" . htmlspecialchars(var_export($template->getRawOriginal('parameters'), true)) . "</pre>
    
    <h2>Processed Parameters</h2>";

$parameters = $template->parameters;
if (is_string($parameters)) {
    try {
        $parameters = json_decode($parameters, true);
    } catch (\Exception $e) {
        $parameters = [];
    }
}

if (!is_array($parameters)) {
    $parameters = [];
}

echo "<pre>" . htmlspecialchars(json_encode($parameters, JSON_PRETTY_PRINT)) . "</pre>";

echo "<h2>Linked Products</h2>";
if ($template->linkedProducts->isEmpty()) {
    echo "<p>No linked products found.</p>";
} else {
    echo "<table>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Parameter Group</th>
        </tr>";
    
    foreach ($template->linkedProducts as $product) {
        echo "<tr>
            <td>{$product->id}</td>
            <td>{$product->name}</td>
            <td>{$product->pivot->parameter_group}</td>
        </tr>";
    }
    
    echo "</table>";
}

// 分析参数链接
echo "<h2>Parameter Link Analysis</h2>";
foreach ($parameters as $param) {
    if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
        continue;
    }
    
    echo "<div class='parameter'>
        <h3>Parameter: {$param['name']}</h3>
        <p>Values: " . implode(", ", $param['values']) . "</p>";
    
    foreach ($param['values'] as $value) {
        $paramIdentifier = $param['name'] . '=' . $value;
        $linkedProduct = null;
        
        foreach ($template->linkedProducts as $product) {
            if ($product->pivot->parameter_group === $paramIdentifier) {
                $linkedProduct = $product;
                break;
            }
        }
        
        echo "<div>
            <p>Value: <strong>{$value}</strong></p>
            <p>Identifier: <strong>{$paramIdentifier}</strong></p>";
        
        if ($linkedProduct) {
            echo "<div class='linked-product'>
                <p>Linked to product: <strong>{$linkedProduct->name}</strong> (ID: {$linkedProduct->id})</p>
            </div>";
        } else {
            echo "<p>Not linked to any product</p>";
        }
        
        echo "</div>";
    }
    
    echo "</div>";
}

echo "</body></html>";
?> 
// Bootstrap Laravel
require '../vendor/autoload.php';
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// 获取模板ID参数
$templateId = $_GET['id'] ?? 1;

// 使用Laravel的ORM获取模板
$template = App\Models\ProductTemplate::with('linkedProducts')->find($templateId);

if (!$template) {
    echo "Template with ID {$templateId} not found.";
    exit;
}

// 头部
echo "<!DOCTYPE html>
<html>
<head>
    <title>Template Parameters Debug</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .parameter { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .linked-product { background: #e6f7e6; padding: 10px; margin-top: 10px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Template Parameters Debug</h1>
    <p><strong>Template ID:</strong> {$template->id}</p>
    <p><strong>Template Name:</strong> {$template->name}</p>
    
    <h2>Raw Parameters Data</h2>
    <pre>" . htmlspecialchars(var_export($template->getRawOriginal('parameters'), true)) . "</pre>
    
    <h2>Processed Parameters</h2>";

$parameters = $template->parameters;
if (is_string($parameters)) {
    try {
        $parameters = json_decode($parameters, true);
    } catch (\Exception $e) {
        $parameters = [];
    }
}

if (!is_array($parameters)) {
    $parameters = [];
}

echo "<pre>" . htmlspecialchars(json_encode($parameters, JSON_PRETTY_PRINT)) . "</pre>";

echo "<h2>Linked Products</h2>";
if ($template->linkedProducts->isEmpty()) {
    echo "<p>No linked products found.</p>";
} else {
    echo "<table>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Parameter Group</th>
        </tr>";
    
    foreach ($template->linkedProducts as $product) {
        echo "<tr>
            <td>{$product->id}</td>
            <td>{$product->name}</td>
            <td>{$product->pivot->parameter_group}</td>
        </tr>";
    }
    
    echo "</table>";
}

// 分析参数链接
echo "<h2>Parameter Link Analysis</h2>";
foreach ($parameters as $param) {
    if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
        continue;
    }
    
    echo "<div class='parameter'>
        <h3>Parameter: {$param['name']}</h3>
        <p>Values: " . implode(", ", $param['values']) . "</p>";
    
    foreach ($param['values'] as $value) {
        $paramIdentifier = $param['name'] . '=' . $value;
        $linkedProduct = null;
        
        foreach ($template->linkedProducts as $product) {
            if ($product->pivot->parameter_group === $paramIdentifier) {
                $linkedProduct = $product;
                break;
            }
        }
        
        echo "<div>
            <p>Value: <strong>{$value}</strong></p>
            <p>Identifier: <strong>{$paramIdentifier}</strong></p>";
        
        if ($linkedProduct) {
            echo "<div class='linked-product'>
                <p>Linked to product: <strong>{$linkedProduct->name}</strong> (ID: {$linkedProduct->id})</p>
            </div>";
        } else {
            echo "<p>Not linked to any product</p>";
        }
        
        echo "</div>";
    }
    
    echo "</div>";
}

echo "</body></html>";
?> 
<?php

// 分析问题
echo "===== 分析CartController.php的产品ID问题 =====\n\n";
echo "问题：前端传递product_id=5，但后端保存为product_id=1\n";
echo "原因：可能是CartController.php的store方法中对product_id处理有误\n\n";

// 修复步骤
echo "===== 修复步骤 =====\n\n";

// 获取CartController.php文件内容
$controllerPath = __DIR__ . '/app/Http/Controllers/Api/CartController.php';
if (!file_exists($controllerPath)) {
    die("错误：找不到文件 $controllerPath\n");
}

$content = file_get_contents($controllerPath);
if (!$content) {
    die("错误：无法读取文件 $controllerPath\n");
}

// 创建备份
$backupPath = $controllerPath . '.bak.' . date('YmdHis');
if (!file_put_contents($backupPath, $content)) {
    die("错误：无法创建备份文件 $backupPath\n");
}

echo "1. 已创建控制器备份：" . basename($backupPath) . "\n";

// 分析代码中潜在的问题区域
echo "2. 分析代码中的问题区域...\n";

// 查找product_id修改的地方
if (preg_match('/\'product_id\'[\s]*=>[\s]*([^,\n]+)/', $content, $matches)) {
    echo "   发现product_id赋值: " . trim($matches[1]) . "\n";
    
    // 检查是否使用了正确的request参数
    if (strpos($matches[1], '$request->product_id') === false) {
        echo "   警告：product_id赋值没有直接使用request中的product_id参数\n";
    }
}

// 检查CartItem创建的代码
if (preg_match('/\$newItemData[\s]*=[\s]*\[(.*?)\];/s', $content, $matches)) {
    echo "   发现CartItem创建代码:\n";
    $lines = explode("\n", $matches[1]);
    foreach ($lines as $line) {
        if (strpos($line, 'product_id') !== false) {
            echo "   " . trim($line) . "\n";
        }
    }
}

// 修复代码
echo "3. 修改代码...\n";

// 替换Pattern: 确保正确使用request的product_id
$pattern = '/\'product_id\'\s*=>\s*\$request->product_id/';
$replacement = '\'product_id\' => (int)$request->product_id';

$newContent = preg_replace($pattern, $replacement, $content);
if ($newContent === $content) {
    echo "   没有找到需要修改的代码模式，检查是否存在其他问题...\n";
    
    // 查找$newItemData数组
    $pattern = '/(\$newItemData\s*=\s*\[\s*\'cart_id\'\s*=>\s*\$cart->id,\s*\'product_id\'\s*=>\s*)[^,]+/s';
    if (preg_match($pattern, $content)) {
        $replacement = '$1(int)$request->product_id';
        $newContent = preg_replace($pattern, $replacement, $content);
        echo "   修改了CartItem创建时的product_id赋值\n";
    } else {
        echo "   无法找到CartItem的product_id赋值代码\n";
    }
}

// 增加调试日志
$logPattern = '/file_put_contents\(\$debugLogFile, date\(\'Y-m-d H:i:s\'\) \. " - Creating new cart item\\n", FILE_APPEND\);/';
$logReplacement = 'file_put_contents($debugLogFile, date(\'Y-m-d H:i:s\') . " - Creating new cart item with product_id: " . $request->product_id . "\\n", FILE_APPEND);';

$newContent = preg_replace($logPattern, $logReplacement, $newContent);

// 记录所有影响SQL性能的地方
echo "4. 在关键位置添加详细日志...\n";

// 在SQL执行前添加日志
$sqlLogPattern = '/\$cartItem = new CartItem\(\$newItemData\);/';
$sqlLogReplacement = 'file_put_contents($debugLogFile, date(\'Y-m-d H:i:s\') . " - 准备创建CartItem，详细数据: " . json_encode($newItemData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\\n", FILE_APPEND);
        $cartItem = new CartItem($newItemData);';

$newContent = preg_replace($sqlLogPattern, $sqlLogReplacement, $newContent);

// 保存修改后的文件
if (!file_put_contents($controllerPath, $newContent)) {
    die("错误：无法保存修改后的文件 $controllerPath\n");
}

echo "5. 成功保存修改后的控制器文件\n";

// 直接修改CartController中的store方法
echo "\n===== 直接修改重点代码 =====\n\n";

// 需要手动修改的关键代码
$manualFix = '
建议手动检查CartController.php中的以下代码:

1. 在创建新CartItem的代码块中确保正确使用$request->product_id:

$newItemData = [
    \'cart_id\' => $cart->id,
    \'product_id\' => (int)$request->product_id,  // 确保使用整数类型
    \'quantity\' => $request->quantity,
    \'specifications\' => $specifications,
];

2. 检查是否有任何代码可能覆盖或修改了product_id的值

3. 检查模板查找和参数匹配的代码，确保它们不会修改原始的product_id
';

echo $manualFix;

echo "\n===== 修复完成 =====\n";
echo "请重启Laravel服务并测试购物车功能\n";
echo "如果问题仍然存在，请查看logs/cart-debug.log获取更多信息\n"; 
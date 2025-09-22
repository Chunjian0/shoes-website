<?php
// 检查购物车表结构并记录索引信息
// 执行后请删除此文件

// 设置内容类型为JSON
header('Content-Type: application/json');

// 日志文件路径
$logFile = __DIR__ . '/cart-table-check.log';

// 记录开始
file_put_contents($logFile, date('Y-m-d H:i:s') . " - 开始检查购物车表结构\n", FILE_APPEND);

try {
    // 连接数据库
    $db = new PDO(
        'mysql:host=localhost;dbname=optic_system;charset=utf8mb4', 
        'root', 
        '', 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 数据库连接成功\n", FILE_APPEND);
    
    // 检查cart_items表结构
    $tableInfoQuery = $db->query("SHOW CREATE TABLE cart_items");
    $tableInfo = $tableInfoQuery->fetch(PDO::FETCH_ASSOC);
    
    if ($tableInfo) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 购物车表结构:\n" . print_r($tableInfo, true) . "\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 无法获取购物车表结构\n", FILE_APPEND);
    }
    
    // 检查表索引
    $indexesQuery = $db->query("SHOW INDEX FROM cart_items");
    $indexes = $indexesQuery->fetchAll(PDO::FETCH_ASSOC);
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 购物车表索引:\n" . print_r($indexes, true) . "\n", FILE_APPEND);
    
    // 检查是否存在cart_product唯一索引
    $hasCartProductIndex = false;
    $cartProductIndexColumns = [];
    
    foreach ($indexes as $index) {
        if ($index['Key_name'] === 'cart_product') {
            $hasCartProductIndex = true;
            $cartProductIndexColumns[] = $index['Column_name'];
        }
    }
    
    if ($hasCartProductIndex) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 发现cart_product索引，包含列: " . implode(', ', $cartProductIndexColumns) . "\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 未发现cart_product索引\n", FILE_APPEND);
    }
    
    // 检查购物车项目数据
    $cartItemsQuery = $db->query("SELECT * FROM cart_items LIMIT 10");
    $cartItems = $cartItemsQuery->fetchAll(PDO::FETCH_ASSOC);
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 购物车项目样本:\n" . print_r($cartItems, true) . "\n", FILE_APPEND);
    
    // 问题解决方案
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 问题分析与修复方案:\n", FILE_APPEND);
    file_put_contents($logFile, "1. cart_product唯一索引限制了同一个购物车中不能有相同的产品ID，即使规格不同\n", FILE_APPEND);
    file_put_contents($logFile, "2. 修复方案: 修改索引为复合唯一索引，包含cart_id、product_id和specifications(或其哈希值)\n", FILE_APPEND);
    file_put_contents($logFile, "3. SQL修复命令: ALTER TABLE cart_items DROP INDEX cart_product, ADD UNIQUE INDEX cart_product_specs(cart_id, product_id, MD5(specifications));\n", FILE_APPEND);
    
    // 返回信息
    echo json_encode([
        'success' => true,
        'message' => '表结构检查完成，请查看日志文件',
        'has_cart_product_index' => $hasCartProductIndex,
        'cart_product_index_columns' => $cartProductIndexColumns,
        'fix_suggestion' => '修改索引为复合唯一索引，包含cart_id、product_id和specifications'
    ]);
    
} catch (PDOException $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 数据库错误: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => '数据库连接或查询错误',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 一般错误: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => '发生错误',
        'error' => $e->getMessage()
    ]);
}
?> 
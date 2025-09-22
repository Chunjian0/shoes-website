<?php
// 修复购物车表结构以允许同一产品的不同规格版本
// 执行后请删除此文件

// 设置内容类型为JSON
header('Content-Type: application/json');

// 日志文件路径
$logFile = __DIR__ . '/cart-table-fix.log';

// 记录开始
file_put_contents($logFile, date('Y-m-d H:i:s') . " - 开始修复购物车表结构\n", FILE_APPEND);

try {
    // 连接数据库
    $db = new PDO(
        'mysql:host=localhost;dbname=optic_system;charset=utf8mb4', 
        'root', 
        '', 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 数据库连接成功\n", FILE_APPEND);
    
    // 开始事务
    $db->beginTransaction();
    
    // 1. 检查是否存在cart_product唯一索引
    $indexExists = false;
    $indexStructure = [];
    
    $indexesQuery = $db->query("SHOW INDEX FROM cart_items WHERE Key_name = 'cart_product'");
    $indexes = $indexesQuery->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($indexes)) {
        $indexExists = true;
        
        foreach ($indexes as $index) {
            $indexStructure[] = [
                'Column_name' => $index['Column_name'],
                'Seq_in_index' => $index['Seq_in_index'],
                'Non_unique' => $index['Non_unique']
            ];
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 发现cart_product索引:\n" . print_r($indexStructure, true) . "\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 未发现cart_product索引\n", FILE_APPEND);
    }
    
    // 2. 备份表结构（可选）
    $createTableQuery = $db->query("SHOW CREATE TABLE cart_items");
    $createTable = $createTableQuery->fetch(PDO::FETCH_ASSOC);
    
    if ($createTable) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 原始表结构:\n" . print_r($createTable, true) . "\n", FILE_APPEND);
    }
    
    // 3. 如果存在原索引，删除它
    if ($indexExists) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 正在删除原有的cart_product索引...\n", FILE_APPEND);
        
        $db->exec("ALTER TABLE cart_items DROP INDEX cart_product");
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 原有索引已删除\n", FILE_APPEND);
    }
    
    // 4. 创建新的复合唯一索引，考虑specifications
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 创建新的复合索引...\n", FILE_APPEND);
    
    // 根据specifications列的类型选择适当的索引方法
    // 方法1：如果specifications是JSON类型，使用JSON字段+MD5
    // 方法2：如果specifications是TEXT/VARCHAR类型，直接使用MD5
    
    // 首先检查列类型
    $columnsQuery = $db->query("SHOW COLUMNS FROM cart_items LIKE 'specifications'");
    $specColumn = $columnsQuery->fetch(PDO::FETCH_ASSOC);
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - specifications列类型: " . $specColumn['Type'] . "\n", FILE_APPEND);
    
    // 根据类型选择索引创建方式
    if (stripos($specColumn['Type'], 'json') !== false) {
        // JSON类型
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 使用JSON类型的索引创建方式\n", FILE_APPEND);
        
        // MySQL 5.7+支持JSON函数
        $db->exec("ALTER TABLE cart_items ADD UNIQUE INDEX cart_product_specs(cart_id, product_id, MD5(specifications))");
    } else {
        // TEXT/VARCHAR类型
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 使用TEXT/VARCHAR类型的索引创建方式\n", FILE_APPEND);
        
        $db->exec("ALTER TABLE cart_items ADD UNIQUE INDEX cart_product_specs(cart_id, product_id, MD5(specifications))");
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 新索引已创建\n", FILE_APPEND);
    
    // 检查新的表结构
    $newCreateTableQuery = $db->query("SHOW CREATE TABLE cart_items");
    $newCreateTable = $newCreateTableQuery->fetch(PDO::FETCH_ASSOC);
    
    if ($newCreateTable) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - 更新后的表结构:\n" . print_r($newCreateTable, true) . "\n", FILE_APPEND);
    }
    
    // 提交事务
    $db->commit();
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 购物车表结构修复完成\n", FILE_APPEND);
    
    // 返回成功信息
    echo json_encode([
        'success' => true,
        'message' => '购物车表结构已修复，现在可以添加同一产品的不同规格版本',
        'original_index_existed' => $indexExists,
        'new_index_created' => true
    ]);
    
} catch (PDOException $e) {
    // 回滚事务
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 数据库错误: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - SQL状态: " . $e->getCode() . "\n", FILE_APPEND);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 堆栈跟踪:\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => '修复过程中发生数据库错误',
        'error' => $e->getMessage(),
        'sql_state' => $e->getCode()
    ]);
} catch (Exception $e) {
    // 回滚事务
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 一般错误: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - 堆栈跟踪:\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => '修复过程中发生错误',
        'error' => $e->getMessage()
    ]);
}

// 添加使用说明
file_put_contents($logFile, "\n===== 使用说明 =====\n", FILE_APPEND);
file_put_contents($logFile, "1. 此修复允许购物车添加同一产品的不同规格版本\n", FILE_APPEND);
file_put_contents($logFile, "2. 修复后，唯一约束将基于cart_id + product_id + specifications的哈希值\n", FILE_APPEND);
file_put_contents($logFile, "3. 如果使用Laravel迁移，建议通过迁移文件来永久修复此问题:\n", FILE_APPEND);
file_put_contents($logFile, "   a. 创建新的迁移: php artisan make:migration fix_cart_items_unique_constraint\n", FILE_APPEND);
file_put_contents($logFile, "   b. 在up()方法中实现索引修改\n", FILE_APPEND);
file_put_contents($logFile, "   c. 在down()方法中恢复原始索引\n", FILE_APPEND);
?> 
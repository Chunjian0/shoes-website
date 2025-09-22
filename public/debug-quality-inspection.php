<?php
/**
 * 质量检验提交调试脚本
 * 
 * 此文件用于记录质量检验表单提交过程中的请求信息，以便排查问题
 * 访问 http://localhost:2268/debug-quality-inspection.php 查看日志
 */

// 设置响应头为JSON
header('Content-Type: application/json');

// 定义日志文件路径
$logFile = __DIR__ . '/../storage/logs/quality-inspection-debug.log';

// 确保日志目录存在
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

// 如果是清除日志请求
if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
    file_put_contents($logFile, '');
    echo json_encode(['status' => 'success', 'message' => 'Debug log cleared']);
    exit;
}

// 如果是请求查看日志
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        
        // 提供一个清除日志的链接和格式化输出
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Quality Inspection Debug Log</title>
            <meta charset="utf-8">
            <style>
                body { font-family: monospace; padding: 20px; max-width: 1200px; margin: 0 auto; }
                h1 { color: #333; }
                pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
                .actions { margin-bottom: 20px; }
                .actions a { display: inline-block; margin-right: 10px; padding: 8px 15px; background: #4f46e5; color: white; 
                           text-decoration: none; border-radius: 5px; }
                .actions a:hover { background: #4338ca; }
                .actions a.clear { background: #ef4444; }
                .actions a.clear:hover { background: #dc2626; }
            </style>
        </head>
        <body>
            <h1>Quality Inspection Debug Log</h1>
            <div class="actions">
                <a href="?refresh=true">Refresh Log</a>
                <a href="?clear=true" class="clear" onclick="return confirm(\'Are you sure you want to clear the log?\')">Clear Log</a>
            </div>
            <pre>' . htmlspecialchars($logs) . '</pre>
        </body>
        </html>';
    } else {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Quality Inspection Debug Log</title>
            <meta charset="utf-8">
            <style>
                body { font-family: monospace; padding: 20px; }
                h1 { color: #333; }
            </style>
        </head>
        <body>
            <h1>No debug logs found</h1>
            <p>There are no quality inspection debug logs available.</p>
        </body>
        </html>';
    }
    exit;
}

// 记录POST请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = date('Y-m-d H:i:s');
    $requestData = file_get_contents('php://input');
    $filesData = !empty($_FILES) ? print_r($_FILES, true) : 'No files';
    
    $debug = <<<EOL
======== NEW REQUEST: {$now} ========
REQUEST URI: {$_SERVER['REQUEST_URI']}
REQUEST METHOD: {$_SERVER['REQUEST_METHOD']}
CONTENT TYPE: {$_SERVER['CONTENT_TYPE']}
POST DATA: 
EOL;
    
    $debug .= print_r($_POST, true);
    
    $debug .= <<<EOL
RAW REQUEST BODY:
{$requestData}

FILES:
{$filesData}

SERVER:
EOL;

    // 添加一些可能有用的服务器变量
    $serverVars = [
        'HTTP_USER_AGENT',
        'HTTP_REFERER',
        'REMOTE_ADDR',
        'HTTP_X_CSRF_TOKEN',
        'CONTENT_LENGTH'
    ];

    foreach ($serverVars as $var) {
        if (isset($_SERVER[$var])) {
            $debug .= "{$var}: {$_SERVER[$var]}\n";
        }
    }

    $debug .= "\n========================================\n\n";

    // 追加到日志文件
    file_put_contents($logFile, $debug, FILE_APPEND);

    // 返回成功响应
    echo json_encode(['status' => 'debug_logged']);
    exit;
}

// 其他请求方法
echo json_encode(['status' => 'error', 'message' => 'Unsupported request method']); 
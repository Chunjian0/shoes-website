<?php
/**
 * API测试工具 - 浏览器版本
 */

// 设置错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 获取测试参数
$endpoint = $_GET['endpoint'] ?? '';
$method = $_GET['method'] ?? 'GET';
$params = $_GET['params'] ?? '';

// API基础URL
$baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$baseUrl .= $_SERVER['HTTP_HOST'] . '/api';

// 预设API端点
$presetEndpoints = [
    // 产品模板API
    'product-templates' => [
        'name' => '获取产品模板列表',
        'method' => 'GET',
        'endpoint' => '/product-templates',
        'params' => 'page=1&per_page=5'
    ],
    'product-template-detail' => [
        'name' => '获取单个产品模板',
        'method' => 'GET',
        'endpoint' => '/product-templates/1',
        'params' => ''
    ],
    'product-template-products' => [
        'name' => '获取模板关联产品',
        'method' => 'GET',
        'endpoint' => '/product-templates/1/products',
        'params' => 'page=1&per_page=5'
    ],
    
    // 首页API
    'homepage-data' => [
        'name' => '获取首页全部数据',
        'method' => 'GET',
        'endpoint' => '/homepage/data',
        'params' => ''
    ],
    'homepage-featured' => [
        'name' => '获取特色产品模板',
        'method' => 'GET',
        'endpoint' => '/homepage/featured-templates',
        'params' => 'page=1&per_page=5'
    ],
    'homepage-new-arrivals' => [
        'name' => '获取新品产品模板',
        'method' => 'GET',
        'endpoint' => '/homepage/new-arrival-templates',
        'params' => 'page=1&per_page=5'
    ],
    'homepage-sale' => [
        'name' => '获取促销产品模板',
        'method' => 'GET',
        'endpoint' => '/homepage/sale-templates',
        'params' => 'page=1&per_page=5'
    ],
    'homepage-banners' => [
        'name' => '获取首页轮播图',
        'method' => 'GET',
        'endpoint' => '/homepage/banners',
        'params' => ''
    ],
    'homepage-settings' => [
        'name' => '获取首页设置',
        'method' => 'GET',
        'endpoint' => '/homepage/settings',
        'params' => ''
    ]
];

// 预设ID
$preset = $_GET['preset'] ?? '';
$testResult = null;

if ($preset && isset($presetEndpoints[$preset])) {
    $selectedPreset = $presetEndpoints[$preset];
    $endpoint = $selectedPreset['endpoint'];
    $method = $selectedPreset['method'];
    $params = $selectedPreset['params'];
}

// 执行API请求
if (!empty($endpoint)) {
    $url = $baseUrl . $endpoint;
    if ($method === 'GET' && !empty($params)) {
        $url .= '?' . $params;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    $testResult = [
        'url' => $url,
        'method' => $method,
        'params' => $params,
        'status' => $info['http_code'],
        'response' => $response,
        'error' => $error,
        'time' => $info['total_time']
    ];
}

// HTML输出
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API测试工具</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            max-height: 500px;
            overflow: auto;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">API测试工具</h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">预设API端点</div>
                    <div class="card-body p-0">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <?php foreach ($presetEndpoints as $key => $presetInfo): ?>
                                <a class="nav-link <?php echo $preset === $key ? 'active' : ''; ?>"
                                   href="?preset=<?php echo $key; ?>">
                                    <?php echo htmlspecialchars($presetInfo['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card mb-4">
                    <div class="card-header">测试API端点</div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="mb-3">
                                <label for="endpoint" class="form-label">API端点</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo htmlspecialchars($baseUrl); ?></span>
                                    <input type="text" class="form-control" id="endpoint" name="endpoint" value="<?php echo htmlspecialchars($endpoint); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="method" class="form-label">请求方法</label>
                                <select class="form-select" id="method" name="method">
                                    <option value="GET" <?php echo $method === 'GET' ? 'selected' : ''; ?>>GET</option>
                                    <option value="POST" <?php echo $method === 'POST' ? 'selected' : ''; ?>>POST</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="params" class="form-label">参数 (GET: query string, POST: form data)</label>
                                <input type="text" class="form-control" id="params" name="params" value="<?php echo htmlspecialchars($params); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">发送请求</button>
                        </form>
                    </div>
                </div>
                
                <?php if ($testResult): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        测试结果
                        <span class="badge bg-<?php echo $testResult['status'] >= 200 && $testResult['status'] < 300 ? 'success' : 'danger'; ?> float-end">
                            状态码: <?php echo $testResult['status']; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>请求URL:</strong> <?php echo htmlspecialchars($testResult['url']); ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>请求方法:</strong> <?php echo htmlspecialchars($testResult['method']); ?>
                        </div>
                        
                        <?php if ($testResult['params']): ?>
                        <div class="mb-3">
                            <strong>参数:</strong> <?php echo htmlspecialchars($testResult['params']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>响应时间:</strong> <?php echo round($testResult['time'] * 1000, 2); ?> ms
                        </div>
                        
                        <?php if ($testResult['error']): ?>
                        <div class="alert alert-danger">
                            <strong>错误:</strong> <?php echo htmlspecialchars($testResult['error']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>响应内容:</strong>
                            <?php
                                $response = $testResult['response'];
                                $isJson = false;
                                
                                // 尝试格式化JSON
                                if ($response) {
                                    $decoded = json_decode($response);
                                    if ($decoded !== null) {
                                        $response = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                        $isJson = true;
                                    }
                                }
                            ?>
                            <pre><?php echo htmlspecialchars($response); ?></pre>
                        </div>
                        
                        <?php if ($isJson): ?>
                        <div class="mb-3">
                            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="copyToClipboard()">复制JSON</button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>是否符合API文档格式:</strong>
                            <?php
                                $conformsToSpec = false;
                                if ($isJson) {
                                    $data = json_decode($testResult['response'], true);
                                    if (isset($data['success']) && isset($data['data'])) {
                                        $conformsToSpec = true;
                                    }
                                }
                            ?>
                            <span class="badge bg-<?php echo $conformsToSpec ? 'success' : 'warning'; ?>">
                                <?php echo $conformsToSpec ? '符合规范' : '不完全符合规范'; ?>
                            </span>
                            
                            <?php if (!$conformsToSpec): ?>
                            <div class="alert alert-warning mt-2">
                                <small>
                                    响应格式不完全符合API文档规范。标准格式应该包含 "success" 和 "data" 字段。
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard() {
            const preElement = document.querySelector('pre');
            const textToCopy = preElement.textContent;
            
            navigator.clipboard.writeText(textToCopy)
                .then(() => {
                    alert('已复制到剪贴板');
                })
                .catch(err => {
                    console.error('无法复制文本: ', err);
                    alert('复制失败，请手动选择并复制');
                });
        }
    </script>
</body>
</html> 
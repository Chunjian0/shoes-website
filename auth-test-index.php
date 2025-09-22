<?php
/**
 * 认证功能测试入口文件
 */

// 显示PHP命令行测试链接
$phpTestUrl = 'api-test.php';

// HTML测试页面链接
$htmlTestUrl = 'auth-test.html';

// 项目根目录URL（根据实际环境配置）
$baseUrl = '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>认证功能测试工具</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #444;
            margin-top: 0;
        }
        p {
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-secondary {
            background-color: #2196F3;
        }
        .btn-secondary:hover {
            background-color: #0b7dda;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>认证功能测试工具</h1>
    
    <div class="card">
        <h2>关于测试工具</h2>
        <p>本工具用于测试Laravel后端的认证功能，包括：</p>
        <ul>
            <li>用户注册</li>
            <li>用户登录</li>
            <li>用户资料管理</li>
            <li>修改密码</li>
            <li>会话管理</li>
        </ul>
        <p>您可以选择使用以下两种测试方式：</p>
    </div>
    
    <div class="card">
        <h2>1. PHP命令行测试</h2>
        <p>运行PHP脚本测试API功能，将自动执行所有测试步骤并输出结果。</p>
        <div class="warning">
            <strong>注意：</strong> 此测试将创建测试用户并修改其密码，仅用于开发环境。
        </div>
        <a href="<?php echo $phpTestUrl; ?>" class="btn" target="_blank">运行PHP测试</a>
    </div>
    
    <div class="card">
        <h2>2. 交互式HTML测试</h2>
        <p>通过浏览器界面手动测试各项功能，可以直观查看API请求和响应数据。</p>
        <a href="<?php echo $htmlTestUrl; ?>" class="btn btn-secondary" target="_blank">运行HTML测试</a>
    </div>
    
    <div class="card">
        <h2>测试结果说明</h2>
        <p>测试过程中将会显示每个API请求的状态码和响应内容。</p>
        <p>如果遇到错误，请检查：</p>
        <ol>
            <li>API URL配置是否正确</li>
            <li>服务器是否正常运行</li>
            <li>数据库连接是否正常</li>
            <li>API路由是否正确配置</li>
            <li>控制器方法是否正确实现</li>
        </ol>
    </div>
</body>
</html> 
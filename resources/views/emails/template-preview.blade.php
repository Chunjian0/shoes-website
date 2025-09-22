<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4f46e5;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .content {
            padding: 24px;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 16px 24px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        a {
            color: #4f46e5;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            margin: 16px 0;
        }
        .button:hover {
            background-color: #4338ca;
            text-decoration: none;
        }
        p {
            margin-bottom: 16px;
        }
        ul {
            margin-bottom: 16px;
        }
        li {
            margin-bottom: 8px;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 24px 0;
        }
        .preview-info {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-info">
            这是邮件模板预览。实际发送的邮件可能会有细微差别，具体取决于邮件客户端的呈现方式。
        </div>
        
        <div class="header">
            <h1>{{ $subject }}</h1>
        </div>
        
        <div class="content">
            {!! $content !!}
        </div>
        
        <div class="footer">
            <p>
                &copy; {{ date('Y') }} {{ config('app.name') }}. 保留所有权利。<br>
                如果您有任何问题，请联系我们的客服团队。
            </p>
        </div>
    </div>
</body>
</html> 
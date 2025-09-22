<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        .content {
            padding: 15px;
            background-color: #ffffff;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Test Email Notification</h2>
        <p>Type: {{ $type }}</p>
    </div>
    
    <div class="content">
        {!! $content !!}
    </div>
    
    <div class="footer">
        <p>This is a test email from the Optic System. Please do not reply to this message.</p>
        <p>© {{ date('Y') }} Optic System</p>
    </div>
</body>
</html> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 0;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #4a6cf7;
            padding: 25px 40px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 40px;
        }
        .content p {
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #4a6cf7;
            text-align: center;
            letter-spacing: 6px;
            background-color: #f5f7fa;
            padding: 20px;
            border-radius: 5px;
            margin: 30px 0;
            border: 1px dashed #cccccc;
        }
        .footer {
            border-top: 1px solid #e0e0e0;
            padding: 20px 40px;
            font-size: 12px;
            color: #888888;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Email Verification Code</h1>
        </div>
        
        <div class="content">
            <p>Dear User,</p>
            
            <p>Your verification code is:</p>
            
            <div class="code">{{ $code }}</div>
            
            <p>This code is valid for 30 minutes. Please complete the verification process soon.</p>
            
            <p>If you did not request this code, please ignore this email.</p>
        </div>
            
        <div class="footer">
            <p>This is an automated message, please do not reply directly.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 
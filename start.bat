@echo off
echo Starting Laravel Development Environment...

:: 启动 npm run dev (在新窗口中运行)
start cmd /k "npm run dev"

:: 等待 2 秒确保 npm 启动
timeout /t 2 /nobreak > nul

:: 启动 Laravel 服务器 (在新窗口中运行)
start cmd /k "php artisan serve --port=2268"

:: 等待 2 秒确保服务器启动
timeout /t 2 /nobreak > nul

:: 打开浏览器
start http://localhost:2268

:: 启动计划任务监听器 (在新窗口中运行)
start cmd /k "php artisan schedule:work"

echo Laravel Development Environment is ready!
echo You can close this window now.

:: 保持窗口打开
pause 
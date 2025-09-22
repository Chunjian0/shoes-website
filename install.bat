@echo off
chcp 65001
setlocal enabledelayedexpansion

echo.
echo ================================================
echo            眼镜店管理系统安装程序
echo ================================================
echo.

:: 检查是否以管理员身份运行
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [错误] 请以管理员身份运行此脚本！
    echo 请右键点击此脚本，选择"以管理员身份运行"
    echo.
    pause
    exit /b 1
)

:: 设置工作目录
cd /d "%~dp0"

:: 检查并安装环境
echo.
echo [询问] 是否需要安装系统环境？
echo  - PHP 8.2
echo  - MySQL 8.0
echo  - Composer
echo  - Node.js 和 npm
echo.
echo 请输入 Y 或 N: 
set /p install_env=
if /i "!install_env!"=="Y" (
    call setup_environment.bat
)

:: 检查环境
echo.
echo [进行] 正在检查系统环境...
php check_environment.php
if %errorLevel% neq 0 (
    echo.
    echo [错误] 环境检查失败，请确保满足所有要求后重试.
    pause
    exit /b 1
)

:: 配置数据库
if not exist ".env" (
    echo.
    echo [进行] 正在配置数据库连接...
    copy ".env.example" ".env"
    powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=', 'DB_PASSWORD=password' | Set-Content .env"
)

:: 安装依赖
echo.
echo [进行] 正在安装 Composer 依赖...
call composer install --no-interaction

echo.
echo [进行] 正在安装 NPM 依赖...
call npm install

:: 编译前端资源
echo.
echo [进行] 正在编译前端资源...
call npm run build

:: 生成应用密钥
echo.
echo [进行] 正在生成应用密钥...
php artisan key:generate

:: 运行数据库迁移
echo.
echo [询问] 是否需要运行数据库迁移？[Y/N]
set /p run_migration=
if /i "!run_migration!"=="Y" (
    php artisan migrate
)

:: 设置文件权限
echo.
echo [进行] 正在设置文件权限...
icacls "storage" /grant "Users":(OI)(CI)F /T
icacls "bootstrap/cache" /grant "Users":(OI)(CI)F /T

:: 设置自动备份
echo.
echo [询问] 是否设置自动数据库备份？[Y/N]
set /p setup_backup=
if /i "!setup_backup!"=="Y" (
    call setup_scheduler.bat
)

:: 启动服务器
echo.
echo [进行] 正在启动服务器...
start "Laravel Server" cmd /c "php artisan serve --port=2268"

echo.
echo ================================================
echo                  安装完成！
echo ================================================
echo.
echo 系统已在 http://localhost:2268 启动
echo 请使用浏览器访问上述地址
echo.
echo 提示：
echo  - 默认数据库密码：password
echo  - 自动备份时间：每天 23:00
echo  - 备份保存位置：storage/app/backups
echo.

pause 
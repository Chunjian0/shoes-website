@echo off
chcp 65001
setlocal enabledelayedexpansion

echo.
echo ================================================
echo              系统环境安装程序
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

:: 创建临时目录
set "TEMP_DIR=%~dp0temp"
if not exist "%TEMP_DIR%" mkdir "%TEMP_DIR%"

:: 检查并安装 Chocolatey
where choco >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo [进行] 正在安装 Chocolatey 包管理器...
    @powershell -NoProfile -ExecutionPolicy Bypass -Command "iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))"
    :: 刷新环境变量
    call refreshenv
)

:: 安装 PHP 8.2
echo.
echo [进行] 正在安装 PHP 8.2...
choco install php --version=8.2.12 -y
call refreshenv

:: 安装 MySQL
echo.
echo [进行] 正在安装 MySQL 8.0...
choco install mysql --version=8.0.35 -y
call refreshenv

:: 安装 Composer
echo.
echo [进行] 正在安装 Composer...
choco install composer -y
call refreshenv

:: 安装 Node.js 和 npm
echo.
echo [进行] 正在安装 Node.js 和 npm...
choco install nodejs-lts -y
call refreshenv

:: 配置 PHP
echo.
echo [进行] 正在配置 PHP...
cd /d "C:\tools\php82"
copy php.ini-development php.ini
powershell -Command "(Get-Content php.ini) -replace ';extension=fileinfo', 'extension=fileinfo' | Set-Content php.ini"
powershell -Command "(Get-Content php.ini) -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Set-Content php.ini"
powershell -Command "(Get-Content php.ini) -replace ';extension=openssl', 'extension=openssl' | Set-Content php.ini"
powershell -Command "(Get-Content php.ini) -replace ';extension=mbstring', 'extension=mbstring' | Set-Content php.ini"
powershell -Command "(Get-Content php.ini) -replace ';extension=gd', 'extension=gd' | Set-Content php.ini"

:: 启动 MySQL 服务
echo.
echo [进行] 正在启动 MySQL 服务...
net start MySQL

:: 配置 MySQL root 密码
echo.
echo [进行] 正在配置 MySQL...
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"

:: 清理临时文件
cd /d "%~dp0"
if exist "%TEMP_DIR%" rd /s /q "%TEMP_DIR%"

echo.
echo ================================================
echo                环境安装完成！
echo ================================================
echo.
echo [信息] 已安装的组件版本：
echo.
echo PHP 版本：
php -v
echo.
echo MySQL 版本：
mysql --version
echo.
echo Composer 版本：
composer --version
echo.
echo Node.js 版本：
node --version
echo.
echo NPM 版本：
npm --version
echo.

pause 
@echo off
echo 正在设置数据库自动备份计划任务...

:: 获取当前目录的绝对路径
set "SCRIPT_PATH=%~dp0"

:: 创建计划任务
SCHTASKS /CREATE /SC DAILY /TN "OpticSystem\DatabaseBackup" /TR "php \"%SCRIPT_PATH%backup.php\"" /ST 23:00 /F

IF %ERRORLEVEL% EQU 0 (
    echo 计划任务创建成功！数据库将在每天晚上 23:00 自动备份.
) ELSE (
    echo 计划任务创建失败，请以管理员身份运行此脚本.
)

pause 
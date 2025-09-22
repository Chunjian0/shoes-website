# 创建临时目录
$downloadPath = "C:\temp"
if (-not (Test-Path -Path $downloadPath)) {
    New-Item -Path $downloadPath -ItemType Directory | Out-Null
}

# 设置下载URL（Node.js官方LTS版本）
$url = "https://nodejs.org/dist/v20.12.2/node-v20.12.2-x64.msi"
$outputFile = "$downloadPath\node-latest.msi"

# 下载文件
Write-Host "正在下载Node.js最新版本..." -ForegroundColor Green
Invoke-WebRequest -Uri $url -OutFile $outputFile

# 显示下载完成信息
if (Test-Path $outputFile) {
    Write-Host "下载完成！" -ForegroundColor Green
    Write-Host "安装文件保存在: $outputFile" -ForegroundColor Green
    Write-Host "请运行这个文件安装Node.js，安装时请确保选中'Add to PATH'选项" -ForegroundColor Yellow
} else {
    Write-Host "下载失败。请手动访问 https://nodejs.org/ 下载" -ForegroundColor Red
} 
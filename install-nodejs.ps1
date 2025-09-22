# Create temporary directory
$tempDir = "C:\temp"
if (-not (Test-Path -Path $tempDir)) {
    New-Item -Path $tempDir -ItemType Directory | Out-Null
}

# Download and install latest Node.js LTS version using winget
Write-Host "Installing Node.js using winget..." -ForegroundColor Green
try {
    # Check if winget is available
    $wingetCheck = winget -v
    
    # If winget exists, use it to install Node.js
    Write-Host "Using winget to install Node.js..." -ForegroundColor Green
    winget install -e --id OpenJS.NodeJS.LTS --accept-source-agreements --accept-package-agreements
    
    Write-Host "Node.js installation completed!" -ForegroundColor Green
    Write-Host "Please restart your terminal to use Node.js and npm." -ForegroundColor Yellow
}
catch {
    # Fallback to manual download if winget is not available
    Write-Host "Winget not available. Downloading Node.js installer manually..." -ForegroundColor Yellow
    
    $url = "https://nodejs.org/dist/v20.12.2/node-v20.12.2-x64.msi"
    $outputFile = "$tempDir\node-latest.msi"
    
    Invoke-WebRequest -Uri $url -OutFile $outputFile
    
    if (Test-Path $outputFile) {
        Write-Host "Download complete. Starting installation..." -ForegroundColor Green
        Start-Process -FilePath $outputFile -ArgumentList "/quiet", "InstallAllUsers=1", "AddToPath=1" -Wait
        Write-Host "Node.js installation completed!" -ForegroundColor Green
        Write-Host "Please restart your terminal to use Node.js and npm." -ForegroundColor Yellow
    } else {
        Write-Host "Download failed. Please visit https://nodejs.org/ manually" -ForegroundColor Red
    }
} 
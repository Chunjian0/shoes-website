# Test Node.js installation
Write-Host "Testing Node.js installation..." -ForegroundColor Green

# Create a new PowerShell process to ensure PATH is refreshed
$nodejsTest = Start-Process powershell -ArgumentList "-Command `"node -v; npm -v`"" -Wait -NoNewWindow -PassThru -RedirectStandardOutput "$env:TEMP\nodejs-test.txt"

# Read the output
$testOutput = Get-Content "$env:TEMP\nodejs-test.txt" -ErrorAction SilentlyContinue

if ($testOutput -and $testOutput.Count -ge 1) {
    Write-Host "Node.js installation verified!" -ForegroundColor Green
    Write-Host "Node.js version: $($testOutput[0])" -ForegroundColor Cyan
    if ($testOutput.Count -ge 2) {
        Write-Host "npm version: $($testOutput[1])" -ForegroundColor Cyan
    }
    
    Write-Host "`nYou can now run 'npm run dev' in your project directory." -ForegroundColor Green
    Write-Host "If you still see errors, please restart your computer to ensure PATH updates are applied." -ForegroundColor Yellow
} else {
    Write-Host "Node.js installation could not be verified." -ForegroundColor Red
    Write-Host "Please restart your computer and try again." -ForegroundColor Yellow
} 
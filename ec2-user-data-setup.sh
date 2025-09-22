#!/bin/bash
set -e # Exit immediately if a command exits with a non-zero status.

# --- Update System ---
echo "Updating System using dnf..."
sudo dnf update -y
echo "System updated."

# --- Install Git ---
echo "Installing Git..."
sudo dnf install -y git
echo "Git installed. Version: $(git --version)"

# --- Install PHP 8.1 and Extensions ---
echo "Installing PHP 8.1 and required tools (AL2023)..."
# Install PHP core, extensions, and build tools needed for pecl
sudo dnf install -y php php-cli php-mysqlnd php-gd php-xml php-mbstring php-zip php-json php-fpm php-pear php-devel gcc php-opcache php-bcmath php-pdo php-tokenizer php-curl
# Verify PHP version
echo "PHP installed. Version: $(php --version)"

# --- Install Redis PHP Extension using pecl ---
echo "Installing Redis PHP extension via pecl..."
# Use '-D' flags to explicitly disable optional features instead of relying on '-n'
sudo pecl install redis -D enable-redis-igbinary=no -D enable-redis-lzf=no -D enable-redis-zstd=no -D enable-redis-msgpack=no -D enable-redis-lz4=no
if [ $? -ne 0 ]; then
    echo "错误: pecl install redis 失败。"
    exit 1
fi
echo "Redis extension downloaded and compiled."

# --- Enable Redis Extension ---
echo "Enabling Redis extension..."
# Find the directory where PHP scans for additional .ini files reliably
# Use awk for better reliability across different output formats
PHP_INI_SCAN_DIR=$(php --ini | awk -F '[:=]>' '/Scan for additional .ini files/ {gsub(/^[ \t]+|[ \t]+$/, "", $2); print $2}')

if [ -z "$PHP_INI_SCAN_DIR" ]; then
    # Fallback for AL2023/RHEL based systems if detection fails
    PHP_INI_SCAN_DIR="/etc/php.d"
    echo "无法自动检测 .ini 扫描目录, 假设为: $PHP_INI_SCAN_DIR"
fi

# Ensure the scan directory exists
sudo mkdir -p "$PHP_INI_SCAN_DIR"

# Define the correct ini file path
REDIS_INI_FILE="${PHP_INI_SCAN_DIR}/50-redis.ini" # Using a number prefix helps control loading order if needed

# Check if the *incorrectly named* file might exist from previous attempt and remove it
# Construct the potential incorrect path pattern based on the previous error output
INCORRECT_PATH_PREFIX="Scan for additional .ini files in: "
INCORRECT_FILE_PATH="${INCORRECT_PATH_PREFIX}${REDIS_INI_FILE}."
# Need to be careful removing files, maybe check first
if [ -f "$INCORRECT_FILE_PATH" ]; then
    echo "发现之前错误创建的 .ini 文件，正在删除: $INCORRECT_FILE_PATH"
    # sudo rm -f "$INCORRECT_FILE_PATH" # Temporarily commenting out direct rm for safety, user can do manually if needed
    echo "请手动检查并删除以下错误文件（如果存在）: $INCORRECT_FILE_PATH"
fi
# Also check for simpler variations if sed failed differently
SIMPLE_INCORRECT_FILE="${PHP_INI_SCAN_DIR}50-redis.ini"
if [[ "$SIMPLE_INCORRECT_FILE" != "$REDIS_INI_FILE" && -f "$SIMPLE_INCORRECT_FILE" ]]; then
    echo "发现另一个可能的错误 .ini 文件，正在删除: $SIMPLE_INCORRECT_FILE"
    # sudo rm -f "$SIMPLE_INCORRECT_FILE"
    echo "请手动检查并删除以下错误文件（如果存在）: $SIMPLE_INCORRECT_FILE"
fi


# Create the redis ini file if it doesn't exist at the correct path
if [ ! -f "$REDIS_INI_FILE" ]; then
  echo "extension=redis.so" | sudo tee "$REDIS_INI_FILE"
  echo "Redis extension enabled via $REDIS_INI_FILE."
else
  echo "Redis .ini file ($REDIS_INI_FILE) already exists."
fi

# Verify if PHP CLI now loads the redis module
echo "Verifying if redis module is loaded by PHP CLI..."
if php -m | grep -iq redis; then
    echo "成功: PHP CLI 已加载 redis 模块。"
else
    echo "错误: PHP CLI 未能加载 redis 模块。请检查 $REDIS_INI_FILE 文件内容和 PHP 配置。"
    # Optional: exit 1 here if loading is critical for the script
fi

# --- Configure PHP-FPM to start on boot (if using FPM) ---
# echo "Enabling PHP-FPM service..."
# sudo systemctl enable php-fpm

# --- Install Composer 2.8.2 ---
echo "Installing Composer v2.8.2..."

# Download the specific 2.8.2 phar file directly using sudo
echo "Downloading Composer phar..."
sudo curl -o /usr/local/bin/composer https://getcomposer.org/download/2.8.2/composer.phar
if [ $? -ne 0 ]; then
    echo "错误: Failed to download Composer phar using curl."
    exit 1
fi

# Make the downloaded file executable
echo "Making Composer executable..."
sudo chmod +x /usr/local/bin/composer
if [ $? -ne 0 ]; then
    echo "错误: Failed to set execute permission on /usr/local/bin/composer."
    exit 1
fi

# Basic verification - Now it should have permission to execute
echo "Verifying Composer installation..."
if ! /usr/local/bin/composer --version > /dev/null; then
   echo "错误: Composer verification command failed. The download might be corrupt or permissions incorrect."
   # You might want to check the file size or content here for further debugging
   ls -l /usr/local/bin/composer # Print file details for debugging
   exit 1
fi

echo "Composer installed. Version: $(/usr/local/bin/composer --version)"

# --- Install Node.js v22 and npm ---
echo "Installing Node.js v22 and npm (AL2023)..."
# Use NodeSource setup script for Node.js 22.x (should work on RPM-based like AL2023)
# Download and execute the NodeSource setup script
curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo bash -
# Install Node.js using dnf
sudo dnf install -y nodejs
# Verify versions
echo "Node.js installed. Version: $(node --version)"
echo "npm installed. Version: $(npm --version)"

echo "--- Installation Complete (Amazon Linux 2023) ---"
# Remember to configure services (httpd/nginx, php-fpm) and clean the instance before creating the AMI.
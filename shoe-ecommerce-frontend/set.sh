#!/bin/bash
export LARAVEL_ROOT="/home/ec2-user/https---github.com-imethankhoooo-optic-system"
if [ -d "$LARAVEL_ROOT" ] && [ "$(ls -A $LARAVEL_ROOT)" ]; then
  echo "目录 $LARAVEL_ROOT 已存在且非空，跳过克隆。"
else
  git clone https://github.com/imethankhoooo/https---github.com-imethankhoooo-optic-system.git "$LARAVEL_ROOT"
  if [ $? -ne 0 ]; then
      echo "错误: git clone 失败。"
      exit 1 # 克隆失败则退出
  fi
  echo "代码克隆成功。"
fi

cd "$LARAVEL_ROOT"
if [ $? -ne 0 ]; then
    echo "错误: 无法 cd 到 $LARAVEL_ROOT。"
    exit 1 
fi
echo "当前工作目录: $(pwd)"

ENV_URL="https://ass-file-s3.s3.us-east-1.amazonaws.com/ec2.env"

curl -f -s -o "$LARAVEL_ROOT/.env" "$ENV_URL"

if [ $? -ne 0 ]; then
    exit 1 
fi

sudo chown ec2-user:apache "$LARAVEL_ROOT/.env"
sudo chmod 664 "$LARAVEL_ROOT/.env"

FRONTEND_ENV_FILE="$LARAVEL_ROOT/shoe-ecommerce-frontend/.env"
FRONTEND_ENV_VAR="VITE_API_BASE_URL=assignment-lb-1469644647.us-east-1.elb.amazonaws.com"
echo "正在设置 ${FRONTEND_ENV_FILE} 中的 VITE_API_BASE_URL..."
mkdir -p "$(dirname "$FRONTEND_ENV_FILE")"
touch "$FRONTEND_ENV_FILE"
sed -i '\#^VITE_API_BASE_URL=#d' "$FRONTEND_ENV_FILE"
echo "$FRONTEND_ENV_VAR" >> "$FRONTEND_ENV_FILE"
echo "VITE_API_BASE_URL 设置完成。"
sudo chown ec2-user:apache "$FRONTEND_ENV_FILE"
sudo chmod 664 "$FRONTEND_ENV_FILE"

echo "正在安装 Composer 依赖..."

export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_HOME="/tmp/composer_home"

composer install --no-dev --optimize-autoloader --no-interaction

if [ $? -ne 0 ]; then
    echo "错误: composer install 失败。"
    exit 1
fi
echo "Composer 依赖安装成功。"

# --- BEGIN: Pre-Composer Permission Setup ---
echo "确保缓存和存储目录存在并对 ec2-user 可写..."
mkdir -p /home/ec2-user/https---github.com-imethankhoooo-optic-system/storage/framework/sessions \\
         /home/ec2-user/https---github.com-imethankhoooo-optic-system/storage/framework/views \\
         /home/ec2-user/https---github.com-imethankhoooo-optic-system/storage/framework/cache \\
         /home/ec2-user/https---github.com-imethankhoooo-optic-system/storage/logs \\
         /home/ec2-user/https---github.com-imethankhoooo-optic-system/bootstrap/cache

# 暂时确保 ec2-user 对这些目录有写权限，以便 Composer 脚本运行
sudo chown -R ec2-user:ec2-user $LARAVEL_ROOT/storage $LARAVEL_ROOT/bootstrap/cache
sudo chmod -R u+rwx $LARAVEL_ROOT/storage $LARAVEL_ROOT/bootstrap/cache
echo "初步权限设置完成。"
# --- END: Pre-Composer Permission Setup ---

echo "正在配置 Redis 使用 TLS 连接 (修改 config/database.php)..."
CONFIG_DB_FILE="$LARAVEL_ROOT/config/database.php"

if [ ! -f "$CONFIG_DB_FILE" ]; then
    echo "错误: 配置文件 $CONFIG_DB_FILE 未找到。"
    exit 1
fi

if ! grep -q "'scheme' *=>" "$CONFIG_DB_FILE" | grep -A 5 "'default' =>"; then
  sed -i "/'database' => env('REDIS_DB', '0'),/a \
            'scheme' => 'tls'," "$CONFIG_DB_FILE"
  echo "为 'default' Redis 连接添加了 'scheme' => 'tls'。"
else
  echo "'default' Redis 连接中似乎已存在 'scheme' 配置，跳过添加。"
fi

if ! grep -q "'scheme' *=>" "$CONFIG_DB_FILE" | grep -A 5 "'cache' =>"; then
  sed -i "/'database' => env('REDIS_CACHE_DB', '1'),/a \
            'scheme' => 'tls'," "$CONFIG_DB_FILE"
  echo "为 'cache' Redis 连接添加了 'scheme' => 'tls'。"
else
  echo "'cache' Redis 连接中似乎已存在 'scheme' 配置，跳过添加。"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
sudo chown -R ec2-user:apache $LARAVEL_ROOT

sudo find $LARAVEL_ROOT -type d -exec chmod 775 {} \;
sudo find $LARAVEL_ROOT -type f -exec chmod 664 {} \;
sudo chmod -R ug+rwx $LARAVEL_ROOT/storage $LARAVEL_ROOT/bootstrap/cache
mkdir -p $LARAVEL_ROOT/storage/framework/views
sudo chmod -R ug+rwx $LARAVEL_ROOT/storage/framework/views
sudo chmod o+x /home/ec2-user

if [[ $(getenforce) == "Enforcing" ]]; then
  sudo setsebool -P httpd_enable_homedirs on
  sudo setsebool -P httpd_can_network_connect on
else
  echo "SELinux 不是 Enforcing 模式，跳过 setsebool 设置。"
fi

echo "正在启用并重启 Apache 和 PHP-FPM..."
sudo systemctl enable httpd
sudo systemctl enable php-fpm
sudo systemctl restart php-fpm
sudo systemctl restart httpd

echo "检查服务状态："
sudo systemctl status httpd --no-pager
sudo systemctl status php-fpm --no-pager

echo "部署命令执行完毕！请通过浏览器或 curl 测试访问。"
curl -s http://localhost/api/product-categories

unset LARAVEL_ROOT
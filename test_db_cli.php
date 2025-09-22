# 1. 重启 PHP-FPM (根据你的实际服务名调整)
sudo systemctl restart php-fpm 

# 2. 重启 Apache (根据你的实际服务名调整)
sudo systemctl restart httpd

# 3. (如果需要) 重新构建前端
 cd shoe-ecommerce-frontend
 npm run build

# 4. (推荐) 重新生成 Laravel 缓存
 cd ../
 php artisan optimize:clear
 php artisan config:cache
 php artisan route:cache
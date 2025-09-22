# Post-Clone Setup Guide

This guide details the steps required to set up the application after cloning the repository from Git.

## Prerequisites

Before you begin, ensure you have the following installed on your server/local machine:

*   **PHP** (Check `composer.json` for required version, likely >= 8.1)
*   **Composer** (PHP dependency manager)
*   **Node.js** and **npm** (or **yarn**) (For frontend dependencies and building)
*   **Database Server** (MySQL/MariaDB recommended, ensure it's running)
*   **Web Server** (Apache or Nginx, configured to serve the project)
*   **Redis Server** (Optional but recommended for caching, queues, etc.)

## Backend Setup (Laravel)

1.  **Navigate to Project Directory:**
    ```bash
    cd /path/to/your/project/https---github.com-imethankhoooo-optic-system 
    ```

2.  **Install Composer Dependencies:**
    ```bash
    composer install --optimize-autoloader --no-dev # For production
    # or
    composer install # For development
    ```

3.  **Create Environment File:**
    Copy the example environment file:
    ```bash
    cp .env.example .env
    ```

4.  **Configure `.env` File:**
    Open the `.env` file with a text editor (`nano .env` or `vim .env`) and update the following critical variables:
    *   `APP_NAME`: Your application name.
    *   `APP_ENV`: Set to `production` for production servers, `local` for development.
    *   `APP_KEY`: Will be generated in the next step.
    *   `APP_DEBUG`: Set to `false` for production, `true` for local development.
    *   `APP_URL`: Your backend's public URL (e.g., `http://54.224.146.109`). **Crucial for generating correct URLs.**
    *   `FRONTEND_URL`: Your frontend application's public URL (e.g., `http://54.224.146.109`). Used for CORS, redirects, etc.
    *   `DB_CONNECTION`: Usually `mysql`.
    *   `DB_HOST`: Your database host (e.g., RDS endpoint or `127.0.0.1` if local).
    *   `DB_PORT`: Usually `3306`.
    *   `DB_DATABASE`: Your database name.
    *   `DB_USERNAME`: Your database username.
    *   `DB_PASSWORD`: Your database password.
    *   `REDIS_HOST`: Your Redis host (e.g., ElastiCache endpoint or `127.0.0.1` if local).
    *   `REDIS_PASSWORD`: Your Redis password (if any, otherwise set to `null`).
    *   `REDIS_PORT`: Usually `6379`.
    *   `MAIL_...`: Configure your mail driver settings if sending emails.
    *   `SANCTUM_STATEFUL_DOMAINS`: Your frontend domain(s) for stateful authentication (e.g., `54.224.146.109` or your domain name). Separate multiple domains with commas.

5.  **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

6.  **Run Database Migrations:**
    This creates the necessary tables in your database.
    ```bash
    php artisan migrate --force # Use --force in production to skip confirmation
    ```
    *   **(Optional) Run Seeders:** If you have seeders to populate initial data:
        ```bash
        php artisan db:seed --force 
        ```

7.  **Create Storage Link:**
    This makes files in `storage/app/public` accessible from the web.
    ```bash
    php artisan storage:link
    ```
    *   _Note: Ensure your web server is configured to follow symbolic links if necessary._

8.  **Optimize for Production (Recommended for Production):**
    Clear caches and generate optimized files.
    ```bash
    php artisan optimize:clear 
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache # Optional, caches Blade views
    ```

9.  **Set File Permissions (If Necessary):**
    Your web server user (e.g., `apache`, `nginx`, `www-data`) needs write permissions on the `storage` and `bootstrap/cache` directories.
    ```bash
    # Example (adjust user/group as needed):
    sudo chown -R $USER:apache storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache 
    # Or consult Laravel documentation for specific permission recommendations.
    ```
    *   **SELinux Context (If using SELinux, like on Amazon Linux):** Ensure the correct context is set for storage and cache directories.
        ```bash
        sudo semanage fcontext -a -t httpd_sys_rw_content_t "/path/to/project/storage(/.*)?"
        sudo semanage fcontext -a -t httpd_sys_rw_content_t "/path/to/project/bootstrap/cache(/.*)?"
        sudo restorecon -Rv /path/to/project/storage
        sudo restorecon -Rv /path/to/project/bootstrap/cache
        ```

## Frontend Setup (React/Vite)

1.  **Navigate to Frontend Directory:**
    ```bash
    cd /path/to/your/project/https---github.com-imethankhoooo-optic-system/shoe-ecommerce-frontend
    ```

2.  **Install Node.js Dependencies:**
    ```bash
    npm install
    # or if you use yarn:
    # yarn install
    ```

3.  **Create Frontend Environment File:**
    *   For **production**, create a `.env.production` file.
    *   For **local development**, create a `.env.development` or just `.env` file.
    *   You might need to copy an example file if one exists (`cp .env.example .env.production`).

4.  **Configure Frontend `.env` File:**
    Open the relevant `.env` file (e.g., `.env.production`) and set the following:
    *   `VITE_API_BASE_URL`: The **base URL** of your Laravel backend (e.g., `http://54.224.146.109`). **Do not include `/api` here.**
    *   `VITE_BACKEND_ASSET_URL`: The base URL for accessing assets served directly by the backend (often the same as `VITE_API_BASE_URL`, e.g., `http://54.224.146.109`). Used for images referenced in promo pages, etc.

5.  **Build Frontend Assets (For Production):**
    This compiles and optimizes your React application into static files in the `dist` directory.
    ```bash
    npm run build
    ```
    *   For **local development**, you would typically run `npm run dev`.

## Web Server Configuration

*   **Document Root:** Configure your Apache or Nginx virtual host to point its `DocumentRoot` to the Laravel project's `public` directory (e.g., `/path/to/project/https---github.com-imethankhoooo-optic-system/public`).
*   **Rewrite Rules:** Ensure your web server configuration includes the necessary rewrite rules to direct all non-file requests to Laravel's `index.php`. Laravel includes example configurations in the `public` directory (`.htaccess` for Apache). For Nginx, you'll need to add the appropriate `location` block in your server configuration.
*   **Frontend Routing:** For Single Page Applications (SPAs) like React, configure your web server (Apache `mod_rewrite` or Nginx `try_files`) to redirect all non-API and non-file requests to the frontend's `index.html` (located in `shoe-ecommerce-frontend/dist/index.html` after building). This allows React Router to handle frontend navigation. You might achieve this by having Apache/Nginx serve the `shoe-ecommerce-frontend/dist` directory as the primary document root and proxy API/storage requests to the backend. *Refer to the deployment documentation or previous conversation steps for specific Apache configuration examples.*
*   **PHP-FPM:** Ensure PHP-FPM is installed, running, and configured correctly to work with your web server.

## Running the Application

1.  **Ensure Services are Running:** Start/enable your web server (Apache/Nginx), PHP-FPM, Database server, and Redis server (if used).
    ```bash
    # Example commands (adjust service names as needed):
    sudo systemctl start httpd
    sudo systemctl enable httpd
    sudo systemctl start php-fpm
    sudo systemctl enable php-fpm
    sudo systemctl start redis # if installed locally
    sudo systemctl enable redis # if installed locally
    # Database (RDS is managed, local needs starting/enabling)
    ```
2.  **Access the Site:** Open your web browser and navigate to the URL configured for your frontend (`FRONTEND_URL`).

## Troubleshooting Common Issues

*   **403 Forbidden:** Check file/directory permissions (especially `storage`, `bootstrap/cache`), web server configuration (`<Directory>` directives in Apache), and SELinux contexts.
*   **404 Not Found (API):** Ensure API routes are defined correctly in Laravel (`routes/api.php`), the web server proxy/rewrite rules for `/api` are correct, and the frontend is calling the correct API endpoint (`VITE_API_BASE_URL`).
*   **404 Not Found (Frontend Routes):** Ensure your web server is configured to redirect non-existent paths to the frontend's `index.html` to handle SPA routing.
*   **500 Internal Server Error:** Check Laravel logs (`storage/logs/laravel.log`) and web server error logs (`/var/log/httpd/error_log` or similar) for specific error messages. Common causes include database connection issues, incorrect `.env` settings, code errors, or permission problems.
*   **CSRF Token Mismatch / 419 Error:** Ensure `APP_URL` and `SANCTUM_STATEFUL_DOMAINS` are correctly set in `.env`, clear browser cookies for the site, and ensure the frontend sends the `X-XSRF-TOKEN` header.
*   **CORS Errors:** Check the `config/cors.php` settings and ensure `FRONTEND_URL` is listed in `allowed_origins`. Also verify `SANCTUM_STATEFUL_DOMAINS`.
*   **Database Connection Refused (Artisan):** Double-check `.env` database credentials, ensure the database server is running and accessible from the server (test with `telnet` or `nc`), and clear Laravel config cache (`php artisan config:clear`, manually delete `bootstrap/cache/config.php` if needed).
*   **Changes Not Reflecting:** Clear all relevant caches: Laravel caches (`config:clear`, `route:clear`, etc.), browser cache, and potentially Cloudflare/CDN cache if used. Rebuild frontend assets (`npm run build`) if frontend code changed. Restart PHP-FPM and the web server.

Good luck! 
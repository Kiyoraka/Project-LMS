# DigitalOcean Video Upload Fix Guide

## Problem
- 422 error when uploading videos (even 7MB files)
- Images upload fine
- Issue is server configuration, not Laravel code

## Quick Fix Commands (Run on your DigitalOcean server via SSH)

### Step 1: Update PHP Configuration
```bash
# Find PHP version
php -v

# Edit PHP FPM configuration (adjust version if needed)
sudo nano /etc/php/8.2/fpm/php.ini
```

Find and update these values:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
max_file_uploads = 20
```

### Step 2: Update Nginx Configuration
```bash
# Edit your site configuration
sudo nano /etc/nginx/sites-available/default
# Or if you have a custom site file:
# sudo nano /etc/nginx/sites-available/lmsprototype
```

Add inside the `server` block:
```nginx
client_max_body_size 500M;
client_body_timeout 300s;
```

Full example:
```nginx
server {
    listen 80;
    server_name lmsprototype-app-yo8va.ondigitalocean.app;
    root /var/www/html/public;

    # ADD THESE LINES FOR VIDEO UPLOAD
    client_max_body_size 500M;
    client_body_timeout 300s;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        # ADD THESE FOR PHP TIMEOUT
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }
}
```

### Step 3: Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Test Nginx configuration
sudo nginx -t

# If test passes, restart Nginx
sudo systemctl restart nginx
```

### Step 4: Clear Laravel Cache
```bash
cd /var/www/html  # or your app directory
php artisan config:clear
php artisan cache:clear
```

## Alternative: Use .user.ini for PHP Settings
If you can't edit php.ini, create this file in your public directory:

```bash
sudo nano /var/www/html/public/.user.ini
```

Add:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
```

## Verification
1. Visit: https://lmsprototype-app-yo8va.ondigitalocean.app/check-upload-limits.php?token=check-limits-2025
2. Check if all values show green
3. Delete the check file after verification

## If Using Apache Instead of Nginx
Edit `.htaccess` in public folder:
```apache
php_value upload_max_filesize 500M
php_value post_max_size 500M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 512M
```

## Laravel Optimization for Large Files
In `app/Http/Kernel.php`, you might need to increase request size:
```php
protected $middleware = [
    // ... other middleware
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
];
```

## Still Not Working?
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Check Nginx logs: `sudo tail -f /var/log/nginx/error.log`
3. Check PHP logs: `sudo tail -f /var/log/php8.2-fpm.log`

## Security Note
After fixing, consider:
- Implementing chunked uploads for very large files
- Using cloud storage (S3, DigitalOcean Spaces) for videos
- Adding virus scanning for uploaded files
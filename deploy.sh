#!/bin/bash

# LMS Prototype Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e

ENVIRONMENT=${1:-production}
APP_NAME="lms-prototype"
BACKUP_DIR="/var/backups/$APP_NAME"
WEB_ROOT="/var/www/html"

echo "🚀 Starting deployment of $APP_NAME to $ENVIRONMENT..."

# Create backup directory
sudo mkdir -p $BACKUP_DIR

# Create backup of current deployment
if [ -d "$WEB_ROOT" ]; then
    echo "📦 Creating backup..."
    sudo tar -czf "$BACKUP_DIR/backup-$(date +%Y%m%d_%H%M%S).tar.gz" \
        --exclude=storage/logs \
        --exclude=storage/framework/cache \
        --exclude=storage/framework/sessions \
        --exclude=storage/framework/views \
        --exclude=node_modules \
        -C "$WEB_ROOT" . || true
fi

# Pull latest code
echo "📥 Pulling latest code..."
cd "$WEB_ROOT"
sudo git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
echo "🔨 Building assets..."
sudo npm ci
sudo npm run build

# Set proper permissions
echo "🔐 Setting permissions..."
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 storage bootstrap/cache

# Laravel optimization
echo "⚡ Optimizing Laravel..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Run database migrations
echo "🗄️ Running migrations..."
sudo -u www-data php artisan migrate --force

# Restart services
echo "🔄 Restarting services..."
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm

# Restart queue workers if they exist
if sudo systemctl is-active --quiet lms-queue; then
    sudo -u www-data php artisan queue:restart
fi

# Clear old backups (keep last 5)
echo "🧹 Cleaning up old backups..."
sudo find $BACKUP_DIR -name "backup-*.tar.gz" -type f -mtime +7 -delete 2>/dev/null || true

echo "✅ Deployment completed successfully!"
echo "🌐 Your LMS is now live!"

# Optional: Run a quick health check
echo "🏥 Running health check..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/ || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Health check passed!"
else
    echo "⚠️  Health check failed (HTTP $HTTP_CODE)"
    echo "🔍 Check the logs: sudo tail -f /var/log/nginx/error.log"
fi 
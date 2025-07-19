#!/bin/bash
# deploy.sh - Production deployment script for Medical Contract System

# Exit on any error
set -e

echo "🚀 Starting deployment process for Medical Contract System..."

# Load environment variables if .env exists
if [ -f ".env" ]; then
    echo "🔧 Loading environment variables..."
    export $(grep -v '^#' .env | xargs)
fi

# Set maintenance mode
echo "🔧 Enabling maintenance mode..."
php artisan down --message="System is under maintenance. We'll be back soon!" --retry=60

# Get the latest code from the repository
echo "🔄 Pulling latest code from repository..."
git fetch origin
git reset --hard origin/main

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Install NPM dependencies and build assets
echo "📦 Installing NPM dependencies..."
npm ci --prefer-offline --no-audit --progress=false

echo "🔨 Building frontend assets..."
npm run production

# Clear and cache configurations
echo "⚙️ Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Run database migrations
echo "💾 Running database migrations..."
php artisan migrate --force

# Clear application cache
echo "🧹 Clearing application cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart queues
echo "🔄 Restarting queues..."
php artisan queue:restart

# Set proper permissions
echo "🔒 Setting file permissions..."
chown -R www-data:www-data .
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache
chmod -R 775 storage/framework/{sessions,views,cache}
chmod -R 775 storage/logs
chmod -R 775 public/uploads

# Clear caches again after permission changes
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart PHP-FPM if available
if command -v systemctl &> /dev/null; then
    echo "🔄 Restarting PHP-FPM..."
    sudo systemctl restart php8.2-fpm || true
fi

# Restart Nginx if available
if command -v systemctl &> /dev/null; then
    echo "🔄 Restarting Nginx..."
    sudo systemctl restart nginx || true
fi

# Disable maintenance mode
echo "✅ Bringing application back online..."
php artisan up

echo "
🎉 Deployment completed successfully!
📅 $(date)"

echo -e "\n🔍 Post-deployment checks:"
php artisan about

# Check if there are any pending migrations
if php artisan migrate:status | grep -q 'No' ; then
    echo -e "\n⚠️  WARNING: There are pending migrations! Run 'php artisan migrate' to apply them."
fi

# Check if storage is linked
if [ ! -L "public/storage" ]; then
    echo -e "\nℹ️  Storage link not found. Run 'php artisan storage:link' to create it."
fi

echo -e "\n🚀 Application is now live at: ${APP_URL:-'Please set APP_URL in .env'}"

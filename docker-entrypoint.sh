#!/bin/bash

echo "Starting entrypoint script..."

# Wait for MySQL to be ready (with timeout)
echo "Waiting for MySQL to be ready..."
max_tries=30
counter=0

while ! mysql -h db -u admin -ppassword roster --protocol=TCP -e "SELECT 1" >/dev/null 2>&1; do
    counter=$((counter + 1))
    if [ $counter -gt $max_tries ]; then
        echo "MySQL connection timeout after ${max_tries} attempts. Exiting..."
        exit 1  # Exit with error code
    fi
    echo "Attempt $counter/${max_tries}: MySQL not ready yet..."
    sleep 2
done

echo "MySQL is ready!"

# Change to Laravel directory
cd /var/www/laravel-app

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo "Creating new Laravel project..."
    composer create-project laravel/laravel:^12.0 .
fi

# Install PHP dependencies
echo "Installing Composer dependencies..."
composer install

# Set directory permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate key if not already set
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Remove all existing DB configuration lines
sed -i '/DB_CONNECTION/d' .env
sed -i '/DB_HOST/d' .env
sed -i '/DB_PORT/d' .env
sed -i '/DB_DATABASE/d' .env
sed -i '/DB_USERNAME/d' .env
sed -i '/DB_PASSWORD/d' .env

# Add our DB configuration
echo "DB_CONNECTION=mysql" >> .env
echo "DB_HOST=db" >> .env
echo "DB_PORT=3306" >> .env
echo "DB_DATABASE=roster" >> .env
echo "DB_USERNAME=admin" >> .env
echo "DB_PASSWORD=password" >> .env

# Generate key if not exists
php artisan key:generate --force

# Clear config cache
php artisan config:clear

# Run migrations
echo "Running migrations..."
php artisan migrate --force -v || exit 1

# Start PHP-FPM
php-fpm 
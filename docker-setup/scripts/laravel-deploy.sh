#!/usr/bin/env bash
echo "Running composer"
composer global require hirak/prestissimo
composer install --no-dev --working-dir=/var/www/html

# echo "generating application key..."
# php artisan key:generate --show

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

# echo "Composer dump..."
# composer dump-autoload

echo "Caching optimize..."
php artisan optimize:clear

# echo "Route list..."
# php artisan route:list

echo "Running migrations..."
php artisan migrate --force

# echo "Running seeder files..."
# php artisan db:seed --force

# echo "Run php worker..."
# php artisan queue:work

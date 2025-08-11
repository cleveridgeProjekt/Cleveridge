#!/bin/bash
set -e

echo "⚙️ Clearing Laravel config..."
php artisan config:clear

echo "⚙️ Caching Laravel config..."
php artisan config:cache

echo "⚙️ Running database migrations..."
php artisan migrate:fresh --force || echo "Migration failed"

echo "⚙️ Seeding database..."
php artisan db:seed --force || echo "Seeding failed"

echo "🚀 Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}

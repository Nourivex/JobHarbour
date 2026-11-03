#!/bin/sh
set -e

PORT=${PORT:-80}
echo "[JobHarbour] Starting service on port $PORT..."

# Ganti LISTEN_PORT pada konfigurasi Nginx sesuai environment Render ($PORT)
sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/http.d/default.conf

# Setup SQLite otomatis jika koneksi database default adalah sqlite
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    export DB_CONNECTION=sqlite
    if [ -z "$DB_DATABASE" ]; then
        export DB_DATABASE=/var/www/html/database/database.sqlite
    fi
    
    DB_DIR=$(dirname "$DB_DATABASE")
    mkdir -p "$DB_DIR"
    
    if [ ! -f "$DB_DATABASE" ]; then
        echo "[JobHarbour] Inisialisasi berkas SQLite di $DB_DATABASE..."
        touch "$DB_DATABASE"
    fi
    
    chown -R www-data:www-data "$DB_DIR"
    chmod -R 775 "$DB_DIR"
fi

# Pastikan permission direktori storage dan cache Laravel
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate APP_KEY otomatis jika belum diset atau belum berformat base64
if [ -z "$APP_KEY" ] || ! echo "$APP_KEY" | grep -q "base64:"; then
    echo "[JobHarbour] Menghasilkan APP_KEY valid untuk enkripsi Laravel..."
    php artisan key:generate --force
fi

# Jalankan migrasi database
echo "[JobHarbour] Menjalankan migrasi database..."
php artisan migrate --force

# Jalankan seeder akun demo (Budi, Recruiter, Admin) jika SEED_ON_DEPLOY=true atau pertama kali deploy
if [ "$SEED_ON_DEPLOY" = "true" ] || [ ! -f /var/www/html/storage/.seeded ]; then
    echo "[JobHarbour] Menjalankan database seeder untuk akun demo..."
    php artisan db:seed --force || true
    touch /var/www/html/storage/.seeded
fi

# Buat symbolic link storage
php artisan storage:link || true

# Optimasi konfigurasi, route, dan view untuk production
if [ "$APP_ENV" = "production" ] || [ -z "$APP_ENV" ]; then
    echo "[JobHarbour] Mengoptimasi cache konfigurasi dan rute..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "[JobHarbour] Sistem siap! Menjalankan PHP-FPM dan Nginx..."
php-fpm -D
exec nginx -g "daemon off;"

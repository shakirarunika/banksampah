#!/usr/bin/env bash
# Deploy Bank Sampah di PC server.
# Jalankan dari Git Bash: ./deploy.sh
set -e
cd "$(dirname "$0")"

echo "==> Masuk maintenance mode..."
php artisan down || true
# Apapun yang terjadi (sukses/gagal), pastikan situs nyala lagi
trap 'php artisan up' EXIT

echo "==> Narik update dari GitHub..."
git pull origin main

echo "==> Install dependensi PHP (tanpa dev)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Build asset frontend..."
npm ci
npm run build

echo "==> Migrasi database..."
php artisan migrate --force

echo "==> Refresh cache..."
php artisan config:cache
php artisan view:cache
# ponytail: route:cache sengaja di-skip — routes/web.php masih pakai closure;
# kalau nanti semua route sudah pakai controller/komponen, tambahkan di sini.

echo "==> Deploy selesai! ✅"

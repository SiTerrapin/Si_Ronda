@echo off
title SiRonda - Starter Script
echo ===================================================
echo             SiRonda Local Development
echo ===================================================
echo.

echo [1/3] Menjalankan Docker MySQL ^& phpMyAdmin...
docker compose up -d
if %errorlevel% neq 0 (
    echo [ERROR] Docker gagal dijalankan. Pastikan Docker Desktop sudah running!
    pause
    exit /b %errorlevel%
)
echo [OK] Database MySQL (Port 3306) dan phpMyAdmin (Port 8080) aktif.
echo.

echo [2/3] Memeriksa dependensi backend...
if not exist "vendor" (
    echo Menginstall library PHP via Composer...
    composer install
    php artisan key:generate
    php artisan migrate:fresh --seed
)
echo.

echo [3/3] Menjalankan server Laravel di http://127.0.0.1:8000
echo Tekan CTRL+C untuk menghentikan server.
echo.
php artisan serve


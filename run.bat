@echo off
title Menjalankan KasirKu POS System...
echo ========================================================
echo        KASIRKU POS - POINT OF SALE SYSTEM
echo ========================================================
echo Membuka Laravel Backend dan Vue 3 Frontend...
echo.

start "KasirKu Backend API (Laravel)" cmd /k "cd backend && php artisan serve --port=8000"
start "KasirKu Frontend (Vue 3 + Vite)" cmd /k "cd frontend && npm run dev"

echo Backend API berjalan di: http://127.0.0.1:8000
echo Frontend POS berjalan di: http://localhost:5173
echo.
echo Browser akan terbuka secara otomatis...
timeout /t 3 >nul
start http://localhost:5173
pause

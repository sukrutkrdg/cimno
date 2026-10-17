@echo off
chcp 65001 >nul
echo ========================================================
echo   ENERJIK CIMNASTIK - SALON YONETIM SISTEMI BASLATILIYOR
echo ========================================================
echo.
echo   Veli Web Sitesi          : http://127.0.0.1:8000
echo   Mobil Uygulama (Kisayol) : http://127.0.0.1:8000/mobil
echo   Salon QR Kisayol Afisi   : http://127.0.0.1:8000/qr-afis
echo   Yonetim Paneli (16 Modul): http://127.0.0.1:8000/panel
echo.
cd /d "C:\Users\sukru.kucuk\.gemini\antigravity\scratch\enerjik-cimnastik\backend"
"C:\Users\sukru.kucuk\Downloads\php-8.5.11-nts-Win32-vs17-x64\php.exe" artisan serve --host=127.0.0.1 --port=8000
pause

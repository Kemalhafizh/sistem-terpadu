@echo off
title Backend SISTA - SMA Islam Sultan Agung 1 Semarang
color 0A

echo ================================================================
echo    SULAONE BACKEND - SMA ISLAM SULTAN AGUNG 1 SEMARANG
echo ================================================================
echo.

cd /d "%~dp0"

echo [1/3] Memeriksa koneksi ADB untuk HP Android (USB Type-C)...
where adb >nul 2>nul
if %errorlevel% equ 0 (
    adb reverse tcp:8000 tcp:8000
    echo     [OK] Port reverse ADB aktif: tcp:8000 ^<^=^> tcp:8000
) else (
    if exist "%LOCALAPPDATA%\Android\Sdk\platform-tools\adb.exe" (
        "%LOCALAPPDATA%\Android\Sdk\platform-tools\adb.exe" reverse tcp:8000 tcp:8000
        echo     [OK] Port reverse ADB aktif via Android SDK
    ) else (
        echo     [INFO] ADB tidak terdeteksi di PATH, melewati port reverse.
    )
)

echo.
echo [2/3] Informasi Akun Login Seeder Bawaan:
echo     - Siswa : siswa1@student.sa1.sch.id           (Password: password)
echo     - Guru  : guru.matematika@sultanagung1.sch.id      (Password: password)
echo     - Ortu  : ortu1@parent.sa1.sch.id             (Password: password)
echo     - Admin : admin@sultanagung1.sch.id           (Password: password)
echo.
echo [3/3] Menyalakan Server Backend Laravel pada http://127.0.0.1:8000...
echo     Status Health: http://127.0.0.1:8000/api/health
echo     (Tekan tombol CTRL + C di jendela ini untuk mematikan server)
echo.
echo ================================================================
echo.

php -S 127.0.0.1:8000 -t public
pause

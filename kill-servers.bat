@echo off
echo Stopping all LMS servers...

REM Kill Node.js processes (Vue frontend)
taskkill /F /IM node.exe 2>nul
if %errorlevel% equ 0 (
    echo Frontend server stopped
) else (
    echo No frontend server running
)

REM Kill PHP processes (Laravel backend)
taskkill /F /IM php.exe 2>nul
if %errorlevel% equ 0 (
    echo Backend server stopped
) else (
    echo No backend server running
)

echo All servers stopped.
timeout /t 2 >nul



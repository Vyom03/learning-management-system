@echo off
echo Starting LMS servers...

REM Kill any existing servers first
call kill-servers.bat

echo.
echo Starting Laravel backend...
start "Laravel Backend" cmd /k "cd laravel-backend && php artisan serve"

timeout /t 3 >nul

echo Starting Vue.js frontend...
start "Vue Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo Servers started!
echo Backend: http://localhost:8000
echo Frontend: http://localhost:5177
echo.
echo Press any key to stop servers...
pause >nul

REM Stop servers when user presses a key
call kill-servers.bat



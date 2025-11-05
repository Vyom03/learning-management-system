# Quick Start Guide

## Prerequisites Check
- [ ] PHP 8.1+ installed
- [ ] Composer installed
- [ ] Node.js 16+ installed
- [ ] MySQL running
- [ ] Database `loginauth` exists

## Setup Steps

### 1. Backend Setup
```bash
cd laravel-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 2. Database Setup
Run these SQL files in MySQL (in order):
1. `create-indexes-safe.sql` - Performance indexes
2. `setup-all-tables.sql` - All LMS tables

Or use batch files:
```bash
create-indexes.bat
setup-all-tables.bat
```

### 3. Frontend Setup
```bash
cd frontend
npm install
```

### 4. Start Servers

**Option A: Use the start script**
```bash
start-servers.bat
```

**Option B: Manual start**

Terminal 1 (Backend):
```bash
cd laravel-backend
php artisan serve
```

Terminal 2 (Frontend):
```bash
cd frontend
npm run dev
```

### 5. Stop Servers
```bash
kill-servers.bat
```

Or manually:
- Press `Ctrl+C` in each terminal
- Or use `kill-servers.bat` to force stop all

## Access the Application
- Frontend: http://localhost:5177
- Backend API: http://localhost:8000/api

## Default Login Credentials
Check your Student Management System database for user credentials.
Users must be created through the SMS, not through LMS registration.

## Troubleshooting

### Servers Won't Start
1. Check if ports 8000 and 5177 are available
2. Kill existing processes: `kill-servers.bat`
3. Check if MySQL is running

### Database Connection Errors
1. Verify `.env` database credentials
2. Ensure database `loginauth` exists
3. Check MySQL service is running

### Port Already in Use
1. Update port in `vite.config.js` (frontend)
2. Update port in `php artisan serve --port=XXXX` (backend)
3. Update CORS config in `config/cors.php`



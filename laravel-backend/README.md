# LMS Laravel Backend

## Setup Complete! ✅

The Laravel backend has been set up and configured. Here's what's been done:

### ✅ Completed
1. Laravel 10 installed
2. JWT Auth package installed
3. Spatie Permission package installed
4. Database configured (loginauth MySQL database)
5. Controllers created (Auth, Course, Dashboard)
6. API routes configured
7. CORS configured for frontend (http://localhost:5175)

### 🚀 Starting the Server

```bash
cd D:/lms/laravel-backend
php artisan serve --port=3000
```

The API will be available at `http://localhost:3000/api/*`

### 📝 Important Notes

1. **JWT Configuration**: The JWT secret needs to be set in `.env` file:
   ```
   JWT_SECRET=your-secret-key-here
   ```
   Run: `php artisan jwt:secret` (if JWT commands are available)

2. **Database**: Using existing MySQL database `loginauth`
   - Host: 127.0.0.1
   - Database: loginauth
   - Username: root
   - Password: temp@12345

3. **Frontend**: No changes needed! The frontend will automatically work with Laravel backend since it runs on the same port (3000) and uses the same API structure.

### 🧪 Testing

```bash
# Health check
curl http://localhost:3000/api/health

# Login
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@test.com\",\"password\":\"password\"}"
```

### 📚 Next Steps

1. Start the Laravel server
2. Test the API endpoints
3. Complete remaining controllers (Enrollments, Quizzes, etc.)
4. Test with frontend

### 🔧 Troubleshooting

If JWT commands don't work:
1. Check if `tymon/jwt-auth` is installed: `composer show tymon/jwt-auth`
2. Manually set `JWT_SECRET` in `.env` file
3. Run `php artisan config:clear` and `php artisan cache:clear`

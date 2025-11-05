# Laravel Backend Structure

This directory contains the Laravel backend structure files that will be copied to the Laravel project once installation is complete.

## Files to Create:

1. **Controllers** (`app/Http/Controllers/Api/`):
   - `AuthController.php` - Authentication endpoints
   - `CourseController.php` - Course management
   - `EnrollmentController.php` - Enrollment management
   - `QuizController.php` - Quiz management
   - `DashboardController.php` - Dashboard statistics

2. **Models** (`app/Models/`):
   - `User.php` - User model (already exists, needs modification)
   - `Course.php` - Course model
   - `Enrollment.php` - Enrollment model
   - `Quiz.php` - Quiz model

3. **Middleware** (`app/Http/Middleware/`):
   - JWT authentication middleware (provided by tymon/jwt-auth)

4. **Routes** (`routes/api.php`):
   - All API routes matching Express backend

5. **Configuration**:
   - JWT config (published from tymon/jwt-auth)
   - CORS config (updated for frontend)
   - Database config (already configured)


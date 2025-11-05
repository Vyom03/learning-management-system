<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\QuizController;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'message' => 'LMS API is running']);
});

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Handle GET requests to login endpoint (for browser navigation)
Route::get('/auth/login', function () {
    return response()->json([
        'error' => 'Method not allowed',
        'message' => 'Please use POST method to login. Access the frontend at http://localhost:5177/login'
    ], 405);
});

// Protected routes (require JWT authentication)
Route::middleware('auth:api')->group(function () {
    // Authentication
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Courses (public listing, but teachers see only their courses)
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    
    // Course management (admin only for create, teacher/admin for update/delete)
    Route::post('/courses', [CourseController::class, 'store']); // Admin only
    Route::put('/courses/{id}', [CourseController::class, 'update']); // Teacher/Admin
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']); // Teacher/Admin

    // Enrollments
    Route::get('/enrollments/my-courses', [EnrollmentController::class, 'myCourses']); // Student
    Route::post('/enrollments/{courseId}', [EnrollmentController::class, 'enroll']); // Student

    // Forums - All authenticated users can view and create topics/replies
    Route::get('/forums/course/{courseId}/topics', [ForumController::class, 'getTopics']);
    Route::get('/forums/topics/{topicId}', [ForumController::class, 'getTopic']);
    Route::post('/forums/topics', [ForumController::class, 'createTopic']);
    Route::post('/forums/replies', [ForumController::class, 'createReply']);

    // Quizzes
    Route::post('/quizzes', [QuizController::class, 'store']); // Teacher only
    Route::get('/quizzes/course/{courseId}', [QuizController::class, 'getQuizzesByCourse']);
    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::post('/quizzes/{id}/submit', [QuizController::class, 'submit']); // Student only

    // Certificates
    Route::get('/certificates/my-certificates', [CertificateController::class, 'myCertificates']); // Student only

    // TODO: Add other routes
    // - Grades
    // - Progress
});


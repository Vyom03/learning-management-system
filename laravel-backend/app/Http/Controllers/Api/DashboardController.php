<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics based on user role
     */
    public function stats(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $userId = is_object($user) ? $user->id : $user['id'];
            
            // Get role from token claims or database
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';

            if ($userRole === 'student') {
                // Student stats
                $enrollments = (int) DB::table('enrollments')
                    ->where('student_id', $userId)
                    ->where(function($query) {
                        $query->where('status', 'active')
                              ->orWhereNull('status');
                    })
                    ->count();

                $certificates = 0;
                try {
                    $certificates = (int) DB::table('certificates')
                        ->where('student_id', $userId)
                        ->count();
                } catch (\Exception $e) {
                    // Table doesn't exist, return 0
                    $certificates = 0;
                }

                       $quizAttempts = 0;
                       try {
                           $quizAttempts = (int) DB::table('quiz_attempts')
                               ->where('student_id', $userId)
                               ->count();
                       } catch (\Exception $e) {
                           // Table doesn't exist, return 0
                           $quizAttempts = 0;
                           \Log::info('quiz_attempts table does not exist yet');
                       }

                return response()->json([
                    'enrollments' => $enrollments,
                    'certificates' => $certificates,
                    'quizAttempts' => $quizAttempts,
                    'myCourses' => 0,
                    'totalStudents' => 0,
                    'quizzes' => 0
                ]);
            } elseif ($userRole === 'teacher') {
                // Teacher stats
                $myCourses = (int) DB::table('courses')
                    ->where('teacher_id', $userId)
                    ->count();

                       // Count distinct students enrolled in teacher's courses (optimized)
                       $totalStudents = (int) DB::table('enrollments as e')
                           ->join('courses as c', 'e.course_id', '=', 'c.id')
                           ->where('c.teacher_id', $userId)
                           ->where(function($query) {
                               $query->where('e.status', 'active')
                                     ->orWhereNull('e.status');
                           })
                           ->distinct('e.student_id')
                           ->count('e.student_id');

                // Count quizzes for teacher's courses
                $quizzes = 0;
                try {
                    $quizzes = (int) DB::table('quizzes as q')
                        ->join('courses as c', 'q.course_id', '=', 'c.id')
                        ->where('c.teacher_id', $userId)
                        ->count();
                } catch (\Exception $e) {
                    // Table doesn't exist, return 0
                    $quizzes = 0;
                }

                \Log::info('Teacher dashboard stats', [
                    'teacher_id' => $userId,
                    'myCourses' => $myCourses,
                    'totalStudents' => $totalStudents,
                    'quizzes' => $quizzes
                ]);

                return response()->json([
                    'enrollments' => 0,
                    'certificates' => 0,
                    'quizAttempts' => 0,
                    'myCourses' => $myCourses,
                    'totalStudents' => $totalStudents,
                    'quizzes' => $quizzes
                ]);
            } elseif ($userRole === 'admin') {
                // Admin stats - all data
                $myCourses = (int) DB::table('courses')->count();

                       // Count distinct students (optimized)
                       $totalStudents = (int) DB::table('model_has_roles as mhr')
                           ->join('roles as r', 'mhr.role_id', '=', 'r.id')
                           ->where('r.name', 'student')
                           ->where('mhr.model_type', 'App\\Models\\User')
                           ->distinct('mhr.model_id')
                           ->count('mhr.model_id');

                // Check if quizzes table exists, if not return 0
                $quizzes = 0;
                try {
                    $quizzes = (int) DB::table('quizzes')->count();
                } catch (\Exception $e) {
                    // Table doesn't exist, return 0
                    $quizzes = 0;
                }

                \Log::info('Admin dashboard stats', [
                    'myCourses' => $myCourses,
                    'totalStudents' => $totalStudents,
                    'quizzes' => $quizzes
                ]);

                return response()->json([
                    'enrollments' => 0,
                    'certificates' => 0,
                    'quizAttempts' => 0,
                    'myCourses' => $myCourses,
                    'totalStudents' => $totalStudents,
                    'quizzes' => $quizzes
                ]);
            }

            return response()->json([
                'enrollments' => 0,
                'certificates' => 0,
                'quizAttempts' => 0,
                'myCourses' => 0,
                'totalStudents' => 0,
                'quizzes' => 0
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch dashboard stats',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}


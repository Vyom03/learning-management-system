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
            $userId = $user->id ?? $user['id'];
            $userRole = $user->role ?? $user['role'] ?? 'student';

            if ($userRole === 'student') {
                // Student stats
                $enrollments = DB::table('enrollments')
                    ->where('student_id', $userId)
                    ->where('status', 'active')
                    ->count();

                $certificates = DB::table('certificates')
                    ->where('student_id', $userId)
                    ->count();

                $quizAttempts = DB::table('quiz_attempts')
                    ->where('student_id', $userId)
                    ->count();

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
                $myCourses = DB::table('courses')
                    ->where('teacher_id', $userId)
                    ->count();

                $totalStudents = DB::table('enrollments as e')
                    ->join('courses as c', 'e.course_id', '=', 'c.id')
                    ->where('c.teacher_id', $userId)
                    ->where('e.status', 'active')
                    ->distinct('e.student_id')
                    ->count('e.student_id');

                $quizzes = DB::table('quizzes as q')
                    ->join('courses as c', 'q.course_id', '=', 'c.id')
                    ->where('c.teacher_id', $userId)
                    ->count();

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
                $myCourses = DB::table('courses')->count();

                $totalStudents = DB::table('model_has_roles as mhr')
                    ->join('roles as r', 'mhr.role_id', '=', 'r.id')
                    ->where('r.name', 'student')
                    ->where('mhr.model_type', 'App\\Models\\User')
                    ->distinct('mhr.model_id')
                    ->count('mhr.model_id');

                $quizzes = DB::table('quizzes')->count();

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
            return response()->json(['error' => 'Failed to fetch dashboard stats'], 500);
        }
    }
}


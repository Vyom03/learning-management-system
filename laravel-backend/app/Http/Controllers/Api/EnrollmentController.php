<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnrollmentController extends Controller
{
    /**
     * Get student's enrolled courses
     */
    public function myCourses(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $userId = is_object($user) ? $user->id : $user['id'];
            
            // Optimized query with proper indexing
            $enrollments = DB::table('enrollments as e')
                ->join('courses as c', 'e.course_id', '=', 'c.id')
                ->leftJoin('users as u', 'c.teacher_id', '=', 'u.id')
                ->where('e.student_id', $userId) // Indexed: idx_enrollments_student_id
                ->where(function($query) {
                    $query->where('e.status', 'active')
                          ->orWhereNull('e.status');
                })
                ->select(
                    'e.id',
                    'e.course_id',
                    'e.student_id',
                    'e.status',
                    DB::raw('COALESCE(e.enrolled_at, e.created_at) as enrolled_at'),
                    DB::raw('COALESCE(e.progress_percentage, 0) as progress_percentage'),
                    DB::raw('COALESCE(e.completed, 0) as completed'),
                    'c.name as title',
                    'c.code',
                    'c.description',
                    'c.semester',
                    'c.credits',
                    'u.name as instructor_name',
                    'u.email as instructor_email'
                )
                ->orderByRaw('COALESCE(e.enrolled_at, e.created_at) DESC')
                ->get();

            \Log::info('Student enrollments query', [
                'user_id' => $userId,
                'count' => $enrollments->count()
            ]);

            // Convert to array and ensure proper types
            $enrollmentsArray = $enrollments->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course_id' => $enrollment->course_id,
                    'student_id' => $enrollment->student_id,
                    'status' => $enrollment->status,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'progress_percentage' => (float) $enrollment->progress_percentage,
                    'completed' => (bool) $enrollment->completed,
                    'title' => $enrollment->title,
                    'code' => $enrollment->code,
                    'description' => $enrollment->description,
                    'semester' => $enrollment->semester,
                    'credits' => $enrollment->credits,
                    'instructor_name' => $enrollment->instructor_name,
                    'instructor_email' => $enrollment->instructor_email
                ];
            })->toArray();

            return response()->json(['enrollments' => $enrollmentsArray]);
        } catch (\Exception $e) {
            \Log::error('Error fetching student enrollments: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollments'], 500);
        }
    }

    /**
     * Enroll in a course
     */
    public function enroll(Request $request, $courseId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only students can enroll
            if ($userRole !== 'student') {
                return response()->json(['error' => 'Only students can enroll in courses'], 403);
            }

            // Check if course exists
            $course = DB::table('courses')->where('id', $courseId)->first();
            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            // Check if already enrolled
            $existingEnrollment = DB::table('enrollments')
                ->where('student_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            if ($existingEnrollment) {
                return response()->json(['error' => 'Already enrolled in this course'], 400);
            }

            // Create enrollment
            $enrollmentId = DB::table('enrollments')->insertGetId([
                'student_id' => $userId,
                'course_id' => $courseId,
                'status' => 'active',
                'progress_percentage' => 0,
                'completed' => false,
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Get the enrollment with course details
            $enrollment = DB::table('enrollments as e')
                ->join('courses as c', 'e.course_id', '=', 'c.id')
                ->leftJoin('users as u', 'c.teacher_id', '=', 'u.id')
                ->where('e.id', $enrollmentId)
                ->select(
                    'e.*',
                    'c.name as course_title',
                    'u.name as instructor_name'
                )
                ->first();

            return response()->json([
                'message' => 'Enrolled successfully',
                'enrollment' => $enrollment
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error enrolling in course: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to enroll in course'], 500);
        }
    }
}


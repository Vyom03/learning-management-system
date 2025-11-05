<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseController extends Controller
{
    /**
     * Get all courses (filtered by teacher if teacher is logged in)
     */
    public function index(Request $request)
    {
        $query = DB::table('courses as c')
            ->leftJoin('users as u', 'c.teacher_id', '=', 'u.id')
            ->select(
                'c.id',
                'c.name as title',
                'c.code',
                'c.description',
                'c.semester',
                'c.credits',
                'c.teacher_id as instructor_id',
                'u.name as instructor_name',
                'c.created_at',
                DB::raw('(SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND (status = "active" OR status IS NULL)) as enrollment_count')
                // Note: This subquery is optimized with indexes on enrollments.course_id and enrollments.status
            );

        // If user is authenticated and is a teacher, show only their courses
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            \Log::info('CourseController::index', [
                'user_id' => $userId,
                'user_role' => $userRole
            ]);

            // If teacher, filter to show only their courses
            if ($userRole === 'teacher') {
                $query->where('c.teacher_id', $userId);
            }
            // If student, show only courses they are enrolled in
            elseif ($userRole === 'student') {
                $query->join('enrollments as e', function($join) use ($userId) {
                    $join->on('c.id', '=', 'e.course_id')
                         ->where('e.student_id', '=', $userId)
                         ->where(function($q) {
                             $q->where('e.status', 'active')
                               ->orWhereNull('e.status');
                         });
                });
                \Log::info('Student query filtered by enrollments', ['user_id' => $userId]);
            }
            // Admin sees all courses
        } catch (\Exception $e) {
            \Log::error('CourseController::index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Not authenticated or token invalid - show all courses
        }

        $courses = $query->orderBy('c.created_at', 'desc')->get();

        \Log::info('CourseController::index result', [
            'courses_count' => $courses->count()
        ]);

        return response()->json(['courses' => $courses]);
    }

    /**
     * Get course by ID
     */
    public function show($id)
    {
        $course = DB::table('courses as c')
            ->leftJoin('users as u', 'c.teacher_id', '=', 'u.id')
            ->where('c.id', $id)
            ->select(
                'c.id',
                'c.name as title',
                'c.code',
                'c.description',
                'c.semester',
                'c.credits',
                'c.teacher_id as instructor_id',
                'u.name as instructor_name',
                'u.email as instructor_email',
                'c.created_at'
            )
            ->first();

        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        return response()->json(['course' => $course]);
    }

    /**
     * Create a new course
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string',
            'semester' => 'nullable|string',
            'credits' => 'nullable|integer'
        ]);

        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';

            // Only admins can create courses
            if ($userRole !== 'admin') {
                return response()->json(['error' => 'Permission denied. Only admins can create courses.'], 403);
            }

            $userId = is_object($user) ? $user->id : $user['id'];
            $teacherId = ($userRole === 'admin' && $request->has('instructor_id')) 
                ? $request->instructor_id 
                : $userId;

            $courseId = DB::table('courses')->insertGetId([
                'name' => $request->title,
                'code' => $request->code ?? 'COURSE' . time(),
                'description' => $request->description,
                'teacher_id' => $teacherId,
                'semester' => $request->semester,
                'credits' => $request->credits ?? 3,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $course = DB::table('courses')
                ->where('id', $courseId)
                ->select('id', 'name as title', 'code', 'description', 'semester', 'credits', 'teacher_id as instructor_id', 'created_at')
                ->first();

            return response()->json([
                'message' => 'Course created successfully',
                'course' => $course
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create course'], 500);
        }
    }

    /**
     * Update a course
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string',
            'semester' => 'nullable|string',
            'credits' => 'nullable|integer'
        ]);

        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            $course = DB::table('courses')->where('id', $id)->first();

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            if ($userRole !== 'admin' && $course->teacher_id != $userId) {
                return response()->json(['error' => 'Permission denied'], 403);
            }

            $updates = [];
            if ($request->has('title')) $updates['name'] = $request->title;
            if ($request->has('description')) $updates['description'] = $request->description;
            if ($request->has('code')) $updates['code'] = $request->code;
            if ($request->has('semester')) $updates['semester'] = $request->semester;
            if ($request->has('credits')) $updates['credits'] = $request->credits;
            $updates['updated_at'] = now();

            if (empty($updates)) {
                return response()->json(['error' => 'No fields to update'], 400);
            }

            DB::table('courses')->where('id', $id)->update($updates);

            $updatedCourse = DB::table('courses')
                ->where('id', $id)
                ->select('id', 'name as title', 'code', 'description', 'semester', 'credits', 'teacher_id as instructor_id', 'created_at')
                ->first();

            return response()->json([
                'message' => 'Course updated successfully',
                'course' => $updatedCourse
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update course'], 500);
        }
    }

    /**
     * Delete a course
     */
    public function destroy($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            $course = DB::table('courses')->where('id', $id)->first();

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            if ($userRole !== 'admin' && $course->teacher_id != $userId) {
                return response()->json(['error' => 'Permission denied'], 403);
            }

            DB::table('courses')->where('id', $id)->delete();

            return response()->json(['message' => 'Course deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete course'], 500);
        }
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseController extends Controller
{
    /**
     * Get all courses
     */
    public function index()
    {
        $courses = DB::table('courses as c')
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
                DB::raw('(SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status = "active") as enrollment_count')
            )
            ->orderBy('c.created_at', 'desc')
            ->get();

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
            $userRole = $user->role ?? $user['role'] ?? 'student';

            if (!in_array($userRole, ['teacher', 'admin'])) {
                return response()->json(['error' => 'Permission denied'], 403);
            }

            $teacherId = ($userRole === 'admin' && $request->has('instructor_id')) 
                ? $request->instructor_id 
                : ($user->id ?? $user['id']);

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
            $userRole = $user->role ?? $user['role'] ?? 'student';

            $course = DB::table('courses')->where('id', $id)->first();

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            if ($userRole !== 'admin' && $course->teacher_id != ($user->id ?? $user['id'])) {
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
            $userRole = $user->role ?? $user['role'] ?? 'student';

            $course = DB::table('courses')->where('id', $id)->first();

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            if ($userRole !== 'admin' && $course->teacher_id != ($user->id ?? $user['id'])) {
                return response()->json(['error' => 'Permission denied'], 403);
            }

            DB::table('courses')->where('id', $id)->delete();

            return response()->json(['message' => 'Course deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete course'], 500);
        }
    }
}


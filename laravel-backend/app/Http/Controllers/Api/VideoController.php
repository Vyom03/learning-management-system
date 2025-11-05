<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\TitleFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class VideoController extends Controller
{
    /**
     * Extract YouTube video ID from URL
     */
    private function extractYouTubeId($url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        preg_match($pattern, $url, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Create a new video content
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only teachers can create video content
            if ($userRole !== 'teacher') {
                return response()->json(['error' => 'Only teachers can create video content'], 403);
            }

            $request->validate([
                'course_id' => 'required|integer|exists:courses,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'youtube_url' => 'required|string|max:500',
                'min_watch_time_minutes' => 'nullable|integer|min:1|max:60'
            ]);

            // Extract YouTube ID from URL
            $youtubeId = $this->extractYouTubeId($request->youtube_url);
            if (!$youtubeId) {
                return response()->json(['error' => 'Invalid YouTube URL'], 422);
            }

            // Verify the teacher owns this course
            $course = DB::table('courses')->where('id', $request->course_id)->first();
            if (!$course || $course->teacher_id != $userId) {
                return response()->json(['error' => 'You can only create video content for your own courses'], 403);
            }

            // Format title and description to Title Case
            $formattedTitle = TitleFormatter::formatTitle($request->title);
            $formattedDescription = $request->description ? TitleFormatter::formatDescription($request->description) : null;

            // Create video content
            $videoId = DB::table('video_content')->insertGetId([
                'course_id' => $request->course_id,
                'title' => $formattedTitle,
                'description' => $formattedDescription,
                'youtube_url' => $request->youtube_url,
                'youtube_id' => $youtubeId,
                'min_watch_time_minutes' => $request->min_watch_time_minutes ?? 2,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Fetch the created video
            $video = DB::table('video_content')->where('id', $videoId)->first();

            return response()->json([
                'message' => 'Video content created successfully',
                'video' => $video
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating video content: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create video content: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get videos for a course
     */
    public function getVideosByCourse($courseId)
    {
        try {
            $videos = DB::table('video_content')
                ->where('course_id', $courseId)
                ->select('id', 'title', 'description', 'youtube_id', 'youtube_url', 'min_watch_time_minutes', 'course_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['videos' => $videos]);
        } catch (\Exception $e) {
            \Log::error('Error fetching videos: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch videos'], 500);
        }
    }

    /**
     * Get a single video with watch progress (for students)
     */
    public function show($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            $video = DB::table('video_content')->where('id', $id)->first();

            if (!$video) {
                return response()->json(['error' => 'Video not found'], 404);
            }

            // Get watch progress for students
            $watchProgress = null;
            if ($userRole === 'student') {
                $watchProgress = DB::table('video_watch_progress')
                    ->where('video_content_id', $id)
                    ->where('student_id', $userId)
                    ->first();
            }

            return response()->json([
                'video' => $video,
                'watch_progress' => $watchProgress
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching video: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch video',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update watch progress (for students)
     */
    public function updateWatchProgress(Request $request, $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only students can update watch progress
            if ($userRole !== 'student') {
                return response()->json(['error' => 'Only students can update watch progress'], 403);
            }

            $request->validate([
                'watch_time_seconds' => 'required|integer|min:0'
            ]);

            $video = DB::table('video_content')->where('id', $id)->first();
            if (!$video) {
                return response()->json(['error' => 'Video not found'], 404);
            }

            $minWatchTimeSeconds = $video->min_watch_time_minutes * 60;
            $isCompleted = $request->watch_time_seconds >= $minWatchTimeSeconds;

            // Check if progress exists
            $existingProgress = DB::table('video_watch_progress')
                ->where('video_content_id', $id)
                ->where('student_id', $userId)
                ->first();

            if ($existingProgress) {
                // Update existing progress
                DB::table('video_watch_progress')
                    ->where('id', $existingProgress->id)
                    ->update([
                        'watch_time_seconds' => max($existingProgress->watch_time_seconds, $request->watch_time_seconds),
                        'is_completed' => $isCompleted || $existingProgress->is_completed ? 1 : 0,
                        'last_watched_at' => now(),
                        'updated_at' => now()
                    ]);
            } else {
                // Create new progress
                DB::table('video_watch_progress')->insert([
                    'video_content_id' => $id,
                    'student_id' => $userId,
                    'watch_time_seconds' => $request->watch_time_seconds,
                    'is_completed' => $isCompleted ? 1 : 0,
                    'last_watched_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Fetch updated progress
            $watchProgress = DB::table('video_watch_progress')
                ->where('video_content_id', $id)
                ->where('student_id', $userId)
                ->first();

            return response()->json([
                'message' => 'Watch progress updated',
                'watch_progress' => $watchProgress,
                'is_completed' => $watchProgress->is_completed == 1
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating watch progress: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update watch progress',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's video progress for a course
     */
    public function getStudentProgress($courseId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only students can get their own progress
            if ($userRole !== 'student') {
                return response()->json(['error' => 'Only students can view their progress'], 403);
            }

            $progress = DB::table('video_watch_progress as vwp')
                ->join('video_content as vc', 'vwp.video_content_id', '=', 'vc.id')
                ->where('vc.course_id', $courseId)
                ->where('vwp.student_id', $userId)
                ->select('vwp.*', 'vc.title as video_title')
                ->get();

            return response()->json(['progress' => $progress]);
        } catch (\Exception $e) {
            \Log::error('Error fetching student progress: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch progress'], 500);
        }
    }

    /**
     * Get video analytics - student watch statistics (for teachers/admins)
     */
    public function getVideoAnalytics($id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only teachers and admins can view analytics
            if (!in_array($userRole, ['teacher', 'admin'])) {
                return response()->json(['error' => 'Only teachers and admins can view video analytics'], 403);
            }

            $video = DB::table('video_content')->where('id', $id)->first();
            if (!$video) {
                return response()->json(['error' => 'Video not found'], 404);
            }

            // Check if teacher owns this course (admins can see all)
            if ($userRole === 'teacher') {
                $course = DB::table('courses')->where('id', $video->course_id)->first();
                if (!$course || $course->teacher_id != $userId) {
                    return response()->json(['error' => 'You can only view analytics for your own courses'], 403);
                }
            }

            // Get all watch progress for this video
            $watchProgress = DB::table('video_watch_progress as vwp')
                ->join('users as u', 'vwp.student_id', '=', 'u.id')
                ->where('vwp.video_content_id', $id)
                ->select(
                    'vwp.id',
                    'vwp.student_id',
                    'u.name as student_name',
                    'u.email as student_email',
                    'vwp.watch_time_seconds',
                    'vwp.is_completed',
                    'vwp.last_watched_at',
                    'vwp.created_at'
                )
                ->orderBy('vwp.watch_time_seconds', 'desc')
                ->get();

            // Calculate statistics
            $totalStudents = $watchProgress->count();
            $completedStudents = $watchProgress->where('is_completed', 1)->count();
            $totalWatchTime = $watchProgress->sum('watch_time_seconds');
            $averageWatchTime = $totalStudents > 0 ? round($totalWatchTime / $totalStudents) : 0;

            return response()->json([
                'video' => $video,
                'statistics' => [
                    'total_students_watched' => $totalStudents,
                    'completed_students' => $completedStudents,
                    'total_watch_time_seconds' => $totalWatchTime,
                    'average_watch_time_seconds' => $averageWatchTime,
                    'completion_rate' => $totalStudents > 0 ? round(($completedStudents / $totalStudents) * 100, 2) : 0
                ],
                'watch_progress' => $watchProgress
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching video analytics: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch video analytics',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}


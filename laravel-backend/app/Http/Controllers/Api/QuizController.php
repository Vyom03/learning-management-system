<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuizController extends Controller
{
    /**
     * Create a new quiz
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only teachers can create quizzes
            if ($userRole !== 'teacher') {
                return response()->json(['error' => 'Only teachers can create quizzes'], 403);
            }

            $request->validate([
                'course_id' => 'required|integer|exists:courses,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'questions' => 'required|array|min:1',
                'questions.*.question' => 'required|string',
                'questions.*.options' => 'required|array|min:2',
                'questions.*.correct_answer' => 'required|integer|min:0',
                'questions.*.points' => 'required|integer|min:1'
            ]);

            // Verify the teacher owns this course
            $course = DB::table('courses')->where('id', $request->course_id)->first();
            if (!$course || $course->teacher_id != $userId) {
                return response()->json(['error' => 'You can only create quizzes for your own courses'], 403);
            }

            // Create quiz
            $quizId = DB::table('quizzes')->insertGetId([
                'course_id' => $request->course_id,
                'title' => $request->title,
                'description' => $request->description ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create questions
            foreach ($request->questions as $question) {
                DB::table('quiz_questions')->insert([
                    'quiz_id' => $quizId,
                    'question' => $question['question'],
                    'options' => json_encode($question['options']),
                    'correct_answer' => $question['correct_answer'],
                    'points' => $question['points'] ?? 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Fetch the created quiz
            $quiz = DB::table('quizzes')->where('id', $quizId)->first();

            return response()->json([
                'message' => 'Quiz created successfully',
                'quiz' => $quiz
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating quiz: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create quiz: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get quizzes for a course
     */
    public function getQuizzesByCourse($courseId)
    {
        try {
            $quizzes = DB::table('quizzes')
                ->where('course_id', $courseId)
                ->select('id', 'title', 'description', 'course_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['quizzes' => $quizzes]);
        } catch (\Exception $e) {
            \Log::error('Error fetching quizzes: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch quizzes'], 500);
        }
    }

    /**
     * Get a single quiz with questions
     */
    public function show($id)
    {
        try {
            $quiz = DB::table('quizzes')->where('id', $id)->first();

            if (!$quiz) {
                \Log::warning('Quiz not found', ['quiz_id' => $id]);
                return response()->json(['error' => 'Quiz not found'], 404);
            }

            $questions = DB::table('quiz_questions')
                ->where('quiz_id', $id)
                ->select('id', 'question', 'options', 'correct_answer', 'points')
                ->orderBy('id', 'asc')
                ->get();

            // Handle JSON options - MySQL JSON columns return as arrays, TEXT columns as strings
            $questions = $questions->map(function($q) {
                // If options is already an array (from JSON column), encode it
                if (is_array($q->options)) {
                    $q->options = json_encode($q->options);
                } elseif (is_string($q->options)) {
                    // If it's a string, try to decode and re-encode to ensure valid JSON
                    $decoded = json_decode($q->options, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $q->options = json_encode($decoded);
                    } else {
                        // Invalid JSON, use empty array
                        $q->options = json_encode([]);
                    }
                } else {
                    // Fallback to empty array
                    $q->options = json_encode([]);
                }
                return $q;
            });

            \Log::info('Quiz loaded successfully', [
                'quiz_id' => $id,
                'questions_count' => $questions->count()
            ]);

            return response()->json([
                'quiz' => $quiz,
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching quiz: ' . $e->getMessage(), [
                'quiz_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to fetch quiz',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit quiz answers and get results
     */
    public function submit(Request $request, $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only students can submit quizzes
            if ($userRole !== 'student') {
                return response()->json(['error' => 'Only students can submit quizzes'], 403);
            }

            $request->validate([
                'answers' => 'required|array'
            ]);

            // Get quiz and questions
            $quiz = DB::table('quizzes')->where('id', $id)->first();
            if (!$quiz) {
                return response()->json(['error' => 'Quiz not found'], 404);
            }

            $questions = DB::table('quiz_questions')
                ->where('quiz_id', $id)
                ->select('id', 'question', 'options', 'correct_answer', 'points')
                ->orderBy('id', 'asc')
                ->get();

            if ($questions->count() === 0) {
                return response()->json(['error' => 'Quiz has no questions'], 400);
            }

            // Grade the quiz
            $score = 0;
            $totalPoints = 0;
            $answers = $request->answers;

            foreach ($questions as $index => $question) {
                $totalPoints += $question->points;
                
                if (isset($answers[$index]) && $answers[$index] == $question->correct_answer) {
                    $score += $question->points;
                }
            }

            $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;

            // Save quiz attempt
            try {
                $attemptId = DB::table('quiz_attempts')->insertGetId([
                    'quiz_id' => $id,
                    'student_id' => $userId,
                    'score' => $score,
                    'total_points' => $totalPoints,
                    'percentage' => $percentage,
                    'answers' => json_encode($answers),
                    'completed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                // If quiz_attempts table doesn't exist, log but still return results
                \Log::warning('Could not save quiz attempt (table may not exist): ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Quiz submitted successfully',
                'result' => [
                    'score' => $score,
                    'totalPoints' => $totalPoints,
                    'percentage' => $percentage
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error submitting quiz: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to submit quiz',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}


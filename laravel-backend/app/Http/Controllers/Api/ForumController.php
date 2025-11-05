<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ForumController extends Controller
{
    /**
     * Get all topics for a course
     */
    public function getTopics($courseId)
    {
        try {
            $topics = DB::table('forum_topics as ft')
                ->leftJoin('users as u', 'ft.author_id', '=', 'u.id')
                ->where('ft.course_id', $courseId)
                ->select(
                    'ft.id',
                    'ft.title',
                    'ft.content',
                    'ft.course_id',
                    'ft.author_id',
                    'u.name as author_name',
                    'ft.created_at',
                    DB::raw('(SELECT COUNT(*) FROM forum_replies WHERE topic_id = ft.id) as reply_count')
                )
                ->orderBy('ft.created_at', 'desc')
                ->get();

            return response()->json(['topics' => $topics]);
        } catch (\Exception $e) {
            \Log::error('Error fetching forum topics: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch topics'], 500);
        }
    }

    /**
     * Get a single topic with replies
     */
    public function getTopic($topicId)
    {
        try {
            $topic = DB::table('forum_topics as ft')
                ->leftJoin('users as u', 'ft.author_id', '=', 'u.id')
                ->where('ft.id', $topicId)
                ->select(
                    'ft.id',
                    'ft.title',
                    'ft.content',
                    'ft.course_id',
                    'ft.author_id',
                    'u.name as author_name',
                    'ft.created_at'
                )
                ->first();

            if (!$topic) {
                return response()->json(['error' => 'Topic not found'], 404);
            }

            // Get replies
            $replies = DB::table('forum_replies as fr')
                ->leftJoin('users as u', 'fr.author_id', '=', 'u.id')
                ->where('fr.topic_id', $topicId)
                ->select(
                    'fr.id',
                    'fr.content',
                    'fr.topic_id',
                    'fr.author_id',
                    'u.name as author_name',
                    'fr.created_at'
                )
                ->orderBy('fr.created_at', 'asc')
                ->get();

            return response()->json([
                'topic' => $topic,
                'replies' => $replies
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching forum topic: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch topic'], 500);
        }
    }

    /**
     * Create a new topic
     */
    public function createTopic(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $userId = is_object($user) ? $user->id : $user['id'];

            $request->validate([
                'course_id' => 'required|integer|exists:courses,id',
                'title' => 'required|string|max:255',
                'content' => 'required|string'
            ]);

            $topicId = DB::table('forum_topics')->insertGetId([
                'course_id' => $request->course_id,
                'author_id' => $userId,
                'title' => $request->title,
                'content' => $request->content,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Fetch the created topic with author info
            $topic = DB::table('forum_topics as ft')
                ->leftJoin('users as u', 'ft.author_id', '=', 'u.id')
                ->where('ft.id', $topicId)
                ->select(
                    'ft.id',
                    'ft.title',
                    'ft.content',
                    'ft.course_id',
                    'ft.author_id',
                    'u.name as author_name',
                    'ft.created_at',
                    DB::raw('0 as reply_count')
                )
                ->first();

            return response()->json([
                'message' => 'Topic created successfully',
                'topic' => $topic
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating forum topic: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create topic'], 500);
        }
    }

    /**
     * Create a reply to a topic
     */
    public function createReply(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $userId = is_object($user) ? $user->id : $user['id'];

            $request->validate([
                'topic_id' => 'required|integer|exists:forum_topics,id',
                'content' => 'required|string'
            ]);

            $replyId = DB::table('forum_replies')->insertGetId([
                'topic_id' => $request->topic_id,
                'author_id' => $userId,
                'content' => $request->content,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Fetch the created reply with author info
            $reply = DB::table('forum_replies as fr')
                ->leftJoin('users as u', 'fr.author_id', '=', 'u.id')
                ->where('fr.id', $replyId)
                ->select(
                    'fr.id',
                    'fr.content',
                    'fr.topic_id',
                    'fr.author_id',
                    'u.name as author_name',
                    'fr.created_at'
                )
                ->first();

            return response()->json([
                'message' => 'Reply posted successfully',
                'reply' => $reply
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating forum reply: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to post reply'], 500);
        }
    }
}


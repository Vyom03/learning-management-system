<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,teacher'
        ]);

        // Check if user exists
        $userExists = DB::table('users')
            ->where('email', $request->email)
            ->exists();

        if ($userExists) {
            return response()->json(['error' => 'User already exists'], 400);
        }

        // Hash password
        $hashedPassword = Hash::make($request->password);

        // Insert user
        $userId = DB::table('users')->insertGetId([
            'email' => $request->email,
            'password' => $hashedPassword,
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Assign role using Spatie Permission structure
        $role = DB::table('roles')->where('name', $request->role)->first();
        if ($role) {
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId
            ]);
        }

        // Get user model for token generation
        $user = \App\Models\User::find($userId);
        
        // Generate token with custom claims
        $customClaims = ['role' => $request->role];
        $token = JWTAuth::customClaims($customClaims)->fromUser($user);

        return response()->json([
            'message' => 'User created successfully',
            'token' => $token,
            'user' => [
                'id' => $userId,
                'email' => $request->email,
                'name' => $request->name,
                'role' => $request->role
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Get user with role from MySQL database
        $user = DB::table('users as u')
            ->leftJoin('model_has_roles as mhr', function($join) {
                $join->on('u.id', '=', 'mhr.model_id')
                     ->where('mhr.model_type', '=', 'App\\Models\\User');
            })
            ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
            ->where('u.email', $request->email)
            ->select('u.*', 'r.name as role')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // If no role found, default to 'student'
        $userRole = $user->role ?? 'student';

        // Get user model for token generation
        $userModel = \App\Models\User::find($user->id);
        
        // Create token with custom claims
        $token = JWTAuth::customClaims([
            'role' => $userRole
        ])->fromUser($userModel);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $userRole,
                'grade_level' => $user->grade_level ?? null
            ]
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $userId = is_object($user) ? $user->id : $user['id'];
            
            // Get user with role from database
            $userData = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', function($join) {
                    $join->on('u.id', '=', 'mhr.model_id')
                         ->where('mhr.model_type', '=', 'App\\Models\\User');
                })
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->where('u.id', $userId)
                ->select('u.id', 'u.email', 'u.name', 'u.grade_level', 'u.created_at', 'r.name as role')
                ->first();

            if (!$userData) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json([
                'user' => [
                    'id' => $userData->id,
                    'email' => $userData->email,
                    'name' => $userData->name,
                    'role' => $userData->role ?? 'student',
                    'grade_level' => $userData->grade_level ?? null,
                    'created_at' => $userData->created_at
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid or expired token'], 403);
        }
    }
}


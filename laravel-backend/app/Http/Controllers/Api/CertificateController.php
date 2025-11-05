<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class CertificateController extends Controller
{
    /**
     * Get student's certificates
     */
    public function myCertificates(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();
            $userRole = $payload->get('role') ?? 'student';
            $userId = is_object($user) ? $user->id : $user['id'];

            // Only students can view their certificates
            if ($userRole !== 'student') {
                return response()->json(['error' => 'Only students can view certificates'], 403);
            }

            // Check if certificates table exists
            try {
                $certificates = DB::table('certificates as c')
                    ->join('courses as co', 'c.course_id', '=', 'co.id')
                    ->where('c.student_id', $userId)
                    ->select(
                        'c.id',
                        'c.course_id',
                        'c.student_id',
                        'c.certificate_number',
                        'c.issued_at',
                        'c.created_at',
                        'co.name as course_title'
                    )
                    ->orderBy('c.issued_at', 'desc')
                    ->get();

                return response()->json(['certificates' => $certificates]);
            } catch (\Exception $e) {
                // If certificates table doesn't exist, return empty array
                \Log::info('Certificates table does not exist yet');
                return response()->json(['certificates' => []]);
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching certificates: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch certificates'], 500);
        }
    }
}


<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DeviceSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|max:255',
            'password' => 'required|max:255'
        ]);

        $user = User::query()
            ->where('email', $request->email)
            ->orWhere('phone_number', $request->email)
            ->with([
                'designation',
                'department.super_department',
                'shift',
                'assinged_role',
                'managed_employees'
            ])
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        // Track device session
        $userAgent = $request->header('User-Agent', '');
        $ipAddress = $request->ip();

        $deviceSession = DeviceSession::createOrUpdateSession(
            $user,
            $userAgent,
            $ipAddress,
            $tokenResult->accessToken
        );

        return response()->json([
            'user' => $user,
            'live_location_track' => $user->live_location_track,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'device_session_id' => $deviceSession->id,
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->auth_user;

        // Find and terminate current device session
        $userAgent = $request->header('User-Agent', '');
        $deviceId = DeviceSession::generateDeviceId($userAgent, $request->ip());

        $deviceSession = DeviceSession::where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->first();

        if ($deviceSession) {
            $deviceSession->terminate();
        } else {
            // Fallback: delete all tokens for this user
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function user(Request $request)
    {
        return ApiResponseClass::sendResponse([
            'user' => $request->auth_user,
            'live_location_track' => $request->auth_user->live_location_track,
        ], 'You are logged in!');
    }

    function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|max:50',
            'password' => 'required|min:6|confirmed',
        ]);

        if (Hash::check($request->old_password, $request->auth_user->password)) {
            $request->auth_user->password = Hash::make($request->password);
            $request->auth_user->save();
            return ApiResponseClass::sendResponse([], 'Password changed successfully');
        }
        return abort(401, 'The provided credentials are incorrect.');
    }

    /**
     * Get user's active device sessions
     */
    public function getDeviceSessions(Request $request)
    {
        $user = $request->auth_user;
        $sessions = DeviceSession::getActiveSessions($user);

        return ApiResponseClass::sendResponse([
            'device_sessions' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'device_name' => $session->device_name,
                    'device_type' => $session->device_type,
                    'platform' => $session->platform,
                    'browser' => $session->browser,
                    'ip_address' => $session->ip_address,
                    'last_activity' => $session->last_activity->diffForHumans(),
                    'last_activity_date' => $session->last_activity->format('M j, Y g:i A'),
                    'is_current' => $session->is_current,
                    'created_at' => $session->created_at->format('M j, Y g:i A'),
                ];
            })
        ], 'Device sessions retrieved successfully');
    }

    /**
     * Terminate a specific device session
     */
    public function terminateDeviceSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:device_sessions,id'
        ]);

        $user = $request->auth_user;
        $sessionId = $request->session_id;

        $deviceSession = DeviceSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$deviceSession) {
            return response()->json([
                'message' => 'Device session not found or unauthorized.'
            ], 404);
        }

        // Don't allow terminating current session
        if ($deviceSession->is_current) {
            return response()->json([
                'message' => 'Cannot terminate current session. Use logout instead.'
            ], 400);
        }

        $deviceSession->terminate();

        return ApiResponseClass::sendResponse([], 'Device session terminated successfully');
    }

    /**
     * Terminate all other device sessions except current
     */
    public function terminateOtherDeviceSessions(Request $request)
    {
        $user = $request->auth_user;
        $currentSession = $request->device_session;

        $terminatedCount = 0;
        $otherSessions = DeviceSession::where('user_id', $user->id)
            ->where('id', '!=', $currentSession->id)
            ->get();

        foreach ($otherSessions as $session) {
            $session->terminate();
            $terminatedCount++;
        }

        return ApiResponseClass::sendResponse([
            'terminated_count' => $terminatedCount
        ], "Successfully terminated {$terminatedCount} device sessions");
    }
}

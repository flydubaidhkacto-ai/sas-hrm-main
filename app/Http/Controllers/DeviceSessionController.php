<?php

namespace App\Http\Controllers;

use App\Models\DeviceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;

class DeviceSessionController extends Controller
{
    /**
     * Display active device sessions for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $sessions = DeviceSession::getActiveSessions($user);

        return view('device-sessions.index', compact('sessions', 'user'));
    }

    /**
     * Get active device sessions as JSON for AJAX requests
     */
    public function getActiveSessions(Request $request)
    {
        $user = Auth::user();
        $sessions = DeviceSession::getActiveSessions($user);

        return response()->json([
            'success' => true,
            'sessions' => $sessions->map(function ($session) {
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
        ]);
    }

    /**
     * Terminate a specific device session
     */
    public function terminate(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:device_sessions,id'
        ]);

        $user = Auth::user();
        $sessionId = $request->session_id;

        $deviceSession = DeviceSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$deviceSession) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device session not found or unauthorized.'
                ], 404);
            }

            Toastr::error('Device session not found or unauthorized.');
            return back();
        }

        // Don't allow terminating current session
        if ($deviceSession->is_current) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot terminate current session. Use logout instead.'
                ], 400);
            }

            Toastr::error('Cannot terminate current session. Use logout instead.');
            return back();
        }

        $deviceSession->terminate();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Device session terminated successfully.'
            ]);
        }

        Toastr::success('Device session terminated successfully.');
        return back();
    }

    /**
     * Terminate all other device sessions except current
     */
    public function terminateOthers(Request $request)
    {
        $user = Auth::user();
        $userAgent = $request->header('User-Agent', '');
        $currentDeviceId = DeviceSession::generateDeviceId($userAgent, $request->ip());

        $terminatedCount = 0;
        $otherSessions = DeviceSession::where('user_id', $user->id)
            ->where('device_id', '!=', $currentDeviceId)
            ->get();

        foreach ($otherSessions as $session) {
            $session->terminate();
            $terminatedCount++;
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Successfully terminated {$terminatedCount} device sessions.",
                'terminated_count' => $terminatedCount
            ]);
        }

        Toastr::success("Successfully terminated {$terminatedCount} device sessions.");
        return back();
    }
}

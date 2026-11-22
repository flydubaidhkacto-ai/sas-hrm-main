<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Models\DeviceSession;


class AuthMobileApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. Token missing.'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Unauthorized. Invalid token.'], 401);
        }

        $user = User::find($accessToken->tokenable_id);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized. User not found.'], 401);
        }

        // Check if this token has a valid device session
        $deviceSession = DeviceSession::where('personal_access_token_id', $accessToken->id)->first();

        if (!$deviceSession) {
            // Token exists but no device session - create one for backward compatibility
            $userAgent = $request->header('User-Agent', '');
            $ipAddress = $request->ip();

            $deviceSession = DeviceSession::createOrUpdateSession(
                $user,
                $userAgent,
                $ipAddress,
                $accessToken
            );
        } else {
            // Update last activity for existing device session
            $deviceSession->update([
                'last_activity' => now(),
                'ip_address' => $request->ip(),
            ]);
        }

        // Check if device session is too old (optional security measure)
        if ($deviceSession->last_activity < now()->subDays(30)) {
            $deviceSession->terminate();
            return response()->json(['message' => 'Session expired. Please login again.'], 401);
        }

        $user->load('assinged_role');
        $request->merge([
            'auth_user' => $user,
            'device_session' => $deviceSession
        ]);

        return $next($request);
    }
}

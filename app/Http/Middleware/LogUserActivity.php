<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        // always allow request first safety
        $response = $next($request);

        try {

            $user = $request->user();

            if (!$user) {
                return $response;
            }

            $method = $request->getMethod();

            UserActivityLog::create([
                'model_name' => $request->url(),
                'status' => match($method) {
                'POST' => 1,
                'PUT', 'PATCH' => 2,
                'DELETE' => 3,
                default => 0,
            },
            'visited_section_name' => null,
            'operation_data' => in_array($method, ['POST','PUT','PATCH','DELETE'])
                ? json_encode($request->all())
                : null,
            'user_id' => $user->id,
        ]);

    } catch (\Throwable $e) {
            // NEVER break application
            logger()->error('ActivityLog failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $response;
    }
}

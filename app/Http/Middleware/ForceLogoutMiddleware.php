<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ForceLogoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next)
  {
      $user = $request->user();

      if (!$user) {
          return $next($request); // 👈 IMPORTANT FIX
      }

      if (!$user->is_active) {
          return abort(401, 'Inactive user');
      }

      return $next($request);
  }
}

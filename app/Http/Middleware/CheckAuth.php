<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, String $function1, String $function2): Response
    {
        if (!Auth::guard('sanctum')->check()) {
            $request->merge(['action' => $function1]);
            // Don't try to get user info if not authenticated
        } else {
            $request->merge(['action' => $function2]);
            $user = Auth::guard('sanctum')->user();
            // Only merge user info if authenticated
            $request->merge(['user' => $user]);
            
            // Uncomment and adjust this if you want to check for specific abilities
            // if (!$user->tokenCan('access-api')) {
            //     return response()->json(['message' => 'Insufficient permissions'], 403);
            // }
        }

        return $next($request);
    }
}

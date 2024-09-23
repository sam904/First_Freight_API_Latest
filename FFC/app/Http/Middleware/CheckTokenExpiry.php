<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckTokenExpiry
{
    public function handle($request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        // Check if it contains a Bearer token
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $bearerToken = $matches[1]; // The token itself
        } else {
            return response()->json(['status' => false, 'error' => 'Token not provided'], 401);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'error' => 'Unauthorized'], 401);
        }

        if ($user->access_token == $bearerToken) {
            // Compare the current time with access token expiry
            if (Carbon::now()->greaterThan($user->access_token_expires_at)) {
                return response()->json(['status' => false, 'error' => 'Access token has expired.'], 401);
            }

            // If access token is valid, allow request to proceed
            return $next($request);
        } else {
            return response()->json(['status' => false, 'error' => 'Token not found'], 401);
        }
    }

    public function handle_old_working($request, Closure $next)
    {
        $user = $request->user();
        $token = $user->token();

        if (Carbon::now()->greaterThan(Carbon::parse($token->expires_at))) {
            return response()->json(['status' => false, 'error' => 'Token has expired'], 401);
        }

        return $next($request);
    }
}

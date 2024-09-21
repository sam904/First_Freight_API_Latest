<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckTokenExpiry
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Retrieve the last stored token data for the user
        $tokenData = DB::table('tokens')->where('user_id', $user->id)->orderBy('access_token_expires_at', 'desc')->first();

        Log::info($tokenData->id);

        if (!$tokenData) {
            return response()->json(['error' => 'Token not found'], 401);
        }

        // Compare the current time with access token expiry
        if (Carbon::now()->greaterThan($tokenData->access_token_expires_at)) {
            return response()->json(['error' => 'Access token has expired.'], 401);
        }

        // If access token is valid, allow request to proceed
        return $next($request);
    }

    public function handle_old_working($request, Closure $next)
    {
        $user = $request->user();
        $token = $user->token();

        if (Carbon::now()->greaterThan(Carbon::parse($token->expires_at))) {
            return response()->json(['error' => 'Token has expired'], 401);
        }

        return $next($request);
    }
}

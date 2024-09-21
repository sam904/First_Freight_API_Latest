<?php

namespace App\Services\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TokenService
{

    public function generateToken($user)
    {
        // Generate access token with expiry (1 hour here)
        $accessTokenResult = $user->createToken('Personal Access Token');
        $accessToken = $accessTokenResult->accessToken;
        $accessTokenExpiry = Carbon::now()->addDays(1);


        // Generate refresh token (15 days expiry here)
        $refreshTokenResult = $user->createToken('Personal Access Token');
        $refreshToken = $refreshTokenResult->accessToken; // Simulate refresh token (you can use Passport's refresh token)
        $refreshTokenExpiry = Carbon::now()->addDays(30);

        try {
            // Store tokens in the database
            $tokensLatestId  = DB::table('tokens')->insertGetId([
                'user_id' => $user->id,
                'access_token' => $accessToken,
                'access_token_expires_at' => $accessTokenExpiry,
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => $refreshTokenExpiry,
                'updated_at' => Carbon::now()
            ]);

            //now update all token status as deactivate except latesid
            DB::table('tokens')->where('id', '!=', $tokensLatestId)->update([
                'status' => 'deactivated',
                'updated_at' => Carbon::now(),
            ]);

            return [
                'access_token' => $accessToken,
                'access_token_expires_at' => $accessTokenExpiry,
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => $refreshTokenExpiry,
            ];
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function refreshToken($refreshToken)
    {
        $tokenData = DB::table('tokens')->where('refresh_token', $refreshToken)->orderBy('refresh_token_expires_at', 'desc')->first();

        if (!$tokenData) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }

        // Check if the refresh token has expired
        if (Carbon::now()->greaterThan($tokenData->refresh_token_expires_at)) {
            return response()->json(['error' => 'Refresh token has expired'], 401);
        }

        // Generate new access token and update the database
        $user = User::find($tokenData->user_id);
        $accessTokenResult = $user->createToken('Personal Access Token');
        $newAccessToken = $accessTokenResult->accessToken;
        $newAccessTokenExpiry = Carbon::now()->addDays(1);

        // Generate refresh token (15 days expiry here)
        $refreshTokenResult = $user->createToken('Personal Access Token');
        $refreshToken = $refreshTokenResult->accessToken; // Simulate refresh token (you can use Passport's refresh token)
        $refreshTokenExpiry = Carbon::now()->addDays(30);

        try {
            DB::table('tokens')->where('user_id', $user->id)->update([
                'access_token' => $newAccessToken,
                'access_token_expires_at' => $newAccessTokenExpiry,
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => $refreshTokenExpiry,
                'updated_at' => Carbon::now(),
            ]);
            return [
                'access_token' => $newAccessToken,
                'access_token_expires_at' => $newAccessTokenExpiry,
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => $refreshTokenExpiry,
            ];
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

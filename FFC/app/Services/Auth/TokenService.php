<?php

namespace App\Services\Auth;

use App\Models\User;
use Exception;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Log;

class TokenService
{

    public function generateToken($user)
    {
        $firstName = $user->first_name;
        // Generate access token with expiry (1 days here)
        $accessTokenResult = $user->createToken('Personal Access Token');
        $accessToken = $accessTokenResult->accessToken;
        $accessTokenExpiry = Carbon::now()->addDays(1);

        // Generate refresh token (30 days expiry here)
        $refreshTokenResult = $user->createToken('Personal Access Token}');
        $refreshToken = $refreshTokenResult->accessToken;
        $refreshTokenExpiry = Carbon::now()->addDays(30);

        try {
            $user->update([
                'access_token' => $accessToken,
                'access_token_expires_at' => $accessTokenExpiry,
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => $refreshTokenExpiry,
            ]);
            return $user;
        } catch (Exception $e) {
            return $e;
        }
    }

    public function refreshToken($refreshToken)
    {
        $user = User::where('refresh_token', $refreshToken)->first();

        // Check if user exists and if the refresh token matches securely
        if (!$user || !hash_equals($user->refresh_token, $refreshToken)) {
            return ['error' => 'Invalid refresh token'];
        }

        if (Carbon::now()->greaterThan($user->refresh_token_expires_at)) {
            return ['error' => 'Refresh token has expired'];
        }
        return $this->generateToken($user);
    }
}

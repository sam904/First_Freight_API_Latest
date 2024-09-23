<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\Auth\TokenService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }

        // Determine whether the input is an email or username
        //$loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Create login credentials array
        $credentials = [
            'email' => $request->username,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        try {
            $tokenData = $this->tokenService->generateToken($user);
            return response()->json($tokenData, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }

        $refreshToken = $request->refresh_token;

        $tokenData = $this->tokenService->refreshToken($refreshToken);
        // Check if the result contains an error
        if (isset($tokenData['error'])) {
            return response()->json(['status' => false, 'error' => $tokenData['error']], 401); // Return only the error message
        }
        return response()->json($tokenData, 200);
    }

    public function test()
    {
        return response()->json("it working...");
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }
}

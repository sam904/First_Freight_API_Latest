<?php

namespace App\Http\Controllers\Api;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\Auth\TokenService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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
            return response()->json([
                'status' => false,
                'error' => $e->errors()
            ], 422);
        }

        // Determine whether the input is an email or username
        // $loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Check if the user exists
        $user = User::where('email', $request->username)->first();

        // Check if user exists and is inactive
        if ($user && $user->status != "activated") {
            return response()->json([
                'status' => false,
                'message' => 'Your account is inactive. Please contact support'
            ], 401);
        }

        // Create login credentials array
        $credentials = [
            'email' => $request->username,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        try {
            $tokenData = $this->tokenService->generateToken($user);
            return response()->json([
                'status' => true,
                'message' => 'User login successfully',
                'data' => $tokenData
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->errors()
            ], 422);
        }

        $refreshToken = $request->refresh_token;

        $tokenData = $this->tokenService->refreshToken($refreshToken);

        // Check if the result contains an error
        if (isset($tokenData['errorMsg'])) {
            return response()->json([
                'status' => false,
                'message' => $tokenData['errorMsg']
            ], 401); // Return only the error message
        }

        return response()->json([
            'status' => true,
            'message' => 'New refresh token generated',
            'data' => $tokenData
        ], 200);
    }

    public function test()
    {
        return response()->json("it working...");
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['status' => true, 'message' => 'User successfully logged out']);
    }


    public function sendOtp($userId)
    {
        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Generate a random 6-digit OTP
        $otpCode = rand(100000, 999999);

        // Set OTP expiration (e.g., 5 minutes)
        $expirationTime = Carbon::now()->addMinutes(5);

        // Store the OTP in the database
        Otp::updateOrCreate(
            [
                'user_id'    => $user->id,
            ],
            [
                'otp'        => $otpCode,
                'expires_at' => $expirationTime,
                'is_verified' => false,
            ]
        );

        // Send the OTP to the user's email
        Mail::to($user->email)->send(new OtpMail($user, $otpCode));

        // Respond with success message
        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully to your email.'
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|digits:6',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->errors()
            ], 422);
        }

        $otpEntry = Otp::where('otp', $request->otp)->first();

        if (!$otpEntry) {
            return response()->json(['status' => false, 'message' => 'Otp not found'], 404);
        }

        //fetch user details
        $user = User::where('id', $otpEntry->user_id)->first();

        // Check if the OTP matches and is still valid 
        if ($otpEntry->expires_at > now()) {
            // OTP is valid
            $otpEntry->update([
                'is_verified' => true,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'OTP validated successfully',
                'user_id' => $user->id,
                'access_token' => $user->access_token
            ], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'OTP expired'], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'error' => $e->errors()], 422);
        }

        $user = User::where('email', $request->username)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User email-id is not registered'], 404);
        }

        // Send the OTP to the user's email
        return $this->sendOtp($user->id);
    }

    public function verifyResetPassword(Request $request)
    {
        return $this->verifyOtp($request);
    }

    /**
     * Summary of updateResetPassword
     * Passed only userId related Access Token else It will not Update Passoword
     */
    public function updateResetPassword(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required' => 'The new password is required',
            'new_password.min' => 'The new password must be at least 8 characters long',
            'new_password.confirmed' => 'The password confirmation does not match',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->errors()], 422); // Return validation errors with a 422 status code
        }

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'messasge' => 'User not found'], 404);
        }

        // Based on Access Token Update the User's Password
        $authHeader = $request->header('Authorization');

        // Check if it contains a Bearer token
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $bearerToken = $matches[1]; // The token itself
        } else {
            return response()->json(['status' => false, 'message' => 'Token not provided'], 401);
        }

        if ($user->access_token == $bearerToken) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            return response()->json(['status' => true, 'message' => 'Password updated successfully'], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Unauthorized Token passed for user : ' . $user->email], 404);
        }
    }
}

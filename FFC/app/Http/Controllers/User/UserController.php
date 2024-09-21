<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::paginate(10);

        return response()->json($users);
    }


    // Register a new user
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'username' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                            $fail('The ' . $attribute . ' must be a valid email or username.');
                        }
                    },
                ],
                //'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'mobile_number' => 'required|string|min:10|max:15|unique:users', // Add mobile number validation
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }

        // Determine whether the input is an email or username
        $loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            $loginType => $request->username,
            'mobile_number' => $validatedData['mobile_number'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return response()->json(['message' => 'User registered successfully!']);
    }
}

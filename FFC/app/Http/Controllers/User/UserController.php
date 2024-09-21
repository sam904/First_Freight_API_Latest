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
        //pagination
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
                // 'username' => ['required',function ($attribute, $value, $fail) { if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {$fail('The ' . $attribute . ' must be a valid email or username.');}},],
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'mobile_number' => 'required|string|min:10|max:15|unique:users', // Add mobile number validation
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 500);
        }

        // Determine whether the input is an email or username
        //$loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if ($image = $request->file('profile_image')) {
            $destinationPath = 'images/profiles/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $validatedData['profile_image'] = "$profileImage";
        }

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['username'],
            //$loginType => $request->username,
            'mobile_number' => $validatedData['mobile_number'],
            'password' => Hash::make($validatedData['password']),
            'profile_image' => $validatedData['profile_image'],
        ]);

        return response()->json(['message' => 'User registered successfully!']);
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
                'mobile_number' => 'required|string|min:10|max:15', // Add mobile number validation
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 500);
        }

        if ($image = $request->file('profile_image')) {
            $destinationPath = 'images/profiles/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $validatedData['profile_image'] = "$profileImage";
        } else {
            unset($validatedData['profile_image']);
        }

        $user = User::findOrFail($id);

        // Update the task with the validated data
        $update = $user->update($validatedData);

        if ($update) {
            return response()->json(['message' => 'User updated successfully.']);
        }

        return abort(500); //Return server error if user update fails
    }


    public function destroy($id)
    {

        $user = User::findOrFail($id);
        $delete = $user->delete($id);

        if ($delete) {
            return response()->json(['message' => 'User deleted successfully.']);
        }

        return abort(500); //Return a server error if the task deletion fails

    }
}

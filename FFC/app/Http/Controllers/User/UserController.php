<?php

namespace App\Http\Controllers\User;

use App\Exports\UserExport;
use App\Helpers\SearchHelper;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        Log::info("*****************************");
        Log::info('User Search');
        Log::info("*****************************");

        $user = $this->userService->getAllUserData($request);

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    // Register a new user
    public function store(Request $request)
    {

        Log::info("Saving Customer Details...");
        $validatedData = $this->userValidation($request);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'User validation failed',
                'error' => $validatedData
            ], 422);
        }

        // $validator = Validator::make($request->all(), [
        //     'first_name' => 'required|string|max:255',
        //     'last_name' => 'required|string|max:255',
        //     // 'username' => ['required',function ($attribute, $value, $fail) { if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {$fail('The ' . $attribute . ' must be a valid email or username.');}},],
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => 'required|string|min:8',
        //     'mobile_number' => 'required|string|min:10|max:15|unique:users', // Add mobile number validation
        //     'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        // ]);
        // // Check if validation fails
        // if ($validator->fails()) {
        //     // return  $validator->errors();
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'User validation failed',
        //         'error' => $validator->errors()
        //     ], 422);
        // }

        // try {
        //     $validatedData = $request->validate([
        //         'first_name' => 'required|string|max:255',
        //         'last_name' => 'required|string|max:255',
        //         // 'username' => ['required',function ($attribute, $value, $fail) { if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {$fail('The ' . $attribute . ' must be a valid email or username.');}},],
        //         'email' => 'required|string|email|max:255|unique:users',
        //         'password' => 'required|string|min:8',
        //         'mobile_number' => 'required|string|min:10|max:15|unique:users', // Add mobile number validation
        //         'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        //     ]);
        // } catch (ValidationException $e) {
        //     return response()->json([
        //         'status' => false,
        //         'error' => $e->errors()
        //     ], 422);
        // }

        // Determine whether the input is an email or username
        //$loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // $validatedData = $validator->validated();

        if ($image = $request->file('profile_image')) {
            $destinationPath = 'images/profiles/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $validatedData['profile_image'] = "$profileImage";
        }

        Log::info('Generating password...');
        // Generate key
        $key = generateSecretKey(32); // Make sure to use a strong key
        // Encrypt the password
        $encryptedPassword = encryptPassword($validatedData['password'], $key);

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            //$loginType => $request->username,
            'mobile_number' => $validatedData['mobile_number'],
            'password' => Hash::make($validatedData['password']),
            'profile_image' => $validatedData['profile_image'],
            'secret_password' => $encryptedPassword,
            'secret_key' => $key,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully'
        ], 201);
    }

    public function edit($id)
    {
        // Use the findModel helper to retrieve the user
        $users = findModel(User::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($users instanceof \Illuminate\Http\JsonResponse) {
            return $users;  // Return the not found response
        }

        return response()->json([
            'status' => true,
            'data' => $users
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // Use the findModel helper to retrieve the user
        $user = findModel(User::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;  // Return the not found response
        }

        // $validator = Validator::make($request->all(), [
        //     'first_name' => 'required|string|max:255',
        //     'last_name' => 'required|string|max:255',
        //     // 'username' => ['required',function ($attribute, $value, $fail) { if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {$fail('The ' . $attribute . ' must be a valid email or username.');}},],
        //     'email' => [
        //         'required',
        //         'string',
        //         'email',
        //         'max:255',
        //         Rule::unique('users')->ignore($id),
        //     ],
        //     'mobile_number' => [
        //         'required',
        //         'numeric',
        //         'min:10',
        //         'max:15',
        //         Rule::unique('users')->ignore($id),
        //     ],
        //     'password' => 'required|string|min:8',
        //     // 'email' => 'required|string|email|max:255|unique:users',
        //     // 'mobile_number' => 'required|string|min:10|max:15|unique:users', // Add mobile number validation
        //     'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        // ]);
        // // Check if validation fails
        // if ($validator->fails()) {
        //     // return  $validator->errors();
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'User validation failed',
        //         'error' => $validator->errors()
        //     ], 422);
        // }
        // $validatedData = $validator->validated();

        // try {
        //     $validatedData = $request->validate([
        //         'first_name' => 'required|string|max:255',
        //         'last_name' => 'required|string|max:255',
        //         'email' => 'required|string|email|max:255',
        //         'password' => 'required|string|min:8',
        //         'mobile_number' => 'required|string|min:10|max:15', // Add mobile number validation
        //         'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        //     ]);
        // } catch (ValidationException $e) {
        //     return response()->json([
        //         'status' => false,
        //         'error' => $e->errors()
        //     ], 422);
        // }

        Log::info("Updating Customer Details...");
        $validatedData = $this->userValidation($request, $id);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'User validation failed',
                'error' => $validatedData
            ], 422);
        }

        if ($image = $request->file('profile_image')) {
            $destinationPath = 'images/profiles/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $validatedData['profile_image'] = "$profileImage";
        } else {
            unset($validatedData['profile_image']);
        }

        // Generate key
        $key = generateSecretKey(32); // Make sure to use a strong key

        // Encrypt the password
        $encryptedPassword = encryptPassword($validatedData['password'], $key);

        // Save secret password and key
        $user->secret_password = $encryptedPassword;
        $user->secret_key = $key;

        try {
            $user->update($validatedData);
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user data',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        // Use the findModel helper to retrieve the user
        $user = findModel(User::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;  // Return the not found response
        }

        // Delete User
        $delete = $user->delete();

        if ($delete) {
            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully'
            ], 200);
        }

        return abort(400); //Return a server error if the task deletion fails
    }

    public function status(Request $request, $id)
    {
        // Use the statusUpdate helper to update status
        return statusUpdate(User::class, $id, [
            'status' => $request->status
        ]);
    }

    public function userValidation(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'username' => ['required',function ($attribute, $value, $fail) { if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {$fail('The ' . $attribute . ' must be a valid email or username.');}},],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'mobile_number' => [
                'required',
                'numeric',
                'digits_between:10,15',
                // 'min:10',
                // 'max:15',
                Rule::unique('users')->ignore($id),
            ],
            'password' => 'required|string|min:8',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }


    public function excelExport(Request $request)
    {
        Log::info("*****************************");
        Log::info('Exporting User Excel sheet...');
        Log::info("*****************************");

        $rates = $this->userService->getAllUserData($request);
        // Export to Excel
        return Excel::download(new UserExport($rates), 'Export_User_' . date('YmdHis') . '.xlsx');
    }
}

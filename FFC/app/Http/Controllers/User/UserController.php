<?php

namespace App\Http\Controllers\User;

use App\Exports\UserExport;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegisteredMail;

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
        Log::info('User Search...');
        Log::info("*****************************");

        $user = $this->userService->getAllUserData($request);

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    // Register a new user
    // public function store(Request $request)
    // {
    //     Log::info("*****************************");
    //     Log::info('Creating User Details...');
    //     Log::info("*****************************");

    //     $validatedData = $this->userValidation($request);

    //     // Check if the validated data is an array (i.e., no validation errors)
    //     if (!is_array($validatedData)) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User validation failed',
    //             'error' => $validatedData
    //         ], 422);
    //     }

    //     // Determine whether the input is an email or username
    //     //$loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    //     DB::beginTransaction();  // Start the transaction
    //     try {
    //         $user = $this->userService->createUser($request);
    //         DB::commit();
    //         Log::info("User: {$user->id} registered successfully.");
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'User registered successfully'
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack(); // Rollback the transaction if something goes wrong            
    //         Log::error('Failed to insert user data: ', ['error' => $e->getMessage()]);
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to insert user data',
    //             "error" => $e->getMessage()
    //         ], 400); // Return error response
    //     }
    // }

    public function store(Request $request)
    {
        Log::info("*****************************");
        Log::info('Creating User Details...');
        Log::info("*****************************");

        $validatedData = $this->userValidation($request);

        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'User validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = $this->userService->createUser($request);
            DB::commit();

            // Send Email
            Mail::to($user->email)->send(new UserRegisteredMail($user));

            Log::info("User: {$user->id} registered successfully.");
            return response()->json([
                'status' => true,
                'message' => 'User registered successfully, email sent.'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to insert user data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert user data',
                "error" => $e->getMessage()
            ], 400);
        }
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
        Log::info("*****************************");
        Log::info('Updating User Details...');
        Log::info("*****************************");

        // Use the findModel helper to retrieve the user
        $user = findModel(User::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;  // Return the not found response
        }

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
            $destinationPath = 'images/profiles/user/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $imageNewpath = 'images/profiles/user/' . "" . $profileImage;
            $validatedData['profile_image'] = "$imageNewpath";
        } else {
            unset($validatedData['profile_image']);
        }
        DB::beginTransaction();  // Start the transaction
        try {
            $user->update($validatedData);
            DB::commit();
            Log::info("User: {$user->id} updated successfully.");
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong 
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user data',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        // Use the findModel helper to retrieve the customer
        $user = findModel(User::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;  // Return the not found response
        }

        DB::transaction(function () use ($user) {
            // Delete the vendor record
            $user->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ], 200);
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
                'string',
                // 'digits_between:10,15',
                // 'min:10',
                // 'max:15',
                Rule::unique('users')->ignore($id),
            ],
            'password' => [
                'sometimes',
                'string',
                'min:8',
                Rule::unique('users')->ignore($id),
            ],
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3048',
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

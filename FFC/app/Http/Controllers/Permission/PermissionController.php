<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Permission;
use App\Models\User;
use App\Services\Permission\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{

    protected $permissionService;
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function getAllMaster()
    {
        $master = Master::all();
        return response()->json(['status' => true, 'data' => $master]);
    }

    public function storeMaster(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ], [
            'name.required' => 'Please provide a name.',
            'name.string' => 'The name should be a valid string.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Master validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $master = Master::create(['name' => $request->name]);
        if ($master) {
            return response()->json(['status' => true, 'message' => 'Master created successfully'], 201);
        } else {
            return response()->json(['status' => false, 'message' => 'Failed to create Master data'], 400);
        }
    }

    public function view($userId)
    {
        // Use the findModel helper to retrieve the User
        $user = findModel(User::class, $userId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $masters = Master::select('id', 'name')
            ->with(['permissions' => function ($query) use ($userId) {
                $query->select('master_id', 'can_create', 'can_edit', 'can_delete', 'can_view')->where('user_id', $userId); // Only load permissions for this user
            }])->get();

        return response()->json([
            'status' => true,
            'data' => $masters
        ], 200);
    }

    public function saveUserPermissions(Request $request, $id)
    {
        // Use the findModel helper to retrieve the user
        $user = findModel(User::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;  // Return the not found response
        }

        // Validate the request data
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'permissions' => 'required|array'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->errors()
            ], 422);
        }

        $this->permissionService->savePermission($request);

        return response()->json([
            'status' => true,
            'message' => 'Permissions saved successfully'
        ], 201);
    }
}

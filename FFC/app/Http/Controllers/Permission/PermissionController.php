<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{

    public function view($userId)
    {
        // Use the findModel helper to retrieve the customer
        $user = findModel(User::class, $userId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;  // Return the not found response
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
        // Use the findModel helper to retrieve the customer
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

        // Extract user_id from the request
        $userId = $validated['user_id'];
        $loginUser = Auth::user();

        // Loop through each permission and update or create the record
        foreach ($validated['permissions'] as $masterName => $permissionData) {
            // Ensure master_id is present in the permission data
            if (isset($permissionData['master_id'])) {
                Permission::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'master_id' => $permissionData['master_id'],
                    ],
                    [
                        'can_create' => $permissionData['can_create'],
                        'can_edit'   => $permissionData['can_edit'],
                        'can_delete' => $permissionData['can_delete'],
                        'can_view'   => $permissionData['can_view'],
                        'granted_by' => $loginUser->id,
                    ]
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Permissions saved successfully'
        ], 201);
    }

    public function findModelHelper(string $id)
    {
        // Use the findModel helper to retrieve the customer
        $user = findModel(User::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;  // Return the not found response
        }

        return $user;
    }
}

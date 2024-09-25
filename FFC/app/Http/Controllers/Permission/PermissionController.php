<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{

    public function view($userId)
    {
        try {
            User::findOrFail($userId);
        } catch (\Exception $e) {
            return response()->json(['status' => false, "error", 'User not found'], 400);
        }

        $masters = Master::select('id', 'name')
            ->with(['permissions' => function ($query) use ($userId) {
                $query->select('master_id', 'can_create', 'can_edit', 'can_delete', 'can_view')->where('user_id', $userId); // Only load permissions for this user
            }])->get();

        return response()->json(['status' => true, 'data' => $masters]);
    }

    public function saveUserPermissions(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['status' => false, "error", 'User not found'], 400);
        }

        // Validate the request data
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'permissions' => 'required|array'
        ]);

        // Extract user_id from the request
        $userId = $validated['user_id'];
        $loginUser = Auth::user();
        Log::info('loginUser : ' . $loginUser->id);

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
            'message' => 'Permissions saved successfully!'
        ], 200);
    }
}

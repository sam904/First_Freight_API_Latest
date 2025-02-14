<?php

namespace App\Services\Permission;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    protected $loginUser;
    public function __construct()
    {
        $this->loginUser =  Auth::user();
        Log::info($this->loginUser);
    }

    public function savePermission(Request $request)
    {
        if (isset($request['permissions'])) {
            // Loop through each permission and update or create the record
            foreach ($request['permissions'] as $masterName => $permissionData) {
                // Ensure master_id is present in the permission data
                if (isset($permissionData['master_id'])) {
                    Permission::updateOrCreate(
                        [
                            'user_id' => $request['user_id'],
                            'master_id' => $permissionData['master_id'],
                        ],
                        [
                            'can_create' => $permissionData['can_create'],
                            'can_edit'   => $permissionData['can_edit'],
                            'can_delete' => $permissionData['can_delete'],
                            'can_view'   => $permissionData['can_view'],
                            'granted_by' => $this->loginUser->id,
                        ]
                    );
                }
            }
        }
        Log::info("Permission saved successfully!!!");
    }
}

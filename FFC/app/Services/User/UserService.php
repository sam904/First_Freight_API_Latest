<?php

namespace App\Services\User;

use App\Helpers\SearchHelper;
use App\Models\User;
use App\Services\Permission\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function getAllUserData(Request $request)
    {
        // Paginate the results
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit');
        $sortColumn = $request->input('sortColumn') ?: 'id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        $isExport = $request->input('export') ?? false;
        $ids = $request->input('ids');

        $query = User::query();
        // Get all column names of the 'users' table
        $model = new User();
        // Apply search filters
        $query = SearchHelper::applySearchFilters($query, $model, $request);
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }
        if ($isExport && empty($limit)) {
            // Fetch all data without pagination
            Log::info("export is true and limit is empty");
            $startTime = microtime(true);
            $limit = $query->count();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            Log::info("User Query Count = {$limit} && execution time: {$executionTime} seconds");
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        } else {
            $limit = $limit ?: 10;
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        }
    }

    public function createUser(Request $request)
    {
        $user = User::create([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'email' => $request['email'],
            'mobile_number' => $request['mobile_number'],
            'password' => Hash::make($request['password']),
            // 'profile_image' => $img['profileImage'],
            //  $loginType => $request->username,
            // 'secret_password' => $encryptedPassword,
            // 'secret_key' => $key,
        ]);
        Log::info("user is created => " . $user->id);
        if ($request->hasFile('profile_image')) {
            $destinationPath = 'images/user/' . $user->id . '/';
            if ($image = $request->file('profile_image')) {
                $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $profileImage);
                $img['profileImage'] = $destinationPath . $profileImage;
            }
            $user->update([
                'profile_image' =>  $img['profileImage']
            ]);
        }


        Log::info("Saving User Permission...");
        $request->merge(['user_id' => $user->id]);
        $this->permissionService->savePermission($request);

        return $user;
    }
}

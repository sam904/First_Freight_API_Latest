<?php

namespace App\Services\User;

use App\Helpers\SearchHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserService
{
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
}

<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SearchHelper
{
    /**
     * Apply search filters to a query based on the provided parameters.
     *
     * @param Builder $query
     * @param string $searchTerm
     * @param string|null $filterBy
     * @param array $searchableColumns
     * @param array $columnMap
     * @return Builder
     */
    public static function applySearchFilters(
        Builder $query,
        ?string $searchTerm = null,
        ?string $filterBy = null,
        ?array $searchableColumns = null,
        ?string $startDate,
        ?string $endDate
    ): Builder {
        Log::info("Start Date = " . $startDate);
        Log::info("End Date = " . $endDate);
        Log::info("filterBy = " . $filterBy);
        Log::info("searchTerm = " . $searchTerm);
        if (!empty($searchTerm)) {
            if (!empty($filterBy)) {
                // Retrieve the column map from the User model
                // $columnMap = User::$columnMap;
                // if (isset($columnMap[$filterBy])) {
                Log::info('Data is searched based on FilterBy and SearchTerm...' . $filterBy . '==' . $searchTerm);
                //     $query->where($columnMap[$filterBy], 'LIKE', "%{$searchTerm}%");
                // }
                $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
            } else {
                Log::info('Data is searched based on SearchTerm Only ==> ' . $searchTerm);
                foreach ($searchableColumns as $column) {
                    $query->orWhere($column, 'LIKE', "%{$searchTerm}%");
                }
            }
        }
        // Check if the startDate and endDate are provided in the request
        if ($startDate && $endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query;
    }
}

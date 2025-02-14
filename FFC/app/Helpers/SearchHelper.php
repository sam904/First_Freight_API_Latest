<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
        $model,
        Request $request
    ): Builder {

        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        Log::info("\n********************\nAppling Search filter for this model = " . get_class($model) . "\n********************");
        Log::info("Start Date = " . $startDate);
        Log::info("End Date = " . $endDate);
        Log::info("filterBy = " . $filterBy);
        Log::info("searchTerm = " . $searchTerm);

        $searchableColumns = $model->getSearchableColumns();
        Log::info("Searchable Columns list => " . json_encode($searchableColumns));

        if (!empty($searchTerm)) {
            if (!empty($filterBy)) {
                // Retrieve the column map from the User model
                // $columnMap = User::$columnMap;
                // if (isset($columnMap[$filterBy])) {
                //     $query->where($columnMap[$filterBy], 'LIKE', "%{$searchTerm}%");
                // }
                if (in_array($filterBy, $searchableColumns)) {
                    Log::info('Data is searched based on FilterBy and SearchTerm...' . $filterBy . '==' . $searchTerm);
                    $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                } else {
                    // Log or handle the case where filterBy is not in searchableColumns
                    Log::info('FilterBy value "' . $filterBy . '" is not in searchableColumns. Skipping to next iteration.');
                }
            } else {
                Log::info('Data is searched based on SearchTerm Only ==> ' . $searchTerm);
                foreach ($searchableColumns as $column) {
                    Log::info("column => " . $column . "==" . $searchTerm);
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

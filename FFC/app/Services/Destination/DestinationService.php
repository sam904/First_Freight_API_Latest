<?php

namespace App\Services\Destination;

use App\Models\Destination\Destination;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DestinationService
{

    public function getAllDestination(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit') ?: 10;
        $sortColumn = $request->input('sortColumn') ?: 'id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';

        $query = DB::table('destinations')
            ->join('countries', 'destinations.country_id', '=', 'countries.id')
            ->join('states', 'destinations.state_id', '=', 'states.id')
            ->select(
                'destinations.id',
                'destinations.name as city',
                'states.name as state',
                'countries.name as country',
                'destinations.status',
                'destinations.created_at'
            );

        // Apply search filters
        if (!empty($searchTerm)) {
            Log::info($filterBy . "==" . $searchTerm);
            if (!empty($filterBy) && $filterBy == "city") {
                $query->where('destinations.name', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "status") {
                $query->Where('destinations.status', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "country") {
                $query->where('countries.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('countries.iso_code', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "state") {
                $query->where('states.name', 'LIKE', "%{$searchTerm}%");
            } else {
                // When filterBy is null, search in all three fields
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('destinations.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('destinations.status', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('countries.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('countries.iso_code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('states.name', 'LIKE', "%{$searchTerm}%");
                });
            }
        }
        // Check if the startDate and endDate are provided in the request
        if ($startDate && $endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('destinations.created_at', [$startDate, $endDate]);
        }
        // Log::info($query->toSql());
        return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
    }

    public function createDestination(Request $request)
    {
        $destination = Destination::create([
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
        ]);
        return true;
    }


    public function updateDestination(Request $request, Destination $destination)
    {
        $destination->update([
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
        ]);
        return true;
    }
}

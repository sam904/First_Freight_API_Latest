<?php

namespace App\Services\Port;

use App\Helpers\SearchHelper;
use App\Models\Country;
use App\Models\Port\Port;
use App\Models\Port\PortTerminal;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortService
{

    public function getAllPort(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        /*$query = Port::select('id', 'name', 'port_type_id', 'country_id', 'state_id')  // Select specific columns from ports table
            ->with([
                'portType' => function ($query) {
                    $query->select('id', 'name'); // Select specific columns from portType table
                },
                'country' => function ($query) {
                    $query->select('id', 'name'); // Select specific columns from country table
                },
                'states' => function ($query) {
                    $query->select('id', 'name'); // Select specific columns from state table
                }
            ]);
        // Get all column names of the 'Port' table
        $model = new Port();
        // Apply search filters
        $query = SearchHelper::applySearchFilters($query, $model, $request);
        $query->orWhereHas('portType', function ($query) use ($searchTerm, $filterBy) {
            if ($filterBy && $searchTerm) {
                Log::info('PortType FilterBy =' . $filterBy);
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            } else {
                Log::info('PortType Search on whole table =' . $searchTerm);
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }
        });
        $countryModel = new Country();
        $searchableCountryColumns = $countryModel->getSearchableColumns();
        $query->orWhereHas('country', function ($query) use ($searchTerm, $filterBy, $searchableCountryColumns) {
            if ($filterBy && in_array($filterBy, $searchableCountryColumns)) {
                Log::info('country FilterBy =' . $filterBy);
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('iso_code', 'LIKE', "%{$searchTerm}%");
            } else {
                Log::info('country Search on whole table =' . $searchTerm);
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('iso_code', 'LIKE', "%{$searchTerm}%");
            }
        });
        $stateModel = new State();
        $searchableStateColumns = $stateModel->getSearchableColumns();
        $query->orWhereHas('state', function ($query) use ($searchTerm, $filterBy, $searchableStateColumns) {
            if ($filterBy && in_array($filterBy, $searchableStateColumns)) {
                Log::info('state FilterBy =' . $filterBy);
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            } else {
                Log::info('state Search on whole table =' . $searchTerm);
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }
        });
*/
        $query = DB::table('ports')
            ->join('port_types', 'ports.port_type_id', '=', 'port_types.id')
            ->join('countries', 'ports.country_id', '=', 'countries.id')
            ->join('states', 'ports.state_id', '=', 'states.id')
            ->select(
                'ports.id as portId',
                'ports.name as portName',
                'port_types.name as portType',
                'states.name as state',
                'countries.name as country',
                'ports.status',
                'ports.created_at'
            );

        // Apply search filters
        if (!empty($searchTerm)) {
            if (!empty($filterBy) && $filterBy == "port") {
                Log::info($filterBy . "==" . $searchTerm);
                $query->where('ports.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('ports.status', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "portType") {
                $query->where('port_types.name', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "country") {
                $query->where('countries.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('countries.iso_code', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "state") {
                $query->where('states.name', 'LIKE', "%{$searchTerm}%");
            } else {
                // When filterBy is null, search in all three fields
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('ports.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('ports.status', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('port_types.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('countries.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('countries.iso_code', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('states.name', 'LIKE', "%{$searchTerm}%");
                });
            }
        }
        // Check if the startDate and endDate are provided in the request
        if ($startDate && $endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('ports.created_at', [$startDate, $endDate]);
        }
        return $query->orderBy("portId", "desc")->paginate($limit, ['*'], 'page', $page);
    }
    public function createPort(Request $request)
    {
        $port = Port::create([
            'port_type_id' => $request['port_type'],
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
        ]);

        // Create sales records
        $this->storeTerminals($request, $port);

        return true;
    }

    public function storeTerminals(Request $request, Port $port)
    {
        foreach ($request->input('terminals') as $portName) {
            if (!empty($portName)) {
                PortTerminal::create([
                    'name' => $portName,
                    'port_id' => $port->id
                ]);
            }
        }
    }

    public function updatePort(Request $request, $id)
    {
        try {
            $port = Port::findOrFail($id);
        } catch (\Exception $e) {
            return ['errorMsg' => "Port not found"];
        }

        // Delete existing related records
        $port->portTerminals()->delete();

        $port->update([
            'port_type_id' => $request['port_type'],
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
        ]);

        // Create sales records
        $this->storeTerminals($request, $port);

        return true;
    }
}

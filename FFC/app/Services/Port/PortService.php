<?php

namespace App\Services\Port;

use App\Models\Port\Port;
use App\Models\Port\PortTerminal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortService
{

    public function getAllPort(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit');
        $sortColumn = $request->input('sortColumn') ?: 'portId';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        $isExport = $request->input('export') ?? false;
        $ids = $request->input('ids');

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
                'ports.created_at',
                'ports.updated_at'
            );

        // Apply filter by IDs if they are provided
        if (!empty($ids)) {
            $query->whereIn('ports.id', $ids);
        }

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
        if ($isExport && empty($limit)) {
            // Fetch all data without pagination
            Log::info("export is true and limit is empty");
            $startTime = microtime(true);
            $limit = $query->count();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            Log::info("Port Query Count = {$limit} && execution time: {$executionTime} seconds");
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        } else {
            $limit = $limit ?: 10;
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        }
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

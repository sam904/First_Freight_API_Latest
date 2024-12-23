<?php

namespace App\Services\Rate;

use App\Helpers\SearchHelper;
use App\Models\Rate\Rate;
use App\Models\Rate\RateCharge;
use App\Models\Rate\RateNotes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RateService
{
    protected $loginUser;

    public function __construct()
    {
        $this->loginUser =  Auth::user();
    }

    public function getAllRateData(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit');
        $sortColumn = $request->input('sortColumn') ?: 'rate_id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        $isExport = $request->input('export') ?? false;
        $ids = $request->input('ids');

        Log::info("Start Date = " . $startDate);
        Log::info("End Date = " . $endDate);
        Log::info("filterBy = " . $filterBy);
        Log::info("searchTerm = " . $searchTerm);

        $today = Carbon::now()->toDateString();
        $query = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->leftJoin('ports as loading_ports', 'rates.port_of_loading_id', '=', 'loading_ports.id') // Left join for port_of_loading_id
            ->leftJoin('ports as discharge_ports', 'rates.port_of_discharge_id', '=', 'discharge_ports.id') // Left join for port_of_discharge_id
            ->join('destinations', 'rates.destination_id', '=', 'destinations.id')
            ->leftJoin('service_types', 'rates.service_type_id', '=', 'service_types.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                'loading_ports.name as port_of_loading_name', // Name for port_of_loading
                'discharge_ports.name as port_of_discharge_name', // Name for port_of_discharge
                'destinations.name as destination_name',
                'service_types.name as serviceTypeName',
                'freight',
                'expiry',
                // DB::raw("DATEDIFF('$today', rates.start_date) as days_passed"),
                DB::raw("DATE_FORMAT(rates.start_date, '%m/%d/%y') as rate_received"),
                // DB::raw('DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY) as expiry_date'),
                // DB::raw('GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0) as expiry_days'),
                // DB::raw("CONCAT(
                //     DATE_FORMAT(DATE_ADD(rates.start_date, INTERVAL GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) DAY), '%m/%d/%Y'),
                //     ', ',
                //     GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0),
                //     ' Days Left'
                // ) as rate_validity"),
                DB::raw("CONCAT(
                        DATE_FORMAT(
                            DATE_ADD(
                                rates.start_date, 
                                INTERVAL GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) DAY
                            ), '%m/%d/%Y'
                        ),
                        ', ',
                        CASE 
                            WHEN GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0) = 0 
                            THEN 'Expired' 
                            ELSE CONCAT(GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0), ' Days Left')
                        END
                    ) as rate_validity"),
                'rates.status',
                'rates.created_at',
                'rates.updated_at',
                'service_types.name as serviceType'
            );

        // Apply filter by IDs if they are provided
        if (!empty($ids)) {
            $query->whereIn('rates.id', $ids);
        }

        // Apply search filters
        if (!empty($searchTerm)) {
            Log::info("\n********************\nAppling Search filter for this model = Rate\n********************");
            if (!empty($filterBy) && $filterBy == "port") {
                // $query->where('ports.name', 'LIKE', "%{$searchTerm}%");
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('loading_ports.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('discharge_ports.name', 'LIKE', "%{$searchTerm}%");
                });
            } elseif (!empty($filterBy) && $filterBy == "destination") {
                $query->where('destinations.name', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "vendor") {
                $query->where('vendors.company_name', 'LIKE', "%{$searchTerm}%");
            } elseif (!empty($filterBy) && $filterBy == "serviceType") {
                $query->where('service_types.name', 'LIKE', "%{$searchTerm}%");
            } else {

                // When filterBy is null, search in all three fields
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('rates.status', 'LIKE', "%{$searchTerm}%")
                        // ->orwhere('ports.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('loading_ports.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('discharge_ports.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('destinations.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('vendors.company_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('service_types.name', 'LIKE', "%{$searchTerm}%");
                });
            }
        }
        // Check if the startDate and endDate are provided in the request
        if ($startDate && $endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('rates.created_at', [$startDate, $endDate]);
        }

        if ($isExport && empty($limit)) {
            // Fetch all data without pagination
            Log::info("export is true and limit is empty");
            $startTime = microtime(true);
            $limit = $query->count();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            Log::info("Rate Query Count = {$limit} && execution time: {$executionTime} seconds");
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        } else {
            $limit = $limit ?: 10;
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        }
    }
    public function createRate(Request $request)
    {
        $rate = Rate::create([
            'vendor_id' => $request['vendor_id'],
            'port_of_loading_id' => $request['port_of_loading_id'] ?? null,
            'port_of_discharge_id' => $request['port_of_discharge_id'] ?? null,
            'destination_id' => $request['destination_id'],
            'freight' => $request['freight'],
            'fsc' => $request['fsc'],
            'start_date' => $request['start_date'],
            'expiry' => $request['expiry'],
            'service_type_id' => $request['serviceType'],
        ]);

        $this->storeCharges($request, $rate);

        // Check if rateNotes is not empty
        if (!empty($request->input('rateNotes'))) {
            foreach ($request->input('rateNotes') as $note) {
                RateNotes::create([
                    'title' => $note['title'],
                    'description' => $note['description'],
                    'tag' => $note['tag'],
                    'pin' => $note['pin'],
                    'rate_id' => $rate->id, // Use the saved rate ID
                    "user_id" =>  $this->loginUser->id,
                ]);
                // // Create a request instance for each note, to format and encapsulate each note’s data as if it's a separate request
                // $noteRequest = new \Illuminate\Http\Request();
                // // Merge the note data with the rate ID into the new request instance
                // $noteRequest->merge([
                //     'title' => $note['title'],
                //     'description' => $note['description'],
                //     'tag' => $note['tag'],
                //     'pin' => $note['pin'],
                //     'rateId' => $rate->id, // Use the saved rate ID
                // ]);
                // // Now you can call the saveNotes method or do any further processing
                // $this->saveNotes($noteRequest);
            }
        }

        return true;
    }

    public function updateRate(Request $request, Rate $rate, $id)
    {
        //delete RateCharges data
        $rate->charges()->delete();

        $rate->update([
            'vendor_id' => $request['vendor_id'],
            'port_of_loading_id' => $request['port_of_loading_id'] ?? null,
            'port_of_discharge_id' => $request['port_of_discharge_id'] ?? null,
            'destination_id' => $request['destination_id'],
            'freight' => $request['freight'],
            'fsc' => $request['fsc'],
            'start_date' => $request['start_date'],
            'expiry' => $request['expiry'],
            'service_type_id' => $request['serviceType'],
        ]);

        // Save RateCharges
        $this->storeCharges($request, $rate);

        return true;
    }

    public function storeCharges(Request $request, $rate)
    {
        $chargeData = $request->input('charges');
        $charge = [];

        foreach ($chargeData as $chargeItem) {
            $charge[] = new RateCharge([
                'charge_name' => $chargeItem['charge_name'],
                'amount' => $chargeItem['amount'],
                'rate_id' => $rate->id
            ]);
        }

        // Save all charges related to rate
        $rate->charges()->saveMany($charge);
    }

    /**
     * Notes
     */

    public function getRateNoteData(Request $request, $rateId)
    {
        $searchTerm = $request->input('searchTerm');
        $query = RateNotes::with('user:id,first_name,last_name');
        $query->where('rate_id', $rateId)
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('tag', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($q) use ($searchTerm) {
                        $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('last_name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        // Log::info($query->toSql(), $query->getBindings());
        return $query->orderBy('id', 'desc')->get();
    }

    public function saveNotes(Request $request)
    {
        Log::info("****** save Note *******");
        RateNotes::create([
            'title' => $request['title'],
            'description' => $request['description'],
            'rate_id' => $request['rateId'],
            'tag' => $request['tag'],
            'pin' => $request['pin'],
            "user_id" =>  $this->loginUser->id,
        ]);
        return true;
    }

    public function updateNote(Request $request, RateNotes $rateNotes)
    {
        $rateNotes->update([
            'title' => $request['title'],
            'description' => $request['description'],
            'rate_id' => $request['rateId'],
            'tag' => $request['tag'],
            'pin' => $request['pin'],
            "user_id" =>  $this->loginUser->id,
        ]);
        return true;
    }

    public function getAllRateExportData_old(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit') ?: 10;
        $sortColumn = $request->input('sortColumn') ?: 'rate_id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        Log::info("filterBy" . $filterBy);
        $portFlag = false;
        $destinationFlag = false;
        $vendorFlag = false;
        $serviceTypeFlag = false;

        // $query = Rate::with([
        //     'vendor:id,company_name',
        //     'port:id,name',
        //     'destination:id,name',
        //     'serviceType:id,name',
        //     'charges:id,charge_name,amount,rate_id',
        // ]);
        // // ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
        // // ->join('ports', 'rates.port_id', '=', 'ports.id')
        // // ->join('destinations', 'rates.destination_id', '=', 'destinations.id')
        // // ->join('service_types', 'rates.service_type_id', '=', 'service_types.id')
        // // ->leftJoin('rate_charges', 'rates.id', '=', 'rate_charges.rate_id') // Left join with charges to fetch amount
        // // ->select(
        // //     'rates.id as rate_id',
        // //     'vendors.company_name as vendor_name',
        // //     'ports.name as port_name',
        // //     'destinations.name as destination_name',
        // //     'freight',
        // //     'expiry',
        // //     'rates.status',
        // //     'rates.created_at',
        // //     'service_types.name as serviceType'
        // // );

        if (
            $filterBy === 'port' || $filterBy === 'destination' ||
            $filterBy === 'vendor' ||  $filterBy === 'serviceType'
        ) {
            // Set filterBy to null in the request
            // $request->merge(['filterBy' => null]);
            if ($filterBy === 'port') {
                $portFlag = true;
            } elseif ($filterBy === 'destination') {
                $destinationFlag = true;
            } elseif ($filterBy === 'vendor') {
                $vendorFlag = true;
            } elseif ($filterBy === 'serviceType') {
                $serviceTypeFlag = true;
            }
        }

        // if ($filterBy == null) {
        //     Log::info('filter by is null');
        //     $portFlag = true;
        //     $destinationFlag = true;
        //     $vendorFlag = true;
        //     $serviceTypeFlag = true;
        // }

        // if ($portFlag) {
        //     $query->whereHas('port', function ($query) use ($searchTerm) {
        //         $query->where('name', 'LIKE', "%{$searchTerm}%");
        //     });
        // }
        // if ($destinationFlag) {
        //     $query->whereHas('destination', function ($query) use ($searchTerm) {
        //         $query->where('name', 'LIKE', "%{$searchTerm}%");
        //         Log::info('destination query : ' . $query->toSql());
        //     });
        // }
        // if ($vendorFlag) {
        //     $query->whereHas('vendor', function ($query) use ($searchTerm) {
        //         $query->where('company_name', 'LIKE', "%{$searchTerm}%");
        //         Log::info('vendor query : ' . $searchTerm . "///" . $query->toSql());
        //     });
        // }
        // if ($serviceTypeFlag) {
        //     $query->whereHas('serviceType', function ($query) use ($searchTerm) {
        //         $query->where('name', 'LIKE', "%{$searchTerm}%");
        //     });
        // }


        // // Apply search filters
        // $model = new Rate();
        // $query = SearchHelper::applySearchFilters($query, $model, $request);
        // Log::info('Final SQL Query: ' . $query->toSql());
        // Log::info('Bindings: ' . json_encode($query->getBindings()));
        // // Check if the startDate and endDate are provided in the request
        // // if ($startDate && $endDate) {
        // //     $endDate = Carbon::parse($endDate)->endOfDay();
        // //     $query->whereBetween('created_at', [$startDate, $endDate]);
        // // }
        // // $columnMapping = [
        // //     'port_name' => 'port.name',
        // //     'destination_name' => 'destination.name',
        // //     // Add other mappings as needed
        // // ];
        // // $actualColumn = $columnMapping[$sortColumn] ?? 'id'; // Default to 'id' if not found
        // // Log::info($actualColumn);

        $query = Rate::with([
            'vendor:id,company_name',
            'port:id,name',
            'destination:id,name',
            'serviceType:id,name',
            'charges:id,charge_name,amount,rate_id',
        ]);
        $query->where(function ($query) use ($portFlag, $destinationFlag, $vendorFlag, $serviceTypeFlag, $searchTerm) {
            if ($portFlag) {
                $query->whereHas('port', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%{$searchTerm}%");
                });
            }
            if ($destinationFlag) {
                $query->whereHas('destination', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%{$searchTerm}%");
                });
            }
            if ($vendorFlag) {
                $query->whereHas('vendor', function ($query) use ($searchTerm) {
                    $query->where('company_name', 'LIKE', "%{$searchTerm}%");
                });
            }
            if ($serviceTypeFlag) {
                $query->whereHas('serviceType', function ($query) use ($searchTerm) {
                    $query->where(
                        'name',
                        'LIKE',
                        "%{$searchTerm}%"
                    );
                });
            }
        })
            ->where(function ($query) use ($searchTerm) {
                $query->orWhere('start_date', 'LIKE', "%{$searchTerm}%")
                    ->orWhere(
                        'expiry',
                        'LIKE',
                        "%{$searchTerm}%"
                    )
                    ->orWhere('freight', 'LIKE', "%{$searchTerm}%")
                    ->orWhere(
                        'fsc',
                        'LIKE',
                        "%{$searchTerm}%"
                    )
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%");
            })
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
        $rates = $query->orderBy('id', $sortDirection)->paginate($limit, ['*'], 'page', $page);
        return $rates;
    }
}

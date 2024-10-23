<?php

namespace App\Services\Rate;

use App\Helpers\SearchHelper;
use App\Models\Rate\Rate;
use App\Models\Rate\RateCharge;
use App\Models\Rate\RateNotes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RateService
{
    public function getAllRateData(Request $request)
    {
        $today = Carbon::now()->toDateString();

        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // Get all column names of the 'users' table
        $model = new Rate();

        $query = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->join('ports', 'rates.port_id', '=', 'ports.id')
            ->join('destinations', 'rates.destination_id', '=', 'destinations.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                'ports.name as port_name',
                'destinations.name as destination_name',
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
            );
        // Apply search filters
        // $query = SearchHelper::applySearchFilters($query, $model, $request);

        // Check if the startDate and endDate are provided in the request
        if ($startDate && $endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $query->orderBy('id', 'desc')->paginate(10);

        return $query;
    }
    public function createRate(Request $request)
    {
        $rate = Rate::create([
            'vendor_id' => $request['vendor_id'],
            'port_id' => $request['port_id'],
            'destination_id' => $request['destination_id'],
            'freight' => $request['freight'],
            'fsc' => $request['fsc'],
            'start_date' => $request['start_date'],
            'expiry' => $request['expiry'],
            'service_type_id' => $request['serviceType'],
        ]);

        $this->storeCharges($request, $rate);

        return true;
    }

    public function updateRate(Request $request, Rate $rate, $id)
    {
        //delete RateCharges data
        $rate->charges()->delete();

        $rate->update([
            'vendor_id' => $request['vendor_id'],
            'port_id' => $request['port_id'],
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

    public function saveNotes(Request $request)
    {
        RateNotes::create([
            'title' => $request['title'],
            'description' => $request['description'],
            'rate_id' => $request['rateId'],
            'tag' => $request['tag'],
            'pin' => $request['pin'],
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
        ]);
        return true;
    }
}

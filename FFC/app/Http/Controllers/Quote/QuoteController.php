<?php

namespace App\Http\Controllers\Quote;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function getVendorList(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $query = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                DB::raw("DATE_FORMAT(rates.start_date, '%m/%d/%y') as rate_received"),
                // DB::raw('DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY) as rate_validity'),
                DB::raw("DATE_FORMAT(DATE_ADD(rates.start_date, INTERVAL GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) DAY), '%m/%d/%Y') as rate_validity")
            ); // Adjust the columns as needed

        // If port_id is provided, filter by port_id
        if ($request->has('port_id')) {
            $query->where('rates.port_id', $request->port_id);
        }

        // If destination_id is provided, filter by destination_id
        if ($request->has('destination_id')) {
            $query->where('rates.destination_id', $request->destination_id);
        }

        // Get the result
        $vendors = $query->distinct()->get();

        return response()->json([
            'status' => true,
            'data' => $vendors
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Quote;

use App\Http\Controllers\Controller;
use App\Models\Quote\Quote;
use App\Services\Quote\QuoteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    protected QuoteService $quoteService;
    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function getVendorList(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $query = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                'expiry',
                DB::raw("DATE_FORMAT(rates.start_date, '%m/%d/%y') as rate_received"),
                // DB::raw('DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY) as rate_validity'),
                // DB::raw("DATE_FORMAT(DATE_ADD(rates.start_date, INTERVAL GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) DAY), '%m/%d/%Y') as rate_validity")
                DB::raw("CONCAT(
                        DATE_FORMAT(
                            DATE_ADD(
                                rates.start_date, 
                                INTERVAL GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) DAY
                            ), '%m/%d/%Y'
                        ),
                        '',
                        CASE 
                            WHEN GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0) = 0 
                            THEN ', Expired' 
                            ELSE ''
                        END
                    ) as rate_validity"),
            );

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

    public function index(Request $request)
    {
        // $quotes = DB::table('quotes')
        //     ->join('customers', 'quotes.customer_id', '=', 'customers.id')
        //     ->join('quote_details', 'quotes.id', '=', 'quote_details.quote_id')
        //     ->join('quote_charges', 'quote_details.id', '=', 'quote_charges.quote_details_id')
        //     ->join('ports', 'quote_details.port_id', '=', 'ports.id')
        //     ->join('destinations', 'quote_details.destination_id', '=', 'destinations.id')
        //     ->join('users', 'users.id', '=', 'quotes.created_by')
        //     ->select(
        //         'quotes.id as Quote_id',
        //         'customers.company_name as Customer_name',  // Assuming you have a 'name' column in the customers table
        //         'ports.name as Port_name',          // Assuming you have a 'name' column in the ports table
        //         'destinations.name as Destination_name', // Assuming you have a 'name' column in the destinations table
        //         DB::raw("CONCAT(users.first_name, ' ', users.last_name) as created_by"),
        //         DB::raw("DATE_FORMAT(quotes.created_at, '%m/%d/%y') as generated_date"),
        //         'quotes.quote_status',
        //         'quotes.status',
        //         // 'quote_details.freight'
        //     )
        //     ->paginate(10);

        $quotes = Quote::with([
            'customer:id,company_name',  // Load customer and only select 'id' and 'company_name'
            'quoteDetails.port:id,name',  // Load port inside quoteDetails and select only 'id' and 'name'
            'quoteDetails.destination:id,name',  // Load destination inside quoteDetails and select only 'id' and 'name'
            'quote'
            //'createdByUser:id,first_name,last_name' // Load the user who created the quote
        ])
            // ->select('id as Quote_id', 'customer_id', 'created_by', 'created_at', 'quote_status', 'status') // Select relevant columns from quotes
            ->paginate(10);
        return response()->json($quotes);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $this->quoteService->createQuote($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Quote created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to save quotation data',
                "error" => $e->getMessage()
            ], 500);
        }
    }
}

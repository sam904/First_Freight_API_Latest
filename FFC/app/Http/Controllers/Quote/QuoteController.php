<?php

namespace App\Http\Controllers\Quote;

use App\Http\Controllers\Controller;
use App\Models\Quote\Quote;
use App\Models\Quote\QuoteNotes;
use App\Services\Quote\QuoteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    protected QuoteService $quoteService;
    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function getVendorList(Request $request)
    {
        /*
        Process:
        1. First, it searches for vendors based on the provided port_id and destination_id.
        2. During the edit operation, when the rate_id and vendor_id are provided, it retrieves the data matching 
            those values and adds it to the existing dataset. This step is specifically for the edit functionality.
        */
        $today = Carbon::now()->toDateString();
        // Main query
        $query = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                'expiry',
                'rates.freight',
                'rates.vendor_id',
                'rates.status',
                DB::raw("DATE_FORMAT(rates.start_date, '%m/%d/%y') as rate_received"),
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
                DB::raw("0 as temp_sort_column")
            );

        if ($request->has('port_id')) {
            $query->where('rates.port_id', $request->port_id);
        }

        if ($request->has('destination_id')) {
            $query->where('rates.destination_id', $request->destination_id);
        }

        $query->where('rates.status', 'active');

        // Query for the additional record based on vendor_id and rate_id
        if ($request->has('vendor_id') && $request->has('rate_id')) {
            $vendorId = $request->input('vendor_id');
            $rateId = $request->input('rate_id');

            $additionalQuery = DB::table('rates')
                ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
                ->select(
                    'rates.id as rate_id',
                    'vendors.company_name as vendor_name',
                    'expiry',
                    'rates.freight',
                    'rates.vendor_id',
                    'rates.status',
                    DB::raw("DATE_FORMAT(rates.start_date, '%m/%d/%y') as rate_received"),
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
                    DB::raw("1 as temp_sort_column")
                )
                ->where('rates.vendor_id', $vendorId)
                ->where('rates.id', $rateId);

            // Use union to combine both queries
            $query = $query->union($additionalQuery);
        }

        // Now apply the sorting after the union
        $finalQuery = DB::table(DB::raw("({$query->toSql()}) as combined"))
            ->mergeBindings($query)  // Needed to bind the original query parameters
            ->orderBy('temp_sort_column', 'desc')
            ->orderBy('freight', 'asc');

        // Paginate the final sorted result
        $ratesCollection = $finalQuery->paginate(5);

        // Return the result
        return response()->json([
            'status' => true,
            'message' => 'Records with additional data and proper sorting',
            'data' => $ratesCollection,
        ], 200);
    }

    public function index(Request $request)
    {
        Log::info('Quote Index...');
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
            'user:id,first_name,last_name,profile_image',
            'quoteDetails.rate:id,start_date,vendor_id,port_id,destination_id',
            'quoteDetails.rate.vendor:id,company_name',
            // 'quoteDetails.vendor:id,company_name',  // Load vendor inside quoteDetails and select only 'id' and 'name'
            // 'quoteDetails.port:id,name',  // Load port inside quoteDetails and select only 'id' and 'name'
            // 'quoteDetails.destination:id,name',  // Load destination inside quoteDetails and select only 'id' and 'name'
            // 'quoteDetails.charges:quote_detail_id,charge_name,amount',
            // 'quoteDetails.rate:id,start_date'
        ])
            ->paginate(10);

        // $quotes = Quote::with([
        //     'customer:id,company_name',  // Load customer and only select 'id' and 'company_name'
        //     'quoteDetails.vendor:id,company_name',
        //     'quoteDetails.port:id,name',  // Load port inside quoteDetails and select only 'id' and 'name'
        //     'quoteDetails.destination:id,name',  // Load destination inside quoteDetails and select only 'id' and 'name'
        //     'quoteDetails.charges:quote_detail_id,charge_name,amount'  // Load quoteCharges within quoteDetails and select relevant fields
        // ])
        //     ->join('users', 'users.id', '=', 'quotes.created_by')  // Join the users table to access user fields
        //     ->select(
        //         'quotes.*',
        //         DB::raw("CONCAT(users.first_name, ' ', users.last_name) as created_by")  // Concatenate first_name and last_name
        //     )
        //     ->paginate(10);
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

    public function edit(Request $request, $id)
    {
        // Use the findModel helper to retrieve the vendor
        $quote = findModel(Quote::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quote instanceof \Illuminate\Http\JsonResponse) {
            return $quote;  // Return the not found response
        }
        $quotes = Quote::with([
            'customer:id,company_name',  // Load customer and only select 'id' and 'company_name'
            // 'user:id,first_name,last_name,profile_image',
            'quoteDetails.rate:id,start_date,vendor_id,port_id,destination_id',
            'quoteDetails.rate.vendor:id,company_name',
            // 'quoteDetails.rate.port:id,name',
            // 'quoteDetails.rate.destination:id,name',
            'quoteDetails.charges:quote_detail_id,charge_name,amount',
        ])->get();

        return response()->json(['status' => true, 'data' => $quotes], 200);
    }

    public function update(Request $request, $id)
    {
        // Use the findModel helper to retrieve the vendor
        $quote = findModel(Quote::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quote instanceof \Illuminate\Http\JsonResponse) {
            return $quote;  // Return the not found response
        }

        DB::beginTransaction();
        try {
            $this->quoteService->updateQuote($request,  $quote);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Quote updated successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update quotation data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        // Use the findModel helper to retrieve the rate
        $quote = findModel(Quote::class, id: $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quote instanceof \Illuminate\Http\JsonResponse) {
            return $quote;  // Return the not found response
        }

        DB::transaction(function () use ($id) {
            $this->quoteService->deleteQuote($id, 'Delete');
        });

        return response()->json([
            'status' => true,
            'message' => 'Quote deleted successfully'
        ], 200);
    }

    public function status(Request $request, $id)
    {
        if ($request->has('quoteStatus')) {
            return statusUpdate(Quote::class, $id, [
                'quote_status' => $request->quoteStatus,
                'created_by' => Auth::user()->id,
            ]);
        }
        // Use the statusUpdate helper to update status
        return statusUpdate(Quote::class, $id, [
            'status' => $request->status,
            'created_by' => Auth::user()->id,
        ]);
    }

    // Quote Notes
    // Passing QuoteId
    public function getQuoteNote($quoteId)
    {
        // Use the findModel helper to retrieve the customer
        $quote = findModel(Quote::class, $quoteId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quote instanceof \Illuminate\Http\JsonResponse) {
            return $quote;  // Return the not found response
        }

        $quoteNote = QuoteNotes::where('quote_id', $quoteId)->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $quoteNote], 200);
    }


    public function storeNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'quoteId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Quote Note Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction
        try {
            $this->quoteService->saveNotes($request);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Quote Notes created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert Quote note data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert Quote note',
                "error" => $e->getMessage()
            ], 500); // Return error response
        }
    }

    public function updateNote(Request $request, $id)
    {
        // Use the findModel helper to retrieve the customer
        $quoteNotes = findModel(QuoteNotes::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quoteNotes instanceof \Illuminate\Http\JsonResponse) {
            return $quoteNotes;  // Return the not found response
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'quoteId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Quote Note Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction
        try {
            $this->quoteService->updateNote($request, $quoteNotes);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Quote Notes updated successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update Quote note data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Quote note',
                "error" => $e->getMessage()
            ], 500); // Return error response
        }
    }

    public function editNote($id)
    {
        // Use the findModel helper to retrieve the customer
        $quoteNotes = findModel(QuoteNotes::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quoteNotes instanceof \Illuminate\Http\JsonResponse) {
            return $quoteNotes;  // Return the not found response
        }

        $quoteNote = QuoteNotes::find($id);
        return response()->json(['status' => true, 'data' => $quoteNote], 200);
    }
    public function destroyNote($id)
    {
        // Use the findModel helper to retrieve the rate
        $quoteNotes = findModel(QuoteNotes::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quoteNotes instanceof \Illuminate\Http\JsonResponse) {
            return $quoteNotes;  // Return the not found response
        }

        DB::transaction(function () use ($quoteNotes) {
            $quoteNotes->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Quote Note deleted successfully'
        ], 200);
    }

    public function statusNote(Request $request, $id)
    {
        // Use the statusUpdate helper to update status
        return statusUpdate(QuoteNotes::class, $id, [
            'status' => $request->status
        ]);
    }
}

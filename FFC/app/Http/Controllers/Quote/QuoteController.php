<?php

namespace App\Http\Controllers\Quote;

use App\Exports\QuoteExport;
use App\Http\Controllers\Controller;
use App\Models\Quote\Quote;
use App\Models\Quote\QuoteNotes;
use App\Services\Quote\QuoteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class QuoteController extends Controller
{
    protected QuoteService $quoteService;
    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function getVendorList(Request $request)
    {
        Log::info("*******************************");
        Log::info("Getting Vendor list for quotes");
        Log::info("*******************************");
        /*
        Process:
        1. First, it searches for vendors based on the provided port_id, destination_id, serviceType
        2. During the edit operation, when the rate_id and vendor_id are provided, it retrieves the data matching 
            those values and adds it to the existing dataset. This step is specifically for the edit functionality.
        */

        $isPortOfLoadingId = $request->input('portOfLoadingId') ?? false;
        $isPortOfDischargeId = $request->input('portOfDischargeId') ?? false;
        $isDestinationId = $request->input('destination_id') ?? false;
        $isServiceType = $request->input('serviceType') ?? false;
        $today = Carbon::now()->toDateString();

        $query = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->leftJoin('ports as loading_ports', 'rates.port_of_loading_id', '=', 'loading_ports.id')
            ->leftJoin('ports as discharge_ports', 'rates.port_of_discharge_id', '=', 'discharge_ports.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                'expiry',
                'rates.freight',
                'rates.fsc',
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

        // Apply conditions for filtering
        if ($isPortOfLoadingId) {
            $query->where('rates.port_of_loading_id', $isPortOfLoadingId);
        }
        if ($isPortOfDischargeId) {
            $query->where('rates.port_of_discharge_id', $isPortOfDischargeId);
        }
        if ($isDestinationId) {
            $query->where('rates.destination_id', $isDestinationId);
        }
        if ($isServiceType) {
            $query->where('rates.service_type_id', $isServiceType);
        }

        $query->where('rates.status', 'active');
        // ->groupBy('rates.id');

        // Additional query for vendor_id and rate_id (if required)
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
            // ->groupBy('rates.id');

            $query = $query->union($additionalQuery);
        }

        // Apply sorting after union
        $finalQuery = DB::table(DB::raw("({$query->toSql()}) as combined"))
            ->mergeBindings($query)
            ->orderBy('temp_sort_column', 'desc')
            ->orderBy('freight', 'asc');

        // Paginate the result
        $ratesCollection = $finalQuery->paginate(5);

        // Fetch and Attach Rate Charges
        $rateIds = $ratesCollection->pluck('rate_id'); // Get all rate IDs in the current page
        $rateCharges = DB::table('rate_charges')
            ->whereIn('rate_id', $rateIds)
            ->get()
            ->groupBy('rate_id');

        // Attach the charges to the rates
        foreach ($ratesCollection as $rate) {
            $rate->rate_charges = $rateCharges->get($rate->rate_id, []); // Attach charges or an empty array
        }

        // Return the result
        return response()->json([
            'status' => true,
            'message' => 'Records with additional data and proper sorting',
            'data' => $ratesCollection,
        ], 200);
    }


    public function getVendorListwithJson(Request $request)
    {
        Log::info("*******************************");
        Log::info("Getting Vendor list for quotes");
        Log::info("*******************************");
        /*
        Process:
        1. First, it searches for vendors based on the provided port_id, destination_id, serviceType
        2. During the edit operation, when the rate_id and vendor_id are provided, it retrieves the data matching 
            those values and adds it to the existing dataset. This step is specifically for the edit functionality.
        */

        $isPortOfLoadingId = $request->input('portOfLoadingId') ?? false;
        $isPortOfDischargeId = $request->input('portOfDischargeId') ?? false;
        $isDestinationId = $request->input('destination_id') ?? false;
        $isServiceType = $request->input('serviceType') ?? false;
        $today = Carbon::now()->toDateString();
        // Main query
        $query = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->leftJoin('ports as loading_ports', 'rates.port_of_loading_id', '=', 'loading_ports.id')
            ->leftJoin('ports as discharge_ports', 'rates.port_of_discharge_id', '=', 'discharge_ports.id')
            ->leftJoin('rate_charges', 'rate_charges.rate_id', '=', 'rates.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                'expiry',
                'rates.freight',
                'rates.fsc',
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
                // DB::raw("JSON_ARRAYAGG(JSON_OBJECT(
                //     'charge_id', rate_charges.id,
                //     'charge_name', rate_charges.charge_name,
                //     'amount', rate_charges.amount
                // )) as rate_charges"),
                DB::raw("COALESCE(
                    JSON_ARRAYAGG(
                        CASE
                            WHEN rate_charges.amount IS NOT NULL AND rate_charges.id IS NOT NULL AND rate_charges.charge_name IS NOT NULL
                            THEN JSON_OBJECT(
                                'amount', rate_charges.amount,
                                'charge_id', rate_charges.id,
                                'charge_name', rate_charges.charge_name
                            )
                        END
                    ), JSON_ARRAY()) as rate_charges"),
                DB::raw("0 as temp_sort_column")
            );

        // if ($isPortId) {
        //     $query->where('rates.port_id', $request->port_id);
        // }
        if ($isPortOfLoadingId) {
            $query->where('rates.port_of_loading_id', $isPortOfLoadingId);
        }
        if ($isPortOfDischargeId) {
            $query->where('rates.port_of_discharge_id', $isPortOfDischargeId);
        }
        if ($isDestinationId) {
            $query->where('rates.destination_id', $request->destination_id);
        }
        if ($isServiceType) {
            $query->where('rates.service_type_id', $request->serviceType);
        }

        $query->where('rates.status', 'active')->groupBy('rates.id');

        // Query for the additional record based on vendor_id and rate_id
        if ($request->has('vendor_id') && $request->has('rate_id')) {
            $vendorId = $request->input('vendor_id');
            $rateId = $request->input('rate_id');

            $additionalQuery = DB::table('rates')
                ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
                ->leftJoin('ports as loading_ports', 'rates.port_of_loading_id', '=', 'loading_ports.id') // Join for portOfLoadingId
                ->leftJoin('ports as discharge_ports', 'rates.port_of_discharge_id', '=', 'discharge_ports.id') // Join for portOfDischargeId
                ->leftJoin('rate_charges', 'rate_charges.rate_id', '=', 'rates.id')
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
                    // DB::raw("JSON_ARRAYAGG(JSON_OBJECT(
                    //     'charge_id', rate_charges.id,
                    //     'charge_name', rate_charges.charge_name,
                    //     'amount', rate_charges.amount
                    // )) as rate_charges"),
                    DB::raw("COALESCE(
                    JSON_ARRAYAGG(
                        CASE
                            WHEN rate_charges.amount IS NOT NULL AND rate_charges.id IS NOT NULL AND rate_charges.charge_name IS NOT NULL
                            THEN JSON_OBJECT(
                                'amount', rate_charges.amount,
                                'charge_id', rate_charges.id,
                                'charge_name', rate_charges.charge_name
                            )
                        END
                    ), JSON_ARRAY()) as rate_charges"),
                    DB::raw("1 as temp_sort_column")
                )
                ->where('rates.vendor_id', $vendorId)
                ->where('rates.id', $rateId)
                ->groupBy('rates.id');

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

        // $ratesCollection->getCollection()->transform(function ($rate) {
        //     $rate->rate_charges = json_decode('[' . $rate->rate_charges . ']', true);
        //     return $rate;
        // });
        foreach ($ratesCollection as $rate) {
            if (empty($rate->rate_charges)) {
                $rate->rate_charges = []; // Set as an empty array if no charges exist
            } else {
                $rate->rate_charges = json_decode($rate->rate_charges, true);
            }
        }
        // Return the result
        return response()->json([
            'status' => true,
            'message' => 'Records with additional data and proper sorting',
            'data' => $ratesCollection,
        ], 200);
    }

    public function index_old(Request $request)
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
            // 'quoteDetails.rate.vendor:id,company_name',
            // 'quoteDetails.vendor:id,company_name',  // Load vendor inside quoteDetails and select only 'id' and 'name'
            'quoteDetails.port:id,name',  // Load port inside quoteDetails and select only 'id' and 'name'
            'quoteDetails.destination:id,name',  // Load destination inside quoteDetails and select only 'id' and 'name'
            // 'quoteDetails.charges:quote_detail_id,charge_name,amount',
            'quoteDetails.rate:id,start_date',
            'quoteDetails.serviceType:id,name'
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

    public function index(Request $request)
    {
        $quotes = $this->quoteService->getAllQuotes($request);
        return response()->json([
            'status' => true,
            'data' => $quotes
        ], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $this->quoteValidation($request);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Quotes validation failed',
                'error' => $validatedData
            ], 422);
        }

        try {
            DB::beginTransaction();
            $quoteId = $this->quoteService->createQuote($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Quote created successfully",
                "quoteId" => $quoteId
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to save quotation data',
                "error" => $e->getMessage()
            ], 400);
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
            'quoteDetails.rate:id,start_date,vendor_id,port_of_loading_id,port_of_discharge_id,destination_id',
            'quoteDetails.rate.vendor:id,company_name',
            // 'quoteDetails.rate.port:id,name',
            // 'quoteDetails.rate.destination:id,name',
            'quoteDetails.charges:quote_detail_id,charge_name,amount',
        ])->find($id);

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

        $validatedData = $this->quoteValidation($request);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Quotes validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();
        try {
            $this->quoteService->updateQuote($request,  $quote);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Quote updated successfully",
                "quoteId" => $id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update quotation data',
                'error' => $e->getMessage()
            ], 400);
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

    private function quoteValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customerId' => 'required|integer',
            'quoteDetails' => 'required|array',
            // 'quoteDetails.*.serviceType' => 'required|integer',
            'quoteDetails.*.portOfLoadingId' => 'nullable|integer',
            'quoteDetails.*.portOfDischargeId' => 'nullable|integer',
            'quoteDetails.*.destinationId' => 'nullable|integer',
            'quoteNotes' => 'sometimes|array',
            'quoteNotes.*.title' => 'required_with:quoteNotes|string',
            'quoteNotes.*.description' => 'required_with:quoteNotes|string',
            'quoteNotes.*.tag' => 'nullable|string',
            'quoteNotes.*.pin' => 'nullable|boolean',
        ]);

        $validator->after(function ($validator) use ($request) {
            $quoteDetails = $request->input('quoteDetails', []);

            foreach ($quoteDetails as $index => $detail) {
                $loadingId = $detail['portOfLoadingId'] ?? null;
                $dischargeId = $detail['portOfDischargeId'] ?? null;

                if (is_null($loadingId) && is_null($dischargeId)) {
                    $validator->errors()->add("quoteDetails.$index.portOfLoadingId", "At least one port (loading or discharge) is required for item $index.");
                }
            }
        });

        // Check if validation fails
        if ($validator->fails()) {
            return $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }

    /**
     * Quote Notes
     */

    // Passing QuoteId
    public function getQuoteNote(Request $request, $quoteId)
    {
        // Use the findModel helper to retrieve the customer
        $quote = findModel(Quote::class, $quoteId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($quote instanceof \Illuminate\Http\JsonResponse) {
            return $quote;  // Return the not found response
        }

        $quoteNote = $this->quoteService->getQuoteNoteData($request, $quoteId);
        // QuoteNotes::with('user:id,first_name,last_name')->where('quote_id', $quoteId)->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $quoteNote], 200);
    }

    public function storeNote(Request $request)
    {
        $validatedData = $this->quoteNoteValidation($request);
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Quote Note validation failed',
                'error' => $validatedData
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
            ], 400); // Return error response
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

        $validatedData = $this->quoteNoteValidation($request);
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Quote Note validation failed',
                'error' => $validatedData
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
            ], 400); // Return error response
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

    private function quoteNoteValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'quoteId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return $validator->validated();
    }

    /**
     * Quotes Note End
     */


    public function excelExport(Request $request)
    {
        Log::info("*****************************");
        Log::info('Exporting User Excel sheet...');
        Log::info("*****************************");

        $quotes = $this->quoteService->getAllQuotes($request);
        // Export to Excel
        return Excel::download(new QuoteExport($quotes), 'Export_Quote_' . date('YmdHis') . '.xlsx');
    }

    public function generatePdf($id)
    {
        Log::info("*****************************");
        Log::info('Quote PDF Downloading...');
        Log::info("*****************************");
        $data = $this->quoteService->getPdfData($id);
        Log::info($data);

        $html = view('quote/quote_pdf', ['data' => $data[0]])->render();
        if (!empty($html)) {
            // Load the HTML and pass the data
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            // Get the raw PDF content
            $pdfContent = $dompdf->output();
            $base64Pdf = base64_encode($pdfContent);
            return response()->json([
                'pdf_base64' => $base64Pdf,
            ]);
            // return response()->streamDownload(
            //     fn() => print($dompdf->output()),
            //     'quote_pdf.pdf',
            //     [
            //         'Content-Type' => 'application/pdf',
            //         'Content-Disposition' => 'attachment; filename="quote_pdf.pdf"',
            //     ]
            // );
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Html View is empty. So not able to generate PDF'
            ], 400); // Return error response
        }


        // $data1 = [
        //     'quotes' => [
        //         ['port' => 'Baltimore', 'destination' => 'Abingdon', 'dray_fsc' => 12200],
        //         ['port' => 'dfa', 'destination' => 'dfsd', 'dray_fsc' => 12200],
        //         // Add more quotes as needed
        //     ],
        // ];

        // // $pdf = Pdf::loadView('quote/quote_pdf', compact('data'))->setPaper('a4', 'portrait');

        // // // $pdf = Pdf::loadView('quote/quote_pdf', ['data' => $data[0]])->setPaper('a4', 'portrait');
        // // // // Attempt to download
        // // return response()->streamDownload(function () use ($pdf) {
        // //     echo $pdf->output();
        // // }, 'quotation.pdf', [
        // //     'Content-Type' => 'application/pdf',
        // // ]);

        // // Encode as Base64
        // $pdfContent = $pdf->output();
        // $base64Pdf = base64_encode($pdfContent);
        // return response()->json([
        //     'pdf_base64' => $base64Pdf,
        // ]);
    }
}

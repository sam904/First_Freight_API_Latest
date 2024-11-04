<?php

namespace App\Services\Quote;

use App\Helpers\SearchHelper;
use App\Models\Quote\Quote;
use App\Models\Quote\QuoteNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuoteService
{
    protected $loginUser;

    public function __construct()
    {
        $this->loginUser =  Auth::user();
    }

    public function getAllQuotes(Request $request)
    {
        Log::info("*******************");
        Log::info("Quotes Search");
        Log::info("*******************");
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit') ?: 10;
        $sortColumn = $request->input('sortColumn') ?: 'id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        $isExport = $request->input('export') ?? false;
        $ids = $request->input('ids');

        $query = Quote::with([
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
        ]);

        // Apply filter by IDs if they are provided
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        // Get all column names of the 'Customers' table
        $model = new Quote();
        // Apply search filters
        $query = SearchHelper::applySearchFilters($query, $model, $request);

        if ($request->input('filterBy') == "port") {
            $query->whereHas('quoteDetails.port', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->input('filterBy') == "destination") {
            $query->whereHas('quoteDetails.destination', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->input('filterBy') == "customer") {
            $query->whereHas('customer', function ($q) use ($searchTerm) {
                $q->where('company_name', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->input('filterBy') == "serviceType") {
            $query->whereHas('quoteDetails.serviceType', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        // // Define the columns for sorting
        // $sortColumn = $sortColumn === 'customer_name' ? 'customers.company_name' : $sortColumn;
        // // Add a join if sorting by customer name
        // if ($sortColumn === 'customers.company_name') {
        //     $query->join('customers', 'quotes.customer_id', '=', 'customers.id');
        // }

        // Execute the query and get results
        return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
    }

    public function createQuote(Request $request)
    {
        Log::info("Creating Quoates by = " . $this->loginUser->id);
        $quote = Quote::create([
            "customer_id" => $request['customerId'],
            "quote_status" => $request["quoteStatus"],
            "created_by" =>  $this->loginUser->id,
        ]);
        Log::info($quote->id . " Quotes created succussfully");
        // Continue processing
        $this->storeQuoteDetails($request, $quote);
        return true;
    }

    public function updateQuote(Request $request, Quote $quote)
    {
        Log::info(message: "Quoate Request => " . $request);
        // delete all quote
        $this->deleteQuote($quote->id, 'Update');
        $quote->update([
            "customer_id" => $request['customerId'],
            "quote_status" => $request["quoteStatus"],
            "created_by" =>  $this->loginUser->id,
        ]);
        Log::info($quote->id . " Quotes created succussfully");
        // Continue processing
        $this->storeQuoteDetails($request, $quote);
        return true;
    }

    public function storeQuoteDetails(Request $request, $quote)
    {
        Log::info("Storing QuoteDetails...");
        foreach ($request->quoteDetails as $detail) {
            $quoteDetail = $quote->quoteDetails()->create([
                "freight" => $detail['freight'],
                "fsc" => $detail['fsc'],
                "quote_id" => $quote->id,
                "rate_id" => $detail['rateId'] ?? null,
                'service_type_id' => $detail['serviceType'] ?? null,
                "container_weight" => $detail['containerWeight'] ?? null,
                "port_id" => $detail['portId'],
                "destination_id" => $detail['destinationId'],
                // "vendor_id" => $detail['vendorId'],
                // "shipment_type" => $detail['shipmentType'],
            ]);
            Log::info("QuoteDetails stored successfully" . $quoteDetail->id);
            // Save charges for each detail
            Log::info("Storing Quote Charges....");
            foreach ($detail['charges'] as $charge) {
                $quoteDetail->charges()->create([
                    'charge_name' => $charge['chargeName'],
                    'amount' => $charge['amount'],
                ]);
            }
        }
    }

    /**
     * Summary of deleteQuote
     * @param mixed $id
     * @param mixed $operation => Operation can be update && delete.
     * When Operation = 'Update' that time delete only quote_details table.
     * When Operation = 'Delete' that time delete from quotes, quote_details, quote_charge tables.
     * @return bool
     */
    public function deleteQuote($id, $operation)
    {
        Log::info("Find the quote with its details and charges : " . $operation);
        $quote = Quote::with('quoteDetails.charges')->findOrFail($id);

        Log::info("Loop through each QuoteDetail and delete the related QuoteCharges" . $quote);
        foreach ($quote->quoteDetails as $quoteDetail) {
            $quoteDetail->charges()->delete(); // Delete related charges
        }

        Log::info("Now delete all QuoteDetails");
        $quote->quoteDetails()->delete();

        if ($operation == "Delete") {
            // Finally, delete the Quote
            $quote->delete();
        }

        return true;
    }

    public function saveNotes(Request $request)
    {
        QuoteNotes::create([
            'title' => $request['title'],
            'description' => $request['description'],
            'quote_id' => $request['quoteId'],
            'tag' => $request['tag'],
            'pin' => $request['pin'],
        ]);
        return true;
    }

    public function updateNote(Request $request, QuoteNotes $quoteNotes)
    {
        $quoteNotes->update([
            'title' => $request['title'],
            'description' => $request['description'],
            'quote_id' => $request['quoteId'],
            'tag' => $request['tag'],
            'pin' => $request['pin'],
        ]);
        return true;
    }
}

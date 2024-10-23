<?php

namespace App\Services\Quote;

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
                "rate_id" => $detail['rateId'],
                'service_type_id' => $detail['serviceType'],
                "container_weight" => $detail['containerWeight'] ?? null,
                // "shipment_type" => $detail['shipmentType'],
                // "port_id" => $detail['portId'],
                // "destination_id" => $detail['destinationId'],
                // "vendor_id" => $detail['vendorId'],
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

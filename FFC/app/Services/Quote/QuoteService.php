<?php

namespace App\Services\Quote;

use App\Models\Quote\Quote;
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
            "shipment_type" => $request["shipmentType"],
            "created_by" =>  $this->loginUser->id,
        ]);

        Log::info($quote->id . " Quotes created succussfully");
        // Continue processing
        $this->storeQuoteDetails($request, $quote);

        return true;
    }

    public function updateQuote(Request $request, Quote $quote)
    {
        Log::info(message: "Updating Quoates..." . $quote);

        // delete all quote
        $this->deleteQuote($quote->id, 'Update');

        $quote->update([
            "customer_id" => $request['customerId'],
            "quote_status" => $request["quoteStatus"],
            "shipment_type" => $request["shipmentType"],
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
                "container_weight" => $detail['containerWeight'],
                "shipment_type" => $detail['shipmentType'],
                "quote_id" => $quote->id,
                // "port_id" => $detail['portId'],
                // "destination_id" => $detail['destinationId'],
                // "vendor_id" => $detail['vendorId'],
                "rate_id" => $detail['rateId'],
            ]);
            Log::info("QuoteDetails stored successfully" . $quoteDetail->id);

            // Save charges for each detail
            Log::info("Storing Quote Charges");
            foreach ($detail['charges'] as $charge) {
                $quoteDetail->charges()->create([
                    'charge_name' => $charge['chargeName'],
                    'amount' => $charge['amount'],
                ]);
            }
        }
    }

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
}

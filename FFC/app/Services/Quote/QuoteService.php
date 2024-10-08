<?php

namespace App\Services\Quote;

use App\Models\Quote\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuoteService
{

    public function createQuote(Request $request)
    {
        Log::info("1");
        $loginUser = Auth::user();

        $quote = Quote::create([
            "customer_id" => $request['customerId'],
            "quote_status" => $request["quoteStatus"],
            "created_by" =>  $loginUser->id,
        ]);

        Log::info($quote);
        // Continue processing
        $this->storeQuoteDetails($request, $quote);

        return true;
    }

    public function storeQuoteDetails(Request $request, $quote)
    {
        Log::info("3");
        foreach ($request->quoteDetails as $detail) {
            Log::info("4");
            $quoteDetail = $quote->quoteDetails()->create([
                "freight" => $detail['freight'],
                "fsc" => $detail['fsc'],
                "container_weight" => $detail['containerWeight'],
                "quote_id" => $quote->id,
                "port_id" => $detail['portId'],
                "destination_id" => $detail['destinationId'],
                "vendor_id" => $detail['vendorId'],
                "rate_id" => $detail['rateId'],
            ]);

            // Save charges for each detail
            Log::info($quoteDetail);
            foreach ($detail['charges'] as $charge) {
                $quoteDetail->charges()->create([
                    'charge_name' => $charge['charge_name'],
                    'amount' => $charge['amount'],
                ]);
            }
        }
    }
}

<?php

namespace App\Services\Rate;

use App\Models\Rate\Rate;
use App\Models\Rate\RateCharge;
use Illuminate\Http\Request;

class RateService
{
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
}

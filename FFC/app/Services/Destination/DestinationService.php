<?php

namespace App\Services\Destination;

use App\Models\Destination\Destination;
use Illuminate\Http\Request;

class DestinationService
{

    public function createDestination(Request $request)
    {
        $destination = Destination::create([
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
            'county_id' => $request['county'],
        ]);
        return true;
    }


    public function updateDestination(Request $request, $id)
    {
        try {
            $destination = Destination::findOrFail($id);
        } catch (\Exception $e) {
            return ['errorMsg' => "Destination not found"];
        }

        $destination->update([
            'name' => $request['name'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
            'county_id' => $request['county'],
        ]);

        return true;
    }
}

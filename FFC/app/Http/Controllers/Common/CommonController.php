<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;

class CommonController extends Controller
{
    public function country()
    {
        $country = Country::all();
        return response()->json(['status' => true, 'data' => $country], 200);
    }

    public function state($countryId)
    {
        $states = State::where("country_id", $countryId)->get();
        if ($states->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No state found with the specified country'
            ], 404);
        }
        return response()->json(['status' => true, 'data' => $states], 200);
    }

    public function city($stateId)
    {
        $cities = City::where("state_id", $stateId)->get();

        if ($cities->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No citi found with the specified state'
            ], 404);
        }

        return response()->json(['status' => true, 'data' => $cities], 200);

        // Get city along with its state and country
        // $city = City::with('state.country')->find(1);
        // $country = $city->state->country; // Access the country through state
        // echo $country->name;
    }
}

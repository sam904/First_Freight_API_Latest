<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer\Customer;
use App\Models\Destination\Destination;
use App\Models\Port\Port;
use App\Models\State;
use App\Models\Vendor;
use Illuminate\Http\Request;

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


    public function getAllVendorList(Request $request)
    {
        $vendorList = Vendor::orderBy("id", "desc")->get();
        return response()->json(['status' => true, 'data' => $vendorList], 200);
    }

    public function getAllPortList(Request $request)
    {
        $portList = Port::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $portList], 200);
    }

    public function getAllDestinationList(Request $request)
    {
        $destinationList = Destination::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $destinationList], 200);
    }

    public function getAllCustomerList(Request $request)
    {
        $customerList = Customer::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $customerList], 200);
    }
}

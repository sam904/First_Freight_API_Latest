<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $query = DB::table(table: 'vendors')->select('id', 'company_name');
        if ($request->has('vendor_name')) {
            $query->where('company_name', 'LIKE', '%' . $request->vendor_name . '%');
        }
        $query->where('status', 'active');
        $query = $query->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $query], 200);
    }

    public function getAllPortList(Request $request)
    {
        $query = DB::table('ports')->select('id', 'name');
        if ($request->has('port_name')) {
            $query->where('name', 'LIKE', '%' . $request->port_name . '%');
        }
        $query->where('status', 'active');
        $query = $query->orderBy('id', 'desc')->get();

        return response()->json(['status' => true, 'data' => $query], 200);
    }

    public function getAllDestinationList(Request $request)
    {
        $query = DB::table(table: 'destinations')->select('id', 'name');
        if ($request->has('destination_name')) {
            $query->where('name', 'LIKE', '%' . $request->destination_name . '%');
        }
        $query->where('status', 'active');
        $query = $query->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $query], 200);
    }

    public function getAllCustomerList(Request $request)
    {
        $query = DB::table(table: 'customers')
            ->select('id', 'company_name');
        if ($request->has('customer_name')) {
            $query->where('company_name', 'LIKE', '%' . $request->customer_name . '%');
        }
        $query->where('status', 'active');
        $query = $query->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $query], 200);
    }
}

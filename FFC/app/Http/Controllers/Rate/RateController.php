<?php

namespace App\Http\Controllers\Rate;

use App\Http\Controllers\Controller;
use App\Models\Rate\Rate;
use App\Services\Rate\RateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{

    protected $rateService;
    public function __construct(RateService $rateService)
    {
        $this->rateService = $rateService;
    }

    public function index()
    {
        $today = Carbon::now()->toDateString();

        $rates = DB::table('rates')
            ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
            ->join('ports', 'rates.port_id', '=', 'ports.id')
            ->join('destinations', 'rates.destination_id', '=', 'destinations.id')
            ->select(
                'rates.id as rate_id',
                'vendors.company_name as vendor_name',
                'ports.name as port_name',
                'destinations.name as destination_name',
                'freight',
                // 'start_date',
                DB::raw("DATE_FORMAT(rates.start_date, '%m/%d/%y') as rate_received"),
                // DB::raw('DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY) as expiry_date'),
                // DB::raw('GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0) as expiry_days'),
                DB::raw("CONCAT(
                    DATE_FORMAT(DATE_ADD(rates.start_date, INTERVAL GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) DAY), '%m/%d/%Y'),
                    ', ',
                    GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0),
                    ' Days Left'
                ) as rate_validity"),
                'rates.status',
            )->paginate(10);

        return response()->json(['status' => true, 'data' => $rates], 200);

        // $rates = DB::table('rates')
        //     ->join('vendors', 'rates.vendor_id', '=', 'vendors.id')
        //     ->join('ports', 'rates.port_id', '=', 'ports.id')
        //     ->join('destinations', 'rates.destination_id', '=', 'destinations.id')
        //     ->select(
        //         'rates.id as rate_id',
        //         'vendors.company_name as vendor_name',
        //         'ports.name as port_name',
        //         'destinations.name as destination_name',
        //         'freight',
        //         'start_date',
        //         // DB::raw('DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY) as expiry_date'),
        //         // DB::raw('GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0) as expiry_days'),
        //         DB::raw("CONCAT(
        //                 DATE_FORMAT(DATE_ADD(rates.start_date, INTERVAL GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) DAY), '%m/%d/%Y'),
        //                 ', ',
        //                 GREATEST(DATEDIFF(DATE_ADD(rates.start_date, INTERVAL rates.expiry DAY), CURDATE()), 0),
        //                 ' Days Left'
        //             ) as rate_validity"),
        //         'rates.status',
        //         // Calculate how many days have passed since the start date
        //         // DB::raw("DATEDIFF('$today', rates.start_date) as days_passed"),
        //         // Calculate remaining days
        //         DB::raw("GREATEST(rates.expiry - DATEDIFF('$today', rates.start_date), 0) as remaining_days")

        //     )
        //     ->paginate(10);
        // // Now calculate `new_expiry_date` in PHP based on remaining_days
        // foreach ($rates as $rate) {
        //     $rate->new_expiry_date = Carbon::parse($rate->start_date)->addDays($rate->remaining_days)->toDateString();
        // }
    }

    public function store(Request $request)
    {
        $validatedData = $this->rateValidateData($request);
        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Rate validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction
        try {
            $this->rateService->createRate($request);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Rate created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert rate data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert rate data',
                "error" => $e->getMessage()
            ], 500); // Return error response
        }
    }

    public function edit($id)
    {
        // Use the findModel helper to retrieve the customer
        $rate = findModel(Rate::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($rate instanceof \Illuminate\Http\JsonResponse) {
            return $rate;  // Return the not found response
        }

        $rateResult = Rate::with('charges')->find($id);
        return response()->json(['status' => true, 'data' => $rateResult], 200);
    }

    public function update(Request $request, $id)
    {
        // Use the findModel helper to retrieve the customer
        $rate = findModel(Rate::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($rate instanceof \Illuminate\Http\JsonResponse) {
            return $rate;  // Return the not found response
        }

        $validatedData = $this->rateValidateData($request);
        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Rate validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction

        try {
            $customerMsg = $this->rateService->updateRate($request,  $rate, $id);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Rate updated successfully"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update rate data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update rate data',
                'error' => $e->getMessage()
            ], 500); // Return error response
        }
    }
    public function status(Request $request, $id)
    {
        // Use the statusUpdate helper to update status
        return statusUpdate(Rate::class, $id, [
            'status' => $request->status
        ]);
    }

    public function destroy($id)
    {
        // Use the findModel helper to retrieve the rate
        $rate = findModel(Rate::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($rate instanceof \Illuminate\Http\JsonResponse) {
            return $rate;  // Return the not found response
        }

        DB::transaction(function () use ($rate) {
            // Delete the rate record
            $rate->charges()->delete();
            $rate->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Rate deleted successfully'
        ], 200);
    }

    public function rateValidateData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'port_id' => 'required',
            'destination_id' => 'required',
            'start_date' => 'required|date',
            'expiry' => 'required',
            'freight' => 'required',
            'fsc' => 'nullable'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }
}
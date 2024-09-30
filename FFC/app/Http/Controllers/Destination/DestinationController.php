<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Models\Destination\County;
use App\Models\Destination\Destination;
use App\Services\Destination\DestinationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DestinationController extends Controller
{

    protected $destinationService;
    public function __construct(DestinationService $destinationService)
    {
        $this->destinationService = $destinationService;
    }

    public function index()
    {
        $destinations = Destination::paginate(10);
        return response()->json(['status' => true, 'data' => $destinations], 200);
    }


    public function store(Request $request)
    {
        DB::beginTransaction();  // Start the transaction
        try {
            $validatedData = $this->destinationValidateData($request);
            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Destination validation failed',
                    'error' => $validatedData
                ], 422);
            }

            $this->destinationService->createDestination($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Destination created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to destination vendor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($destinationId)
    {
        // Use the findModel helper to retrieve the destination
        $destination = findModel(Destination::class, $destinationId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($destination instanceof \Illuminate\Http\JsonResponse) {
            return $destination;  // Return the not found response
        }

        return response()->json(['status' => true, 'data' => $destination], 200);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();  // Start the transaction

        try {

            $validatedData = $this->destinationValidateData($request);

            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Destination validation failed',
                    'error' => $validatedData
                ], 422);
            }

            $destinationMsg = $this->destinationService->updateDestination($request, $id);

            DB::commit();

            if (isset($destinationMsg['errorMsg'])) {
                return response()->json([
                    'status' => false,
                    'message' => $destinationMsg['errorMsg']
                ], 404); // Return only the error message
            }

            return response()->json([
                'status' => true,
                'message' => "Destination updated successfully",
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            return response()->json([
                'status' => false,
                'message' => 'Failed to update destination data',
                'error' => $e->getMessage()
            ], 500); // Return error response
        }
    }
    public function destroy($destinationId)
    {
        // Use the findModel helper to retrieve the destination
        $destination = findModel(Destination::class, $destinationId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($destination instanceof \Illuminate\Http\JsonResponse) {
            return $destination;  // Return the not found response
        }
        DB::transaction(function () use ($destination) {
            // Delete the destination record
            $destination->delete();
        });
        return response()->json(['status' => true, 'message' => 'Destination deleted successfully'], 200);
    }

    public function status(Request $request, $destinationId)
    {
        return statusUpdate(Destination::class, $destinationId, [
            'status' => $request->status
        ]);
    }

    public function county()
    {
        $county = County::all();
        return response()->json(['status' => true, 'data' => $county], 200);
    }

    public function destinationValidateData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'county' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }
}

<?php

namespace App\Http\Controllers\Destination;

use App\Http\Controllers\Controller;
use App\Imports\DestinationImport;
use App\Models\Destination\County;
use App\Models\Destination\Destination;
use App\Services\Destination\DestinationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DestinationController extends Controller
{

    protected $destinationService;
    public function __construct(DestinationService $destinationService)
    {
        $this->destinationService = $destinationService;
    }

    public function index(Request $request)
    {
        $destinations = $this->destinationService->getAllDestination($request);
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
                'message' => 'Failed to insert destination data',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function edit($id)
    {
        // Use the findModel helper to retrieve the destination
        $destination = findModel(Destination::class, $id);
        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($destination instanceof \Illuminate\Http\JsonResponse) {
            return $destination;  // Return the not found response
        }
        return response()->json(['status' => true, 'data' => $destination], 200);
    }

    public function update(Request $request, $id)
    {
        // Use the findModel helper to retrieve the destination
        $destination = findModel(Destination::class, $id);
        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($destination instanceof \Illuminate\Http\JsonResponse) {
            return $destination;  // Return the not found response
        }
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
            $this->destinationService->updateDestination($request, $destination);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Destination updated successfully"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update Destination data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Destination data',
                'error' => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function destroy($id)
    {
        // Use the findModel helper to retrieve the destination
        $destination = findModel(Destination::class, $id);
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

    public function status(Request $request, $id)
    {
        return statusUpdate(Destination::class, $id, [
            'status' => $request->status
        ]);
    }

    public function destinationValidateData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }

    public function excelUpload(Request $request)
    {
        Log::info("*****************************");
        Log::info('Importing Destination Excel sheet...');
        Log::info("*****************************");

        $request->validate([
            'uploadFile' => 'required|mimes:xlsx,xls,csv',
            // 'updatedColumns' => 'required|array'
        ]);

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();
            Excel::import(new DestinationImport($updatedColumns), $request->file('uploadFile'));
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Excel Upload Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }
}

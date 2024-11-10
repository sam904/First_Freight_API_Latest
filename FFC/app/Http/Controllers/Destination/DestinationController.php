<?php

namespace App\Http\Controllers\Destination;

use App\Exports\DestinationExport;
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
            'state' => 'required|integer',
            'country' => 'required|integer',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }

    /**
     * Zero Count means there are already record exit while uploading
     */
    public function excelUpload(Request $request)
    {
        Log::info("*****************************");
        Log::info('Importing Destination Excel sheet...');
        Log::info("*****************************");

        $validated = $request->validate([
            'uploadFile' => 'required|mimes:xlsx,xls',
            // 'updatedColumns' => 'required|array'
        ]);

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();

            // Instantiate PortImport before the import
            $excelImport = new DestinationImport($updatedColumns);

            // Perform the import
            Excel::import($excelImport, $request->file('uploadFile'));

            // Get Rows inserted count
            $validRowcount = $excelImport->getValidRowCount();
            Log::info("Valid rows count : " . $validRowcount);

            // Get Existing row count
            $existingRowcount = $excelImport->getExistingRowCount();
            Log::info("Existing Row Count : " . $existingRowcount[0]);
            Log::info("Existing Row Record : ", $existingRowcount[1]);

            // Check for any errors after the import
            $errorsResponse = $excelImport->getErrorsResponse();
            if ($errorsResponse) {
                DB::rollBack();
                return response()->json($errorsResponse, 400);
            }

            // Manually call afterImport to handle further processing
            $excelImport->afterImport();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Excel Upload Successfully',
                // 'inserted_records_count' => $validRowcount,
                // 'existing_records_count' => $existingRowcount[0],
                // 'existing_records_row_numbers' => implode(', ', $existingRowcount[1]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during the import process.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function excelUploadOld(Request $request)
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

    public function excelExport(Request $request)
    {
        Log::info("*****************************");
        Log::info('Exporting Destination Excel sheet...');
        Log::info("*****************************");

        $destinations = $this->destinationService->getAllDestination($request);
        // Export to Excel
        return Excel::download(new DestinationExport($destinations), 'Export_Destination_' . date('YmdHis') . '.xlsx');
    }
}

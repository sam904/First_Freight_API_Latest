<?php

namespace App\Http\Controllers\Port;

use App\Exports\PortExport;
use App\Http\Controllers\Controller;
use App\Imports\PortImport;
use App\Models\Port\Port;
use App\Models\Port\PortType;
use App\Services\Port\PortService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PortController extends Controller
{

    protected $portService;
    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    public function index(Request $request)
    {
        $ports = $this->portService->getAllPort($request);
        // $ports = Port::paginate(10);
        return response()->json(['status' => true, 'data' => $ports], 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();  // Start the transaction
        try {
            $validatedData = $this->portValidateData($request);
            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Port validation failed',
                    'error' => $validatedData
                ], 422);
            }

            $this->portService->createPort($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Port created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to save vendor data',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function edit($id)
    {
        // Use the findModel helper to retrieve the port
        $port = findModel(Port::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($port instanceof \Illuminate\Http\JsonResponse) {
            return $port;  // Return the not found response
        }

        $ports = Port::with(['portTerminals'])->find($id);
        return response()->json(['status' => true, 'data' => $ports], 200);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();  // Start the transaction

        try {

            $validatedData = $this->portValidateData($request);

            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Port validation failed',
                    'error' => $validatedData
                ], 422);
            }

            $portMsg = $this->portService->updatePort($request, $id);

            DB::commit();

            if (isset($portMsg['errorMsg'])) {
                return response()->json([
                    'status' => false,
                    'message' => $portMsg['errorMsg']
                ], 404); // Return only the error message
            }

            return response()->json([
                'status' => true,
                'message' => "Port updated successfully",
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            return response()->json([
                'status' => false,
                'message' => 'Failed to update port data',
                'error' => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function destroy($portId)
    {
        // Use the findModel helper to retrieve the port
        $port = findModel(Port::class, $portId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($port instanceof \Illuminate\Http\JsonResponse) {
            return $port;  // Return the not found response
        }
        DB::transaction(function () use ($port) {

            // Delete all related records
            $port->portTerminals()->delete();
            Log::info("Port Terminal Deleted successfully.");
            // Delete the vendor record
            $port->delete();
        });
        return response()->json(['status' => true, 'message' => 'Port deleted successfully'], 200);
    }

    public function status(Request $request, $portId)
    {
        return statusUpdate(Port::class, $portId, [
            'status' => $request->status
        ]);
    }

    public function portType($id = null)
    {
        if ($id) {
            $portType = findModel(PortType::class, $id);
            // Check if the returned value is a JSON response (meaning the model was not found)
            if ($portType instanceof \Illuminate\Http\JsonResponse) {
                return $portType;  // Return the not found response
            }
            $portType = Port::with(['portType', 'portTerminals'])->where('port_type_id', $id)->get();
            if (!$portType) {
                return response()->json(['status' => false, 'message' => 'PortType not found'], 404);
            }
            return response()->json(['status' => true, 'data' => $portType], 200);
        }

        $portType = PortType::all();
        return response()->json(['status' => true, 'data' => $portType], 200);
    }

    public function portValidateData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'port_type' => 'required|integer',
            'name' => 'required|string',
            'state' => 'required|integer',
            'country' => 'required|integer',

            // Terminal validation for each item in the array
            'terminals' => 'nullable|array',
            'terminals.*' => 'nullable|string',
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
        Log::info('Importing Port Excel sheet...');
        Log::info("*****************************");

        try {
            $validatedData = $request->validate([
                'uploadFile' => 'required|file|mimes:xlsx,xls',
                // 'updatedColumns' => 'required|array'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();

            // Instantiate PortImport before the import
            $excelImport = new PortImport($updatedColumns);

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
        Log::info('Importing Port Excel sheet...');
        Log::info("*****************************");

        $request->validate([
            'uploadFile' => 'required|mimes:xlsx,xls,csv',
            // 'updatedColumns' => 'required|array'
        ]);

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();
            Excel::import(new PortImport($updatedColumns), $request->file('uploadFile'));
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
        Log::info('Exporting Port Excel sheet...');
        Log::info("*****************************");

        $port = $this->portService->getAllPort($request);
        // Export to Excel
        return Excel::download(new PortExport($port), 'Export_Port_' . date('YmdHis') . '.xlsx');
    }


    /**
     * Get Port Type wise data
     */

    public function getPortDataByPortType($id)
    {
        $ports = $this->portService->getAllPort($id);
        // $ports = Port::paginate(10);
        return response()->json(['status' => true, 'data' => $ports], 200);
    }
}

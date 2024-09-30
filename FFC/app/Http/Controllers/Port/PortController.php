<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\Port\Port;
use App\Models\Port\PortType;
use App\Services\Port\PortService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PortController extends Controller
{

    protected $portService;
    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    public function index()
    {
        $ports = Port::paginate(10);
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
            ], 500);
        }
    }

    public function edit($portId)
    {
        // Use the findModel helper to retrieve the port
        $port = findModel(Port::class, $portId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($port instanceof \Illuminate\Http\JsonResponse) {
            return $port;  // Return the not found response
        }

        return response()->json(['status' => true, 'data' => $port], 200);
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
            ], 500); // Return error response
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

    public function portType()
    {
        $portType = PortType::all();
        return response()->json(['status' => true, 'data' => $portType], 200);
    }

    public function portValidateData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'port_type' => 'required|string',
            'name' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',

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
}

<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Common\ServiceType;
use App\Services\Common\ServiceTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServiceTypeController extends Controller
{

    protected $serviceTypeService;
    public function __construct(ServiceTypeService $serviceTypeService)
    {
        $this->serviceTypeService = $serviceTypeService;
    }

    public function index()
    {
        $services = ServiceType::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $services], 200);
    }


    public function store(Request $request)
    {
        $validatedData = $this->servicetypeValidateData($request);
        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Service Type validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction
        try {
            $this->serviceTypeService->createServiceType($request);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Service Type created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert Service Type data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert Service Type data',
                "error" => $e->getMessage()
            ], 500); // Return error response
        }
    }

    public function edit($id)
    {
        // Use the findModel helper to retrieve the customer
        $serviceType = findModel(ServiceType::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($serviceType instanceof \Illuminate\Http\JsonResponse) {
            return $serviceType;  // Return the not found response
        }
        $services = ServiceType::find($id);
        return response()->json(['status' => true, 'data' => $services], 200);
    }

    public function update(Request $request, $id)
    {
        // Use the findModel helper to retrieve the customer
        $serviceType = findModel(ServiceType::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($serviceType instanceof \Illuminate\Http\JsonResponse) {
            return $serviceType;  // Return the not found response
        }
        $validatedData = $this->servicetypeValidateData($request);
        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Service Type validation failed',
                'error' => $validatedData
            ], 422);
        }
        DB::beginTransaction();  // Start the transaction
        try {
            $this->serviceTypeService->updateServiceType($request, $serviceType);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Service Type updated successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update Service Type data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Service Type data',
                "error" => $e->getMessage()
            ], 500); // Return error response
        }
    }

    public function status(Request $request, $id)
    {
        // Use the statusUpdate helper to update status
        return statusUpdate(ServiceType::class, $id, [
            'status' => $request->status
        ]);
    }

    public function destroy($id)
    {
        // Use the findModel helper to retrieve the rate
        $serviceType = findModel(ServiceType::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($serviceType instanceof \Illuminate\Http\JsonResponse) {
            return $serviceType;  // Return the not found response
        }

        DB::transaction(function () use ($serviceType) {
            // Delete the rate record 
            $serviceType->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Service Type deleted successfully'
        ], 200);
    }

    public function servicetypeValidateData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }
}

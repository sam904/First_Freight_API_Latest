<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\OrderStatusMaster;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderStatusMasterController extends Controller
{

    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    public function index(Request $request)
    {
        $statusMaster = $this->orderService->getAllOrderStatus($request);
        return response()->json([
            'status' => true,
            'data' => $statusMaster
        ], 200);
    }

    public function store(Request $request)
    {
        Log::info("Saving Order Status Master Details....");
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Order Status Master validation failed',
                'error' => $e->errors()
            ], 422);
        }
        DB::beginTransaction();  // Start the transaction
        try {
            $this->orderService->createStatusMaster($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Order Status Master created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to save Order Status Master data',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function edit($id)
    {
        // Use the findModel helper to retrieve the port
        $orderStatusMaster = findModel(OrderStatusMaster::class, $id);
        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($orderStatusMaster instanceof \Illuminate\Http\JsonResponse) {
            return $orderStatusMaster;  // Return the not found response
        }
        return response()->json(['status' => true, 'data' => $orderStatusMaster], 200);
    }

    public function update(Request $request, $id)
    {
        Log::info("Updating Order Status Master Details....");
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Order Status Master validation failed',
                'error' => $e->errors()
            ], 422);
        }

        // Use the findModel helper to retrieve the customer
        $orderStatusMaster = findModel(OrderStatusMaster::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($orderStatusMaster instanceof \Illuminate\Http\JsonResponse) {
            return $orderStatusMaster;  // Return the not found response
        }

        DB::beginTransaction();  // Start the transaction
        try {
            $this->orderService->updateStatusMaster($request, $orderStatusMaster);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Order Status Master updated successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update order status master data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order status master',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function destroy($id)
    {
        // Use the findModel helper to retrieve the OrderStatusMaster
        $orderStatusMaster = findModel(OrderStatusMaster::class, $id);
        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($orderStatusMaster instanceof \Illuminate\Http\JsonResponse) {
            return $orderStatusMaster;  // Return the not found response
        }
        DB::transaction(function () use ($orderStatusMaster) {
            $orderStatusMaster->delete();
        });
        return response()->json(['status' => true, 'message' => 'Order Status Master deleted successfully'], 200);
    }

    public function status(Request $request, $id)
    {
        return statusUpdate(OrderStatusMaster::class, $id, [
            'status' => $request->status
        ]);
    }
}

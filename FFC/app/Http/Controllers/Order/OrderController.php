<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    public function index(Request $request)
    {
        $order = $this->orderService->getAllOrders($request);

        return response()->json([
            'status' => true,
            'data' => $order
        ], 200);
    }

    public function store(Request $request)
    {
        Log::info("*****************************");
        Log::info('Saving Order Details...');
        Log::info("*****************************");

        // Validate request
        $validatedData = $this->validateOrder($request);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Order validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction
        try {
            // Save order
            $order = $this->orderService->saveOrder($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Order created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert order data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert order data',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function edit($id)
    {
        // Use the findModel helper to retrieve the port
        $order = findModel(Order::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($order instanceof \Illuminate\Http\JsonResponse) {
            return $order;  // Return the not found response
        }

        return response()->json(['status' => true, 'data' => $order], 200);
    }

    public function status(Request $request, $id)
    {
        // Use the statusUpdate helper to update status
        return statusUpdate(Order::class, $id, [
            'status' => $request->status
        ]);
    }


    public function destroy($id)
    {
        // Use the findModel helper to retrieve the port
        $order = findModel(Order::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($order instanceof \Illuminate\Http\JsonResponse) {
            return $order;
        }
        DB::transaction(function () use ($order) {
            // Delete all related records
            $order->orderStatuses()->delete();
            $order->orderDetails()->delete();
            $order->delete();
        });
        return response()->json(['status' => true, 'message' => 'Order deleted successfully'], 200);
    }

    public function validateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customerId' => 'required|integer|exists:customers,id',
            'quoteId' => 'nullable|integer|exists:quotes,id',
            'receivedDate' => 'required|date',
            'addressId' => 'required|integer|exists:customers,id',
            'containerNo' => 'nullable|string|max:255',
            'containerSize' => 'nullable|string|max:255',
            'po' => 'nullable|string|max:255',
            'cpo' => 'nullable|string|max:255',
            'overweight' => 'required|in:Yes,No',
            'orderDetails' => 'required|array',
            'orderDetails.*.serviceTypeId' => 'required|integer|exists:service_types,id',
            'orderDetails.*.createdBy' => 'required|integer|exists:users,id',
            'orderDetails.*.vendorId' => 'required|integer|exists:vendors,id',
            'orderDetails.*.portId' => 'required|integer|exists:ports,id',
            'orderDetails.*.destinationId' => 'required|integer|exists:destinations,id',
            'orderDetails.*.transhipmentPortId' => 'nullable|integer|exists:ports,id',
            'orderDetails.*.orderStatusId' => 'required|exists:order_status_masters,id',
            'orderDetails.*.shipper' => 'nullable|string|max:255',
            'orderDetails.*.shipperAddress' => 'nullable|string|max:255',
            'orderDetails.*.consignee' => 'nullable|string|max:255',
            'orderDetails.*.consigneeAddress' => 'nullable|string|max:255',
            'orderDetails.*.buyer' => 'nullable|string|max:255',
            'orderDetails.*.buyerAddress' => 'nullable|string|max:255',
            'orderDetails.*.notifyParty' => 'nullable|string|max:255',
            'orderDetails.*.bookingRequestSentDate' => 'nullable|date',
            'orderDetails.*.bookingDate' => 'nullable|date',
            'orderDetails.*.bcSentToShipper' => 'nullable|date',
            'orderDetails.*.bl' => 'nullable|string|max:255',
            'orderDetails.*.seal' => 'nullable|string|max:255',
            'orderDetails.*.weight' => 'nullable|string|max:255',
            'orderDetails.*.pallets' => 'nullable|integer',
            'orderDetails.*.etd' => 'nullable|date',
            'orderDetails.*.eta' => 'nullable|date',
            'orderDetails.*.streamshipLine' => 'nullable|string|max:255',
            'orderDetails.*.dischargeDate' => 'nullable|date',
            'orderDetails.*.cargoReadyDate' => 'nullable|date',
            'orderDetails.*.vesselVoyage' => 'nullable|string|max:255',
            'orderDetails.*.commodity' => 'nullable|string|max:255',
            'orderDetails.*.siCutOff' => 'nullable|date',
            'orderDetails.*.vgmCutOff' => 'nullable|date',
            'orderDetails.*.cyCutOff' => 'nullable|date',
            'orderDetails.*.isf' => 'nullable|date',
            'orderDetails.*.isfDate' => 'nullable|date',
            'orderDetails.*.isfNo' => 'nullable|string|max:255',
            'orderDetails.*.customFiled' => 'nullable|in:Yes,No',
            'orderDetails.*.lastFreeDay' => 'nullable|date',
            'orderDetails.*.freightLocation' => 'nullable|string|max:255',
            'orderDetails.*.dangerousGoods' => 'nullable|in:Yes,No',
            'orderDetails.*.freightPrepaid' => 'nullable|in:Yes,No',
            'orderDetails.*.allInclusiveRate' => 'nullable|numeric',
            'orderDetails.*.htsCode' => 'nullable|string|max:255',
            'orderDetails.*.firmCode' => 'nullable|string|max:255',
            'orderDetails.*.specialInstructions' => 'nullable|string|max:500',
            'orderDetails.*.notes' => 'nullable|string|max:500',
            'orderDetails.*.deliveryOrderSentDate' => 'nullable|date',
            'orderDetails.*.deliveryVendorConfirm' => 'nullable|in:Yes,No',
            'orderDetails.*.deliveryPickedUpDate' => 'nullable|date',
            'orderDetails.*.deliveryScheduleDate' => 'nullable|date',
            'orderDetails.*.deliveryEmptyReturnDate' => 'nullable|date',
            'orderDetails.*.deliveryEmptyPickUpCutoffDate' => 'nullable|date',
            'orderDetails.*.transmitTime' => 'nullable|date',
            'orderDetails.*.deliveryMode' => 'nullable|string|max:255',
            'orderDetails.*.deliveryFreeDays' => 'nullable|integer',
            'orderDetails.*.mscChassis' => 'nullable|in:Yes,No',
            'orderDetails.*.pierpassFees' => 'nullable|numeric',
            'orderDetails.*.cleanTruckFees' => 'nullable|numeric',
            'orderDetails.*.accessorialCharges' => 'nullable|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }
}

<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusMaster;
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

        $order = Order::with([
            'customer:id,company_name',
            'address:id,company_name',
            'quote:id',
            'orderContainerDetails',
            'orderDetails' => function ($query) {
                $query->with([
                    'deliveries' => function ($query) {
                        $query->with([
                            'serviceType:id,name',
                            'portOfLoading:id,name',
                            'portOfDischarge:id,name',
                            'destination:id,name',
                            'vendor:id,company_name',
                            'transhipmentPort:id,name',
                            'createdBy:id,first_name,last_name',
                            'statuses' => function ($query) {
                                $query->with([
                                    'deliveryStatus:id,name'
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ])->findOrFail($id); // Use findOrFail for fetching the specific Order for editing


        return response()->json(['status' => true, 'data' => $order], 200);
    }


    public function update(Request $request, $id)
    {
        Log::info("*****************************");
        Log::info('Updating Order Details...');
        Log::info("*****************************");

        // Use the findModel helper to retrieve the order
        $order = findModel(Order::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($order instanceof \Illuminate\Http\JsonResponse) {
            return $order;  // Return the not found response
        }

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
            $order = $this->orderService->updateOrder($request,  $order);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Order updated successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update order data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order data',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }
    }


    public function getOrderStatusByServiceType($id)
    {
        $data = OrderStatusMaster::where('service_type_id', $id)->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
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
            'customerId' => 'required|integer',
            'quoteId' => 'nullable|integer',
            'receivedDate' => 'required|date',
            'addressId' => 'required|integer',
            'overweight' => 'required|in:Yes,No',
            'containerDetails' => 'required|array|min:1',
            'containerDetails.*.containerNo' => 'required|string',
            'containerDetails.*.containerSize' => 'required|string',
            'containerDetails.*.po' => 'required|string',
            'containerDetails.*.cpo' => 'required|string',
            'orderDetails' => 'required|array|min:1',
            'orderDetails.deliveryDetails' => 'required|array|min:1',
            'orderDetails.deliveryDetails.*.orderSentDate' => 'required|date',
            'orderDetails.deliveryDetails.*.isVendorConfirmed' => 'required|in:Yes,No',
            'orderDetails.deliveryDetails.*.pickedUpDate' => 'required|date',
            'orderDetails.deliveryDetails.*.scheduleDate' => 'required|date',
            'orderDetails.deliveryDetails.*.emptyReturnOnDate' => 'required|date',
            'orderDetails.deliveryDetails.*.emptyPickUpCutoffDate' => 'required|date',
            'orderDetails.deliveryDetails.*.transitTime' => 'required|date',
            'orderDetails.deliveryDetails.*.mode' => 'required|string',
            'orderDetails.deliveryDetails.*.freeDays' => 'required|integer',
            'orderDetails.deliveryDetails.*.serviceTypeId' => 'required|integer',
            'orderDetails.deliveryDetails.*.portOfLoadingId' => 'required|integer',
            'orderDetails.deliveryDetails.*.portOfDischargeId' => 'required|integer',
            'orderDetails.deliveryDetails.*.destinationId' => 'required|integer',
            'orderDetails.deliveryDetails.*.vendorId' => 'required|integer',
            'orderDetails.deliveryDetails.*.transhipmentPortId' => 'required|integer',
            'orderDetails.deliveryDetails.*.deliveryCreatedBy' => 'required|integer',
            'orderDetails.deliveryDetails.*.deliveryStatusId' => 'required|integer',
            'orderDetails.shipper' => 'required|string',
            'orderDetails.shipperAddress' => 'required|string',
            'orderDetails.consignee' => 'required|string',
            'orderDetails.consigneeAddress' => 'required|string',
            'orderDetails.buyer' => 'required|string',
            'orderDetails.buyerAddress' => 'required|string',
            'orderDetails.notifyParty' => 'required|string',
            'orderDetails.bookingRequestSentDate' => 'required|date',
            'orderDetails.bookingDate' => 'required|date',
            'orderDetails.bcSentToShipper' => 'required|date',
            'orderDetails.seal' => 'required|string',
            'orderDetails.weight' => 'required|string',
            'orderDetails.pallets' => 'required|integer',
            'orderDetails.etd' => 'required|date',
            'orderDetails.eta' => 'required|date',
            'orderDetails.streamshipLine' => 'required|string',
            'orderDetails.dischargeDate' => 'required|date',
            'orderDetails.cargoReadyDate' => 'required|date',
            'orderDetails.masterBl' => 'required|string',
            'orderDetails.houseBl' => 'required|string',
            'orderDetails.freightLocation' => 'required|string',
            'orderDetails.vesselVoyage' => 'required|string',
            'orderDetails.firmCode' => 'required|string',
            'orderDetails.commodity' => 'required|string',
            'orderDetails.specialInstructions' => 'nullable|string',
            'orderDetails.uploadDocuments' => 'nullable|string',
            'orderDetails.notes' => 'nullable|string',
            'orderDetails.siCutOff' => 'required|date',
            'orderDetails.vgmCutOff' => 'required|date',
            'orderDetails.cyCutOff' => 'required|date',
            'orderDetails.isf' => 'required|string',
            'orderDetails.isfDate' => 'required|date',
            'orderDetails.isfNo' => 'required|string',
            'orderDetails.isfConfirmation' => 'required|in:Yes,No',
            'orderDetails.customFiled' => 'required|in:Yes,No',
            'orderDetails.customClearanceDate' => 'required|date',
            'orderDetails.customConfirmation' => 'required|string',
            'orderDetails.lastFreeDay' => 'required|date',
            'orderDetails.dangerousGoods' => 'required|in:Yes,No',
            'orderDetails.freightPrepaid' => 'required|in:Yes,No',
            'orderDetails.allInclusiveRate' => 'required|numeric',
            'orderDetails.htsCode' => 'required|string',
            'orderDetails.consolidator' => 'required|string',
            'orderDetails.importerOfRecordName' => 'required|string',
            'orderDetails.importerOfRecordNumber' => 'required|string',
            'orderDetails.mscChassis' => 'required|in:Yes,No',
            'orderDetails.pierpassFees' => 'required|in:Yes,No',
            'orderDetails.cleanTruckFees' => 'required|in:Yes,No',
            'orderDetails.accessorialCharges' => 'required|in:Yes,No',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }
}

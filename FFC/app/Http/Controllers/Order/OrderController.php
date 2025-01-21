<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Common\ServiceType;
use App\Models\Order\Order;
use App\Models\Order\OrderDelivery;
use App\Models\Order\OrderDetails;
use App\Models\Order\OrderNote;
use App\Models\Order\OrderStatusMaster;
use App\Services\Order\OrderService;
use Dompdf\Dompdf;
use Exception;
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
                'orderId' => $order,
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
            'orderDetails' => function ($query) {
                $query->with([
                    'serviceType:id,name',
                    'orderContainerDetails',
                    'orderHormonizeDetails',
                    // 'shipper:id,company_name,city,state_id,country_id',
                    // 'shipper.country:id,name',
                    // 'shipper.state:id,name',
                    // 'buyer:id,company_name,city,state_id,country_id',
                    // 'buyer.country:id,name',
                    // 'buyer.state:id,name',
                    // 'consignee:id,company_name,city,state_id,country_id',
                    // 'consignee.country:id,name',
                    // 'consignee.state:id,name',
                    // 'manufacturer:id,company_name,city,state_id,country_id',
                    // 'manufacturer.country:id,name',
                    // 'manufacturer.state:id,name',
                    // 'shipToParty:id,company_name,city,state_id,country_id',
                    // 'shipToParty.country:id,name',
                    // 'shipToParty.state:id,name',
                    // 'consolidator:id,company_name,city,state_id,country_id',
                    // 'consolidator.country:id,name',
                    // 'consolidator.state:id,name',
                    'deliveries' => function ($query) {
                        $query->select('order_deliveries.*') // Select all columns of deliveries
                            ->addSelect([
                                // Subquery to get the latest delivery_status_id
                                DB::raw('(SELECT delivery_status_id FROM order_delivery_statuses 
                                    WHERE order_delivery_statuses.order_delivery_id = order_deliveries.id 
                                    ORDER BY created_at DESC LIMIT 1) AS delivery_status_id')
                            ])
                            ->with([
                                'portOfLoading:id,name',
                                'portOfDischarge:id,name',
                                'destination:id,name',
                                'vendor:id,company_name',
                                'transhipmentPort:id,name',
                                'createdBy:id,first_name,last_name',
                                'railRamp:id,name',
                                'receiverName:id,name',
                                'receiverAddress:id,address',
                                'statuses' => function ($query) {
                                    // $query->latest('created_at')->limit(1);
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
            // 'quoteId' => 'nullable|integer',
            // 'receivedDate' => 'required|date',
            // 'addressId' => 'required|integer',
            // 'overweight' => 'required|in:Yes,No',
            // 'order_container_details' => 'required|array|min:1',
            // 'order_container_details.*.containerNo' => 'required|string',
            // 'order_container_details.*.containerSize' => 'required|string',
            // 'order_container_details.*.po' => 'required|string',
            // 'order_container_details.*.cpo' => 'required|string',
            // 'order_details' => 'required|array|min:1',
            // 'order_details.deliveries' => 'required|array|min:1',
            // 'order_details.deliveries.*.orderSentDate' => 'required|date',
            // 'order_details.deliveries.*.isVendorConfirmed' => 'required|in:Yes,No',
            // 'order_details.deliveries.*.pickedUpDate' => 'required|date',
            // 'order_details.deliveries.*.scheduleDate' => 'required|date',
            // 'order_details.deliveries.*.emptyReturnOnDate' => 'required|date',
            // 'order_details.deliveries.*.emptyPickUpCutoffDate' => 'required|date',
            // 'order_details.deliveries.*.transitTime' => 'required|date',
            // 'order_details.deliveries.*.mode' => 'required|string',
            // 'order_details.deliveries.*.freeDays' => 'required|integer',
            // 'order_details.deliveries.*.serviceTypeId' => 'required|integer',
            // 'order_details.deliveries.*.portOfLoadingId' => 'required|integer',
            // 'order_details.deliveries.*.portOfDischargeId' => 'required|integer',
            // 'order_details.deliveries.*.destinationId' => 'required|integer',
            // 'order_details.deliveries.*.vendorId' => 'required|integer',
            // 'order_details.deliveries.*.transhipmentPortId' => 'required|integer',
            // 'order_details.deliveries.*.deliveryCreatedBy' => 'required|integer',
            // 'order_details.deliveries.*.deliveryStatusId' => 'required|integer',
            // 'order_details.shipper' => 'required|string',
            // 'order_details.shipperAddress' => 'required|string',
            // 'order_details.consignee' => 'required|string',
            // 'order_details.consigneeAddress' => 'required|string',
            // 'order_details.buyer' => 'required|string',
            // 'order_details.buyerAddress' => 'required|string',
            // 'order_details.notifyParty' => 'required|string',
            // 'order_details.bookingRequestSentDate' => 'required|date',
            // 'order_details.bookingDate' => 'required|date',
            // 'order_details.bcSentToShipper' => 'required|date',
            // 'order_details.seal' => 'required|string',
            // 'order_details.weight' => 'required|string',
            // 'order_details.pallets' => 'required|integer',
            // 'order_details.etd' => 'required|date',
            // 'order_details.eta' => 'required|date',
            // 'order_details.streamshipLine' => 'required|string',
            // 'order_details.dischargeDate' => 'required|date',
            // 'order_details.cargoReadyDate' => 'required|date',
            // 'order_details.masterBl' => 'required|string',
            // 'order_details.houseBl' => 'required|string',
            // 'order_details.freightLocation' => 'required|string',
            // 'order_details.vesselVoyage' => 'required|string',
            // 'order_details.firmCode' => 'required|string',
            // 'order_details.commodity' => 'required|string',
            // 'order_details.specialInstructions' => 'nullable|string',
            // 'order_details.uploadDocuments' => 'nullable|string',
            // 'order_details.notes' => 'nullable|string',
            // 'order_details.siCutOff' => 'required|date',
            // 'order_details.vgmCutOff' => 'required|date',
            // 'order_details.cyCutOff' => 'required|date',
            // 'order_details.isf' => 'required|string',
            // 'order_details.isfDate' => 'required|date',
            // 'order_details.isfNo' => 'required|string',
            // 'order_details.isfConfirmation' => 'required|in:Yes,No',
            // 'order_details.customFiled' => 'required|in:Yes,No',
            // 'order_details.customClearanceDate' => 'required|date',
            // 'order_details.customConfirmation' => 'required|string',
            // 'order_details.lastFreeDay' => 'required|date',
            // 'order_details.dangerousGoods' => 'required|in:Yes,No',
            // 'order_details.freightPrepaid' => 'required|in:Yes,No',
            // 'order_details.allInclusiveRate' => 'required|numeric',
            // 'order_details.htsCode' => 'required|string',
            // 'order_details.consolidator' => 'required|string',
            // 'order_details.importerOfRecordName' => 'required|string',
            // 'order_details.importerOfRecordNumber' => 'required|string',
            // 'order_details.mscChassis' => 'required|in:Yes,No',
            // 'order_details.pierpassFees' => 'required|in:Yes,No',
            // 'order_details.cleanTruckFees' => 'required|in:Yes,No',
            // 'order_details.accessorialCharges' => 'required|in:Yes,No',
            // 'order_details.ein' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }

    /**
     * Order Note
     */

    public function getOrderNote(Request $request, $id)
    {
        Log::info("*****************************");
        Log::info('Get Order Note');
        Log::info("*****************************");
        // Use the findModel helper to retrieve the Order
        $order = findModel(Order::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($order instanceof \Illuminate\Http\JsonResponse) {
            return $order;  // Return the not found response
        }

        $note = $this->orderService->getOrderNoteData($request, $id);
        // OrderNote::with('user:id,first_name,last_name')->where('order_id', $id)->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $note], 200);
    }

    public function storeNote(Request $request)
    {
        Log::info("*****************************");
        Log::info('Save Order Note');
        Log::info("*****************************");
        $validatedData = $this->orderNoteValidation($request);
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Order Note validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction
        try {
            $this->orderService->createNotes($request);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Order Notes created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert order note data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert order note',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function updateNote(Request $request, $id)
    {
        Log::info("*****************************");
        Log::info('Update Order Note');
        Log::info("*****************************");
        // Use the findModel helper to retrieve the OrderNote
        $orderNotes = findModel(OrderNote::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($orderNotes instanceof \Illuminate\Http\JsonResponse) {
            return $orderNotes;  // Return the not found response
        }

        $validatedData = $this->orderNoteValidation($request);
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Order Note validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction
        try {
            $this->orderService->updateNote($request, $orderNotes);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Order Notes updated successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update order note data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order note',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function editNote($id)
    {
        Log::info("*****************************");
        Log::info('Edit Order Note');
        Log::info("*****************************");
        // Use the findModel helper to retrieve the Order
        $orderNote = findModel(OrderNote::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($orderNote instanceof \Illuminate\Http\JsonResponse) {
            return $orderNote;  // Return the not found response
        }

        $note = OrderNote::with('user:id,first_name,last_name')->find($id);
        return response()->json(['status' => true, 'data' => $note], 200);
    }

    public function destroyNote($id)
    {
        Log::info("*****************************");
        Log::info('Delete Order Note');
        Log::info("*****************************");
        // Use the findModel helper to retrieve the Order
        $orderNote = findModel(OrderNote::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($orderNote instanceof \Illuminate\Http\JsonResponse) {
            return $orderNote;  // Return the not found response
        }

        DB::transaction(function () use ($orderNote) {
            $orderNote->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Order Note deleted successfully'
        ], 200);
    }

    public function statusNote(Request $request, $id)
    {
        Log::info("*****************************");
        Log::info('Status Order Note............');
        Log::info("*****************************");
        // Use the statusUpdate helper to update status
        return statusUpdate(OrderNote::class, $id, [
            'status' => $request->status
        ]);
    }

    private function orderNoteValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'orderId' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return $validator->validated();
    }

    /**
     * Order PDF
     * id should be delivery id
     */
    public function generatePdf($id)
    {
        Log::info("*********************");
        Log::info("Pdf is generating...." . $id);
        Log::info("*********************");
        // Get delevery Details
        $deliveryId = $id;
        $deliveryData = OrderDelivery::find($deliveryId);
        Log::info("deliveryId => " . $deliveryId);
        if (!$deliveryData) {
            Log::info('Order Delivery Details are not found.');
            return response()->json([
                'status' => false,
                'message' => 'Order Delivery Details are not found for this Id =>' . $id
            ], 400); // Return error response
        }
        // Get Order Details
        $orderDetailsId = $deliveryData->order_details_id;
        Log::info("orderDetailsId => " . $orderDetailsId);
        $orderDetailsData = OrderDetails::find($orderDetailsId);
        if (!$orderDetailsData) {
            Log::info('Order Details are not found.');
            return response()->json([
                'status' => false,
                'message' => 'Order Details are not found'
            ], 400);
        }
        // Get Order Details
        $orderId = $orderDetailsData->order_id;
        Log::info("orderId => " . $orderId);
        $orderData = Order::find($orderId);
        if (!$orderData) {
            Log::info('Order is not found.');
            return response()->json([
                'status' => false,
                'message' => 'Order is not found'
            ], 400);
        }
        // Get Service Type details
        $serviceTypeId = $orderDetailsData->service_type_id;
        Log::info("serviceTypeId => " . $serviceTypeId);
        $serviceData = ServiceType::find($serviceTypeId);
        if (!$serviceData) {
            Log::info('Service Type is not found.');
            return response()->json([
                'status' => false,
                'message' => 'Service Type is not found'
            ], 400);
        }
        // Fetch data from database
        try {
            $data = $this->orderService->getPdfData($deliveryId, $orderDetailsId, $orderId);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Order Data Not Found',
                "error" => $e->getMessage()
            ], 400); // Return error response
        }

        // return response()->json([
        //     $data[0],
        // ]);
        // exit;

        Log::info($serviceData);
        Log::info("PDF DATA => " . $data[0]);
        // $html = view('order/Trucking', compact('data'))->render();
        if (isset($serviceData['id']) && ($serviceData['id'] == 1 || $serviceData['id'] == 5)) {
            Log::info("Getting view for Trucking Template");
            $html = view('order/Trucking', ['data' => $data[0]])->render();
        } else if (isset($serviceData['id']) && ($serviceData['id'] == 2)) {
            Log::info("Getting view for Transload Template");
            $html = view('order/Transload', ['data' => $data[0]])->render();
        } else if (isset($serviceData['id']) && ($serviceData['id'] == 3 || $serviceData['id'] == 4)) {
            Log::info("Getting view for Ocean Template");
            $html = view('order/Ocean', ['data' => $data[0]])->render();
        } else if (isset($serviceData['id']) && ($serviceData['id'] == 6)) {
            Log::info("Getting view for ISF Template");
            $html = view('order/ISF', ['data' => $data[0]])->render();
        } else {
            Log::info("Service ID does not match any template.");
        }
        if (!empty($html)) {
            // Load the HTML and pass the data
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            // Get the raw PDF content
            $pdfContent = $dompdf->output();

            // Encode as Base64
            $base64Pdf = base64_encode($pdfContent);

            return response()->json([
                'pdf_base64' => $base64Pdf,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Html View is empty. So not able to generate PDF'
            ], 400); // Return error response
        }


        // return response()->streamDownload(
        //     fn() => print($dompdf->output()),
        //     'delivery_order.pdf',
        //     [
        //         'Content-Type' => 'application/pdf',
        //         'Content-Disposition' => 'attachment; filename="delivery_order.pdf"',
        //     ]
        // );

        // return $pdf->download('Trucking.pdf');
    }

    /**
     * Delivery Status
     * 1. Get Request
     * 2. Update Request
     */
    public function deliveryStatus($serviceTypeId, $deliveryId)
    {
        // Use the findModel helper to retrieve the port
        $serviceType = findModel(ServiceType::class, $serviceTypeId);
        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($serviceType instanceof \Illuminate\Http\JsonResponse) {
            return $serviceType;  // Return the not found response
        }
        // Use the findModel helper to retrieve the port
        $orderDelivery = findModel(OrderDelivery::class, $deliveryId);
        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($orderDelivery instanceof \Illuminate\Http\JsonResponse) {
            return $orderDelivery;  // Return the not found response
        }
        $order = $this->orderService->getDeliveryStatusData($serviceTypeId, $deliveryId);
        return response()->json(['status' => true, 'data' => $order], 200);
    }

    public function deliveryUpdateStatus(Request $request, $deliveryId)
    {
        $orderDelivery = findModel(OrderDelivery::class, $deliveryId);
        if ($orderDelivery instanceof \Illuminate\Http\JsonResponse) {
            return $orderDelivery;
        }
        $orderStatusMaster = OrderStatusMaster::find($request['statusId']);
        if (empty($orderStatusMaster)) {
            Log::info('Status Id not found.');
            return response()->json(['status' => false, 'message' => 'Status Id not found.'], 200);
        }
        $order = $this->orderService->updateDeliveryStatus($request, $deliveryId);
        return response()->json(['status' => true, 'data' => $order], 200);
    }
}

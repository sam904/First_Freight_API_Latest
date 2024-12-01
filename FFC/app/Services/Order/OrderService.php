<?php

namespace App\Services\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderDetails;
use App\Models\Order\OrderNote;
use App\Models\Order\OrderStatusMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{

    protected $loginUser;
    protected $noteService;

    public function __construct()
    {
        $this->loginUser =  Auth::user();
    }

    public function getAllOrders(Request $request)
    {
        $orders = Order::with([
            'customer:id,company_name',
            'address:id,company_name',
            'quote:id',
            'orderContainerDetails',
            'orderDetails' => function ($query) {
                $query->with([
                    'deliveries' => function ($query) {
                        $query->with([
                            // 'orderDetail:id,order_id', // OrderDetail relationship within deliveries
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
        ])->paginate(10);

        return $orders;
    }

    /**
     * Order Status Master
     */
    public function getAllOrderStatus(Request $request)
    {
        return OrderStatusMaster::paginate(10);
    }

    public function saveOrder(Request $request)
    {
        Log::info("Create Order");
        $order = $this->createOrder($request);
        Log::info("Order is created => " . $order->id);

        // Log::info("Container Details");
        // $this->containerDetails($request, $order);

        Log::info("Order Details");
        $this->orderDetails($request, $order);

        Log::info("Order Notes");
        if (!empty($request->input('orderNotes'))) {
            $this->saveOrderNotes($request, $order->id);
        }

        return $order->id;
    }

    public function updateOrder(Request $request, Order $order)
    {
        Log::info("update order id => " . $order->id);
        $this->createOrder($request, $order, $order->id);

        $this->orderDetails($request, $order);
        return true;
    }

    public function createOrder(Request $request, Order $order = null, $id = null)
    {
        $orderData = [
            'customer_id' => $request['customerId'],
            'quote_id' => $request['quoteId'] ?? null,
            'received_date' => $request['receivedDate'] ?? null,
            'address_id' => $request['addressId'] ?? null,
            "created_by" =>  $this->loginUser->id,
        ];
        if ($id == null) {
            Log::info("Saving Order...");
            $order = Order::create($orderData);
        } else {
            Log::info("updating Order data for => " . $order->id);
            $order = $order->update($orderData);
        }
        return $order;
    }

    public function containerDetails($data, OrderDetails $orderDetail, $id = null)
    {
        // Log::info($data['order_container_details']);
        // if (!empty($request['order_container_details'])) {
        if (isset($data['order_container_details']) && is_array($data['order_container_details'])) {
            foreach ($data['order_container_details'] as $container) {
                Log::info("container => ", $container);
                $containerData = [
                    'container_no' => $container['containerNo'] ?? null,
                    'container_size' => $container['containerSize'] ?? null,
                    'po' => $container['po'] ?? null,
                    'cpo' => $container['cpo'] ?? null,
                    'overweight' => $container['overweight'] ?? null,
                ];
                if ($id == null) {
                    Log::info("Save to Order Container Details table..." . $orderDetail);
                    $orderDetail->orderContainerDetails()->create($containerData);
                } else {
                    Log::info("update the Order Container Details table for =>" . $orderDetail->id);

                    $orderContainer = $orderDetail->orderContainerDetails()->where('id', $container['orderContainerId'])->first();
                    $orderContainer->update($containerData);
                }
            }
        }
        // } else {
        //     Log::info("Order Container details are empty...");
        // }
    }

    public function orderDetails(Request $request, Order $order)
    {
        if (!empty($request['order_details'])) {
            foreach ($request->order_details as $detail) {


                // Perform order details actions if isOrderDetailsRequest is insert or update
                if ($detail['isOrderDetailsRequest'] != null) {

                    $orderDetailData = [
                        'service_type_id' => $detail['serviceTypeId'] ?? null,
                        'shipper' => $detail['shipper'] ?? null,
                        'shipper_address' => $detail['shipperAddress'] ?? null,
                        'consignee' => $detail['consignee'] ?? null,
                        'consignee_address' => $detail['consigneeAddress'] ?? null,
                        'buyer' => $detail['buyer'] ?? null,
                        'buyer_address' => $detail['buyerAddress'] ?? null,
                        'notify_party' => $detail['notifyParty'] ?? null,
                        'booking_request_sent_date' => $detail['bookingRequestSentDate'] ?? null,
                        'booking_date' => $detail['bookingDate'] ?? null,
                        'bc_sent_to_shipper' => $detail['bcSentToShipper'] ?? null,
                        'seal' => $detail['seal'] ?? null,
                        'weight' => $detail['weight'] ?? null,
                        'pallets' => $detail['pallets'] ?? null,
                        'etd' => $detail['etd'] ?? null,
                        'eta' => $detail['eta'] ?? null,
                        'streamship_line' => $detail['streamshipLine'] ?? null,
                        'discharge_date' => $detail['dischargeDate'] ?? null,
                        'cargo_ready_date' => $detail['cargoReadyDate'] ?? null,
                        'master_bl' => $detail['masterBl'] ?? null,
                        'house_bl' => $detail['houseBl'] ?? null,
                        'freight_location' => $detail['freightLocation'] ?? null,
                        'vessel_voyage' => $detail['vesselVoyage'] ?? null,
                        'firm_code' => $detail['firmCode'] ?? null,
                        'commodity' => $detail['commodity'] ?? null,
                        'special_instructions' => $detail['specialInstructions'] ?? null,
                        'upload_documents' => $detail['uploadDocuments'] ?? null,
                        'notes' => $detail['notes'] ?? null,
                        'si_cut_off' => $detail['siCutOff'] ?? null,
                        'vgm_cut_off' => $detail['vgmCutOff'] ?? null,
                        'cy_cut_off' => $detail['cyCutOff'] ?? null,
                        'isf' => $detail['isf'] ?? null,
                        'isf_date' => $detail['isfDate'] ?? null,
                        'isf_no' => $detail['isfNo'] ?? null,
                        'isf_confirmation' => $detail['isfConfirmation'] ?? null,
                        'custom_filed' => $detail['customFiled'] ?? null,
                        'custom_clearance_date' => $detail['customClearanceDate'] ?? null,
                        'custom_confirmation' => $detail['customConfirmation'] ?? null,
                        'last_free_day' => $detail['lastFreeDay'] ?? null,
                        'dangerous_goods' => $detail['dangerousGoods'] ?? null,
                        'freight_prepaid' => $detail['freightPrepaid'] ?? null,
                        'all_inclusive_rate' => $detail['allInclusiveRate'] ?? null,
                        'hts_code' => $detail['htsCode'] ?? null,
                        'consolidator' => $detail['consolidator'] ?? null,
                        'importer_of_record_name' => $detail['importerOfRecordName'] ?? null,
                        'importer_of_record_number' => $detail['importerOfRecordNumber'] ?? null,
                        'msc_chassis' => $detail['mscChassis'] ?? null,
                        'pierpass_fees' => $detail['pierpassFees'] ?? null,
                        'clean_truck_fees' => $detail['cleanTruckFees'] ?? null,
                        'accessorial_charges' => $detail['accessorialCharges'] ?? null,
                    ];

                    if ($detail['isOrderDetailsRequest'] === 'insert') {
                        $orderDetail = $order->orderDetails()->create($orderDetailData);
                        Log::info("Saving Order Details..." . $orderDetail->id);

                        // Log::info($detail['order_container_details']);
                        $this->containerDetails($detail, $orderDetail, null);
                    } elseif ($detail['isOrderDetailsRequest'] === 'update') {
                        $orderDetailId = $detail['orderDetailsId'];
                        if (empty($orderDetailId)) {
                            Log::info('Order Detail ID is required.');
                            throw new \InvalidArgumentException('Order Detail ID is required.');
                        }
                        Log::info("updating Order Details..." . $order->id);
                        $orderDetail = $order->orderDetails()->where('id', $orderDetailId)->first();
                        $orderDetail->update($orderDetailData);
                        Log::info("orderDetail => " . $orderDetail);

                        $this->containerDetails($detail, $orderDetail, $orderDetail->id);
                    } else {
                        Log::error("Order Details not found for update");
                    }

                    // Documement Upload
                    if ($request->hasFile('uploadDocuments')) {
                        Log::info("Order Documents are uploading...");
                        $this->uploadImages($request, $order, $orderDetail);
                    }

                    // Handle deliveryDetails
                    if (!empty($detail['deliveries'])) {
                        foreach ($detail['deliveries'] as $delivery) {
                            // Perform order delivery if isRequestType is 'insert' or 'update'
                            if ($delivery['isRequestType'] != null) {
                                $deliveryDetailsData = [
                                    'order_sent_date' => $delivery['orderSentDate'] ?? null,
                                    'is_vendor_confirmed' => $delivery['isVendorConfirmed'] ?? null,
                                    'picked_up_date' => $delivery['pickedUpDate'] ?? null,
                                    'schedule_date' => $delivery['scheduleDate'] ?? null,
                                    'empty_return_on_date' => $delivery['emptyReturnOnDate'] ?? null,
                                    'empty_pick_up_cutoff_date' => $delivery['emptyPickUpCutoffDate'] ?? null,
                                    'transit_time' => $delivery['transitTime'] ?? null,
                                    'mode' => $delivery['mode'] ?? null,
                                    'free_days' => $delivery['freeDays'] ?? null,
                                    'port_of_loading_id' => $delivery['portOfLoadingId'],
                                    'port_of_discharge_id' => $delivery['portOfDischargeId'],
                                    'destination_id' => $delivery['destinationId'],
                                    'vendor_id' => $delivery['vendorId'],
                                    'transhipment_port_id' => $delivery['transhipmentPortId'],
                                    'delivery_created_by' => $delivery['deliveryCreatedBy'],
                                ];
                                if ($delivery['isRequestType'] === 'insert') {
                                    Log::info("Order Delivery is being created...");
                                    $orderDelivery = $orderDetail->deliveries()->create($deliveryDetailsData);
                                } elseif ($delivery['isRequestType'] === 'update') {
                                    if (empty($delivery['deliveryId'])) {
                                        Log::info('Order Delivery ID is required.');
                                        throw new \InvalidArgumentException('Order Delivery ID is required.');
                                    }
                                    Log::info("Order Delivery is being updated for delivery   order details id :" . $orderDetail->id);
                                    $orderDelivery = $orderDetail->deliveries()
                                        ->where('order_details_id', $orderDetail->id)
                                        ->where('id', $delivery['deliveryId'])
                                        ->first();
                                    if ($orderDelivery) {
                                        $orderDelivery->update($deliveryDetailsData);
                                    } else {
                                        Log::error("Delivery not found for update.");
                                    }
                                }

                                if ($delivery['isStatusRequestChanged'] == true) {
                                    $deliveryStatusData = ['delivery_status_id' => $delivery['deliveryStatusId']];
                                    Log::info("Order Delivery Status are creating...");
                                    $orderDelivery->statuses()->create($deliveryStatusData);
                                }
                            } else {
                                Log::info("Delivery Details isRequestType is null....");
                            }
                        }
                    } else {
                        Log::info("Delivery Details array found as empty");
                    }
                } else {
                    Log::info("Order Details isOrderDetailsRequest is null");
                }
            }
        }
    }

    public function uploadImages(Request $request, $order, $orderDetail)
    {
        Log::info("Uploading Images for order => " . $order->id . " && order details id =>" . $orderDetail->id);

        // Get existing images (if any)
        $existingImages = $order->upload_document ? explode(',', $order->upload_document) : [];

        $uploadedImages = [];
        $destinationPath = 'images/profiles/order/' . $order->id . '/' . $orderDetail->id . '/';
        $images = $request->file('uploadDocuments');

        if (is_array($images)) {
            foreach ($images as $image) {
                Log::info("In case of Multiple image input");
                $imgName = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imgName);
                $uploadedImages[] = $imgName; // Collect only the file names
            }
        } elseif ($images) {
            Log::info("In case of single image input");
            $imgName = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $images->getClientOriginalExtension();
            $images->move($destinationPath, $imgName);
            $uploadedImages[] = $imgName;
        }

        Log::info(implode(',', $uploadedImages));

        // Merge existing images with newly uploaded images
        $allImages = array_merge($existingImages, $uploadedImages);

        // Update the database with the new list of images
        $order->update([
            'upload_documents' => implode(',', $allImages) // Convert array to comma-separated string
        ]);
    }


    /**
     * Summary of Order Status Master
     * 1. createStatusMaster
     * 2. updateStatusMaster
     */
    public function createStatusMaster(Request $request)
    {
        // $orderStatusMaster = OrderStatusMaster::create([
        //     'name' => $request['name'],
        //     'service_type_id' => $request['serviceTypeId'],
        //     'sort_level' => $request['sortLevel'],
        // ]);
        $orderStatusMaster = OrderStatusMaster::updateOrCreate(
            // Search criteria to determine if the record exists
            [
                'service_type_id' => $request['serviceTypeId'],
                'name' => $request['name']
            ],
            // Data to update or create
            [
                'sort_level' => $request['sortLevel']
            ]
        );
        return $orderStatusMaster;
    }

    public function updateStatusMaster(Request $request, OrderStatusMaster $orderStatusMaster)
    {
        $orderStatusMaster->update([
            'name' => $request['name'],
            'service_type_id' => $request['serviceTypeId'],
            'sort_level' => $request['sortLevel'],
        ]);
        return true;
    }

    /**
     * Order Notes
     */

    public function saveOrderNotes(Request $request, $id)
    {
        if (!empty($request->input('orderNotes'))) {
            foreach ($request->input('orderNotes') as $note) {
                OrderNote::create([
                    'title' => $note['title'],
                    'description' => $note['description'],
                    'tag' => $note['tag'],
                    'pin' => $note['pin'],
                    'order_id' => $id, // Use the saved rate ID
                    "user_id" =>  $this->loginUser->id,
                ]);
                Log::info("Order Note is created for order id => " . $id);
            }
        }
    }

    public function createNotes(Request $request)
    {
        OrderNote::create([
            'title' => $request['title'],
            'description' => $request['description'],
            'tag' => $request['tag'],
            'pin' => $request['pin'],
            'order_id' => $request['orderId'], // Use the saved rate ID
            "user_id" =>  $this->loginUser->id,
        ]);
        return true;
    }


    public function updateNote(Request $request, OrderNote $notes)
    {
        $notes->update([
            'title' => $request['title'],
            'description' => $request['description'],
            'tag' => $request['tag'],
            'pin' => $request['pin'],
            'order_id' => $request['orderId'],
            'user_id' =>  $this->loginUser->id,
        ]);
        return true;
    }
}

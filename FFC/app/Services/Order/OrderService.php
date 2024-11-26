<?php

namespace App\Services\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderStatus;
use App\Models\Order\OrderStatusMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderService
{

    protected $loginUser;

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

        $order = $this->createOrder($request);
        Log::info("Order is created => " . $order->id);

        $this->containerDetails($request, $order);

        $this->orderDetails($request, $order);

        return true;
    }

    public function updateOrder(Request $request, Order $order)
    {
        $order = $this->createOrder($request, $order, $order->id);
    }

    public function createOrder(Request $request, Order $order = null, $id = null)
    {
        $orderData = [
            'customer_id' => $request['customerId'],
            'quote_id' => $request['quoteId'] ?? null,
            'received_date' => $request['receivedDate'] ?? null,
            'address_id' => $request['addressId'],
            'overweight' => $request['overweight'] ?? null,
            "created_by" =>  $this->loginUser->id,
        ];
        if ($id == null) {
            $order = Order::create($orderData);
        } else {
            $order = $order->update($orderData);
        }
        return $order;
    }

    public function containerDetails(Request $request, Order $order, $id = null)
    {
        $containerData = [
            'container_no' => $container['containerNo'] ?? null,
            'container_size' => $container['containerSize'] ?? null,
            'po' => $container['po'] ?? null,
            'cpo' => $container['cpo'] ?? null,
        ];
        if (!empty($request['containerDetails'])) {
            Log::info("Save to order_container_details table...");
            foreach ($request['containerDetails'] as $container) {
                $order->orderContainerDetails()->create($containerData);
            }
        } else {
            Log::info("Order Container details are empty...");
        }
    }

    public function orderDetails(Request $request, Order $order, $id = null)
    {
        Log::info("Save to order_details and related tables...");
        if (!empty($request['orderDetails'])) {
            $detail = $request['orderDetails'];
            $orderDetailData = [
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

            $orderDetail = $order->orderDetails()->create($orderDetailData);

            Log::info("Order Documents are uploading...");
            if ($request->hasFile('uploadDocuments')) {
                $this->uploadImages($request, $order);
            }

            Log::info("Order Delivery are creating...");
            foreach ($detail['deliveryDetails'] as $delivery) {
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
                    'service_type_id' => $delivery['serviceTypeId'],
                    'port_of_loading_id' => $delivery['portOfLoadingId'],
                    'port_of_discharge_id' => $delivery['portOfDischargeId'],
                    'destination_id' => $delivery['destinationId'],
                    'vendor_id' => $delivery['vendorId'],
                    'transhipment_port_id' => $delivery['transhipmentPortId'],
                    'delivery_created_by' => $delivery['deliveryCreatedBy'],
                ];
                $orderDelivery = $orderDetail->deliveries()->create($deliveryDetailsData);

                Log::info("Order Delivery Status are creating...");
                $deliveryStatusData = ['delivery_status_id' => $delivery['deliveryStatusId']];
                $orderDelivery->statuses()->create($deliveryStatusData);
            }
        }
    }
    public function uploadImages(Request $request, $order)
    {
        Log::info("Uploading Images for order => " . $order->id);

        // Get existing images (if any)
        $existingImages = $order->upload_document ? explode(',', $order->upload_document) : [];

        $uploadedImages = [];
        $destinationPath = 'images/profiles/order/' . $order->id . '/';
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
        $orderStatusMaster = OrderStatusMaster::create([
            'name' => $request['name'],
            'service_type_id' => $request['serviceTypeId'],
            'sort_level' => $request['sortLevel'],
        ]);
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
}

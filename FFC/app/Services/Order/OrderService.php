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
            'quote:id',
            'orderDetails.serviceType:id,name',
            'orderDetails.vendor:id,company_name',
            'orderDetails.port:id,name',
            'orderDetails.destination:id,name',
            'orderDetails.transhipmentPort:id,name',
            'orderDetails.user:id,first_name,last_name'
        ]);
        return $orders->paginate(10);
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
        $order = Order::create([
            'customer_id' => $request['customerId'],
            'quote_id' => $request['quoteId'],
            'received_date' => $request['receivedDate'],
            'address_id' => $request['addressId'],
            'container_no' => $request['containerNo'],
            'container_size' => $request['containerSize'],
            'po' => $request['po'],
            'cpo' => $request['cpo'] ?? null,
            'overweight' => $request['overweight'],
            "created_by" =>  $this->loginUser->id,
        ]);

        Log::info("Order Documents are uploading...");
        if ($request->hasFile('uploadDocuments')) {
            $this->uploadImages($request, $order);
        }

        Log::info("Order Details are saving...");
        $this->saveOrderDetails($request, $order);



        return true;
    }

    public function saveOrderDetails(Request $request, $order)
    {
        foreach ($request['orderDetails'] as $detail) {
            $order->orderDetails()->create([
                "created_by" =>  $detail['createdBy'],
                'service_type_id' => $detail['serviceTypeId'],
                'vendor_id' => $detail['vendorId'],
                'port_id' => $detail['portId'],
                'destination_id' => $detail['destinationId'],
                'transhipment_port_id' => $detail['transhipmentPortId'] ?? null,
                'shipper' => $detail['shipper'],
                'shipper_address' => $detail['shipperAddress'],
                'consignee' => $detail['consignee'],
                'consignee_address' => $detail['consigneeAddress'],
                'buyer' => $detail['buyer'] ?? null,
                'buyer_address' => $detail['buyerAddress'] ?? null,
                'notify_party' => $detail['notifyParty'] ?? null,
                'booking_request_sent_date' => $detail['bookingRequestSentDate'],
                'booking_date' => $detail['bookingDate'] ?? null,
                'bc_sent_to_shipper' => $detail['bcSentToShipper'] ?? null,
                'bl' => $detail['bl'] ?? null,
                'seal' => $detail['seal'] ?? null,
                'weight' => $detail['weight'] ?? null,
                'pallets' => $detail['pallets'] ?? null,
                'etd' => $detail['etd'],
                'eta' => $detail['eta'],
                'streamship_line' => $detail['streamshipLine'] ?? null,
                'discharge_date' => $detail['dischargeDate'] ?? null,
                'cargo_ready_date' => $detail['cargoReadyDate'] ?? null,
                'vessel_voyage' => $detail['vesselVoyage'] ?? null,
                'commodity' => $detail['commodity'] ?? null,
                'si_cut_off' => $detail['siCutOff'] ?? null,
                'vgm_cut_off' => $detail['vgmCutOff'] ?? null,
                'cy_cut_off' => $detail['cyCutOff'] ?? null,
                'isf' => $detail['isf'] ?? null,
                'isf_date' => $detail['isfDate'] ?? null,
                'isf_no' => $detail['isfNo'] ?? null,
                'custom_filed' => $detail['customFiled'] ?? null,
                'last_free_day' => $detail['lastFreeDay'] ?? null,
                'freight_location' => $detail['freightLocation'] ?? null,
                'dangerous_goods' => $detail['dangerousGoods'] ?? null,
                'freight_prepaid' => $detail['freightPrepaid'] ?? null,
                'all_inclusive_rate' => $detail['allInclusiveRate'] ?? null,
                'hts_code' => $detail['htsCode'] ?? null,
                'firm_code' => $detail['firmCode'] ?? null,
                'special_instructions' => $detail['specialInstructions'] ?? null,
                'notes' => $detail['notes'] ?? null,
                'delivery_order_sent_date' => $detail['deliveryOrderSentDate'] ?? null,
                'delivery_vendor_confirm' => $detail['deliveryVendorConfirm'] ?? null,
                'delivery_picked_up_date' => $detail['deliveryPickedUpDate'] ?? null,
                'delivery_schedule_date' => $detail['deliveryScheduleDate'] ?? null,
                'delivery_empty_return_date' => $detail['deliveryEmptyReturnDate'] ?? null,
                'delivery_empty_pick_up_cutoff_date' => $detail['deliveryEmptyPickUpCutoffDate'] ?? null,
                'transmit_time' => $detail['transmitTime'] ?? null,
                'delivery_mode' => $detail['deliveryMode'] ?? null,
                'delivery_free_days' => $detail['deliveryFreeDays'] ?? null,
                'msc_chassis' => $detail['mscChassis'] ?? null,
                'pierpass_fees' => $detail['pierpassFees'] ?? null,
                'clean_truck_fees' => $detail['cleanTruckFees'] ?? null,
                'accessorial_charges' => $detail['accessorialCharges'] ?? null,
            ]);

            Log::info("Order status is saving...");
            $order->orderStatuses()->create([
                'status_id' => $detail['orderStatusId']
            ]);
            // OrderStatus::create([
            //     'order_id' => $order->id,
            //     'status_id' => $detail['orderStatusId']
            // ]);
        }
    }

    public function saveOrderOld(Request $request)
    {
        $order =  Order::create([
            'received_date' => $request['receivedDate'],
            'address' => $request['address'],
            'container_no' => $request['containerNo'],
            'bl' =>  $request['bl'],
            'po' =>  $request['po'],
            'cpo' =>  $request['cpo'],
            'seal' =>  $request['seal'],
            'last_free_day' =>  $request['lastFreeDay'],
            'container_size' =>  $request['containerSize'],
            'weight' =>  $request['weight'],
            'overweight' =>  $request['overWeight'],
            'pallets' =>  $request['pallets'],
            'streamship_line' =>  $request['streamShipLine'],
            'discharge_date' =>  $request['dischargeDate'],
            'freight_location' =>  $request['freightLocation'],
            'firm_code' =>  $request['firmCode'],
            'vessel_voyage' =>  $request['vesselVoyage'],
            'eta' =>  $request['eta'],
            'commodity' =>  $request['commodity'],
            'special_instructions' =>  $request['specialInstructions'],
            'notes' =>  $request['notes'],
            'mode' =>  $request['mode'],
            'free_days' =>  $request['freeDays'],
            'delivery_order_sent_date' =>  $request['deliveryOrderSentDate'],
            'delivery_vendor_confirm' =>  $request['deliveryVendorConfirm'],
            'delivery_picked_up_date' =>  $request['deliveryPickedUpDate'],
            'delivery_schedule_date' =>  $request['deliveryScheduleDate'],
            'delivery_empty_return_date' =>  $request['deliveryEmptyReturnDate'],
            'msc_chassis' =>  $request['mscChassis'],
            'pierpass_fees' =>  $request['pierPassFees'],
            'clean_truck_fees' =>  $request['cleanTruckFees'],
            'accessorial_charges' =>  $request['accessorialCharges'],
            'service_type_id' =>  $request['serviceTypeId'],
            'customer_id' =>  $request['customerId'],
            'vendor_id' =>  $request['vendorId'],
            'port_id' =>  $request['portId'],
            'destination_id' =>  $request['destinationId'],
            "user_id" =>  $this->loginUser->id,
        ]);

        if ($request->hasFile('uploadDocuments')) {
            $this->uploadImages($request, $order);
        }

        Log::info("Order status is saving...");
        OrderStatus::create([
            'order_id' => $order->id,
            'status_id' => $request['orderStatusId']
        ]);

        return true;
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
        ]);
        return $orderStatusMaster;
    }

    public function updateStatusMaster(Request $request, OrderStatusMaster $orderStatusMaster)
    {
        $orderStatusMaster->update([
            'name' => $request['name'],
        ]);
        return true;
    }
}

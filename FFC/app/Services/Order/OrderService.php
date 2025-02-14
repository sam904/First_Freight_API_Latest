<?php

namespace App\Services\Order;

use App\Helpers\SearchHelper;
use App\Models\Order\Order;
use App\Models\Order\OrderDeliveryStatus;
use App\Models\Order\OrderDetails;
use App\Models\Order\OrderNote;
use App\Models\Order\OrderStatusMaster;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit');
        $sortColumn = $request->input('sortColumn') ?: 'id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        $isExport = $request->input('export') ?? false;
        $ids = $request->input('ids');

        // Get all column names of the 'Vendors' table
        $model = new Order();

        $query = Order::with([
            'customer:id,company_name',
            'address:id,company_name',
            'quote:id',
            'orderDetails' => function ($query) {
                $query->with([
                    'serviceType:id,name',
                    'orderContainerDetails',
                    'deliveries' => function ($query) {
                        $query->with([
                            // 'orderDetail:id,order_id', // OrderDetail relationship within deliveries
                            'portOfLoading:id,name',
                            'portOfDischarge:id,name',
                            'destination:id,name',
                            'vendor:id,company_name',
                            'transhipmentPort:id,name',
                            'createdBy:id,first_name,last_name,profile_image',
                            'railRamp:id,name',
                            'receiverName:id,name',
                            'receiverAddress:id,address',
                            'statuses' => function ($query) {
                                $query->with([
                                    'deliveryStatus:id,name'
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ]);
        // Apply filter by IDs if they are provided
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }
        // Apply search filters
        $query = SearchHelper::applySearchFilters($query, $model, $request);
        // Sub Table query
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                // Search in customer relation
                $query->orWhereHas('customer', function ($q) use ($searchTerm) {
                    $q->where('company_name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in address relation
                $query->orWhereHas('address', function ($q) use ($searchTerm) {
                    $q->where('company_name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in the serviceType relation
                $query->whereHas('orderDetails.serviceType', function ($q) use ($searchTerm) {
                    Log::info("Search Term => " . $searchTerm);
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in orderContainerDetails within order Details
                $query->orWhereHas('orderDetails.orderContainerDetails', function ($q) use ($searchTerm) {
                    $q->where('container_no', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('container_size', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('po', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('cpo', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('overweight', 'LIKE', "%{$searchTerm}%");
                });

                // Search in receiverName within deliveries
                $query->orWhereHas('orderDetails.deliveries.receiverName', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in receiverAddress within deliveries
                $query->orWhereHas('orderDetails.deliveries.receiverAddress', function ($q) use ($searchTerm) {
                    $q->where('address', 'LIKE', "%{$searchTerm}%");
                });

                // Search in railRamp within deliveries
                $query->orWhereHas('orderDetails.deliveries.railRamp', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in portOfLoading within deliveries
                $query->orWhereHas('orderDetails.deliveries.portOfLoading', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in portOfDischarge within deliveries
                $query->orWhereHas('orderDetails.deliveries.portOfDischarge', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in destination name within deliveries
                $query->orWhereHas('orderDetails.deliveries.destination', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in vendor within deliveries
                $query->orWhereHas('orderDetails.deliveries.vendor', function ($q) use ($searchTerm) {
                    $q->where('company_name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in transhipment port name within deliveries
                $query->orWhereHas('orderDetails.deliveries.transhipmentPort', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in created by name within deliveries
                $query->orWhereHas('orderDetails.deliveries.createdBy', function ($q) use ($searchTerm) {
                    // Check if the search term contains a space (assuming full name has a space)
                    $names = explode(' ', $searchTerm, 2);
                    if (count($names) === 2) {
                        // Full name provided: search for first and last name separately
                        $q->where(function ($query) use ($names) {
                            $query->where('first_name', 'LIKE', "%{$names[0]}%")
                                ->where('last_name', 'LIKE', "%{$names[1]}%");
                        });
                    } else {
                        // Single name provided: search for it in either first or last name
                        $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('last_name', 'LIKE', "%{$searchTerm}%");
                    }
                });

                // Search in statuses and nested delivery_status within deliveries
                $query->orWhereHas('orderDetails.deliveries.statuses.deliveryStatus', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search in Delivery
                $query->orWhereHas('orderDetails.deliveries', function ($q) use ($searchTerm) {
                    $q->where('mode', 'LIKE', "%{$searchTerm}%")
                        // ->orWhere('picked_up_date', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('mode', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('free_days', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        if ($isExport && empty($limit)) {
            // Fetch all data without pagination
            Log::info("export is true and limit is empty");
            $startTime = microtime(true);
            $limit = $query->count();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            Log::info("Order Query Count = {$limit} && execution time: {$executionTime} seconds");
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        } else {
            $limit = $limit ?: 10;
            $orders = $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        }
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
        if (isset($data['order_container_details']) && is_array($data['order_container_details'])) {
            foreach ($data['order_container_details'] as $container) {
                $containerData = [
                    'container_no' => $container['containerNo'] ?? null,
                    'container_size' => $container['containerSize'] ?? null,
                    'po' => $container['po'] ?? null,
                    'cpo' => $container['cpo'] ?? null,
                    'overweight' => $container['overweight'] ?? null,
                    'container_type' => $container['container_type'] ?? null,
                ];
                if (isset($container['orderContainerId']) && $container['orderContainerId'] != null) {
                    // Update existing record
                    Log::info("Updating Order Container Details for ID => " . $container['orderContainerId']);
                    $orderContainer = $orderDetail->orderContainerDetails()->where('id', $container['orderContainerId'])->first();

                    if (empty($orderContainer)) {
                        Log::info('Order Container ID not found: ' . $container['orderContainerId']);
                        throw new \InvalidArgumentException('Order Container ID not found.');
                    }
                    $orderContainer->update($containerData);
                } else {
                    // Create new record
                    Log::info("Creating new Order Container Details record for OrderDetail ID => " . $orderDetail->id);
                    $orderDetail->orderContainerDetails()->create($containerData);
                }
            }
        } else {
            Log::info("Order Container details are empty...");
        }
    }

    public function orderHormonizeDetails($data, OrderDetails $orderDetail, $id = null)
    {
        if (isset($data['order_hormonize_details']) && is_array($data['order_hormonize_details'])) {
            foreach ($data['order_hormonize_details'] as $hormonize) {
                $hormonizeData = [
                    'tarriff_schedule_number' => $hormonize['tarriff_schedule_number'] ?? null
                ];
                if (isset($hormonize['orderHormonizeId']) && $hormonize['orderHormonizeId'] != null) {
                    Log::info("update the Order Hormonize Details table for =>" . $hormonize['orderHormonizeId']);
                    $orderHormonize = $orderDetail->orderHormonizeDetails()->where('id', $hormonize['orderHormonizeId'])->first();
                    if (empty($orderHormonize)) {
                        Log::info('Order Hormonize ID is required/Not Found');
                        throw new \InvalidArgumentException('Order Hormonize ID is required/Not Found.');
                    }
                    $orderHormonize->update($hormonizeData);
                } else {
                    Log::info("Inserting Hormonize details");
                    $orderDetail->orderHormonizeDetails()->create($hormonizeData);
                }
            }
        } else {
            Log::info("Order Hormonize details are empty...");
        }
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
                        'manufacturer' => $detail['manufacturer'] ?? null,
                        'manufacturer_address' => $detail['manufacturerAddress'] ?? null,
                        'ship_to_party' => $detail['shipToParty'] ?? null,
                        'ship_to_party_address' => $detail['shipToPartyAddress'] ?? null,
                        'mother_vessel_date' => $detail['motherVesselDate'] ?? null,
                        'vessel_loaded_date' => $detail['vesselLoadedDate'] ?? null,
                        'consolidator_address' => $detail['consolidatorAddress'] ?? null,
                        'ein' => $detail['ein'] ?? null,
                        'inco' => $detail['inco'] ?? null,
                        'customer_rate' => $detail['customerRate'] ?? null,
                        'pallet_dimensions' => $detail['palletDimensions'] ?? null,
                        'cubic_meter' => $detail['cubicMeter'] ?? null,
                    ];

                    if ($detail['isOrderDetailsRequest'] === 'insert') {
                        $orderDetail = $order->orderDetails()->create($orderDetailData);
                        Log::info("Saving Order Details..." . $orderDetail->id);
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
                    } else {
                        Log::error("Order Details not found for update");
                    }

                    Log::info("Saving Order Container Details...");
                    $this->containerDetails($detail, $orderDetail);

                    Log::info("Saving Order Hormonize Details...");
                    $this->orderHormonizeDetails($detail, $orderDetail);


                    // Documement Upload
                    // if (!empty($detail['uploadDocuments'])) {
                    //     Log::info("Order Documents are uploading...");
                    //     $this->uploadImages($detail['uploadDocuments'], $order, $orderDetail);
                    // }

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
                                    'port_of_loading_id' => $delivery['portOfLoadingId'] ?? null,
                                    'port_of_discharge_id' => $delivery['portOfDischargeId'] ?? null,
                                    'destination_id' => $delivery['destinationId'] ?? null,
                                    'vendor_id' => $delivery['vendorId'] ?? null,
                                    'transhipment_port_id' => $delivery['transhipmentPortId'] ?? null,
                                    'delivery_created_by' => $delivery['deliveryCreatedBy'] ?? null,
                                    'ignate_cutoff_date' => $delivery['ignateCutoffDate'] ?? null,
                                    'country_of_origin' => $delivery['countryOfOrigin'] ?? null,
                                    'rail_ramp_id' => $delivery['railRampId'] ?? null,
                                    'receiver_name_id' => $delivery['receiverNameId'] ?? null,
                                    'receiver_address_id' => $delivery['receiverAddressId'] ?? null,
                                    'isChecked' => $delivery['isChecked'] ?? 0,
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
                                    // 1. update current status to 0 for the specific order delivery status
                                    OrderDeliveryStatus::where('order_delivery_id', $orderDelivery->id)->update(['current_status' => 0]);
                                    // 2. Create a new record with the current status set to 1
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
        } else {
            Log::info("Order Details are empty.");
        }
    }

    public function uploadImages($images, $order, $orderDetail)
    {
        Log::info("Uploading Images...");
        // Get existing images (if any)
        $existingImages = $order->upload_document ? explode(',', $order->upload_document) : [];

        $uploadedImages = [];

        if ($images) {
            $imageArray = explode(',', $images); // Split the string into an array
            foreach ($imageArray as $image) {
                // $sourcePath = public_path('images/upload/' . trim($image)); // Temporary location of the image
                if (!file_exists($image)) {
                    Log::error("Upload Documents Source file does not exist: " . $image);
                    throw new \InvalidArgumentException('Upload Documents Source file does not exist');
                }
                $uploadedImages[] = $image;
            }
            // Merge existing images with newly uploaded images
            $allImages = array_merge($existingImages, $uploadedImages);

            Log::info("allImages => ", $allImages);

            // Update the database with the new list of images
            $order->update([
                'upload_documents' => implode(',', $allImages) // Convert array to comma-separated string
            ]);
        }
    }

    public function uploadImagesOld(Request $request, $order, $orderDetail)
    {
        Log::info("Uploading Images for order => " . $order->id . " && order details id =>" . $orderDetail->id);

        // Get existing images (if any)
        $existingImages = $order->upload_document ? explode(',', $order->upload_document) : [];

        $uploadedImages = [];
        $destinationPath = 'images/order/' . $order->id . '/' . $orderDetail->id . '/';
        $images = $request->file('uploadDocuments');

        if (is_array($images)) {
            foreach ($images as $image) {
                Log::info("In case of Multiple image input");
                $imgName = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $imgName);
                $uploadedImages[] = $destinationPath . $imgName; // Collect only the file names
            }
        } elseif ($images) {
            Log::info("In case of single image input");
            $imgName = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $images->getClientOriginalExtension();
            $images->move($destinationPath, $imgName);
            $uploadedImages[] = $destinationPath . $imgName;
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
    public function getOrderNoteData(Request $request, $orderId)
    {
        $searchTerm = $request->input('searchTerm');
        $query = OrderNote::with('user:id,first_name,last_name');
        $query->where('order_id', $orderId)
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('tag', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($q) use ($searchTerm) {
                        $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('last_name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        // Log::info($query->toSql(), $query->getBindings());
        return $query->orderBy('id', 'desc')->get();
    }

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

    /**
     * Id is delivery id
     */
    public function getPdfData($deliveryId, $orderDetailsId, $orderId)
    {
        // Get Order Data
        $orders = Order::with([
            'customer:id,company_name',
            'address:id,company_name',
            'quote:id',
            'orderDetails' => function ($query) use ($orderDetailsId, $deliveryId) {
                // Filter the order details based on the order_detail_id and delivery_id
                $query->where('id', $orderDetailsId)
                    ->with([
                        'serviceType:id,name',
                        'orderContainerDetails',
                        'deliveries' => function ($query) use ($deliveryId) {
                            // Filter deliveries by delivery_id
                            $query->where('id', $deliveryId)
                                ->with([
                                    'portOfLoading:id,name',
                                    'portOfDischarge:id,name',
                                    'destination:id,name',
                                    'vendor:id,company_name,mc_number,scac_number,us_dot_number',
                                    'transhipmentPort:id,name',
                                    'createdBy:id,first_name,last_name',
                                    // 'statuses' => function ($query) {
                                    //     $query->with([
                                    //         'deliveryStatus:id,name'
                                    //     ]);
                                    // }
                                ]);
                        }
                    ]);
            }
        ])->where('id', $orderId)->get();

        return $orders;
    }

    /**
     * Summary of deliveryUpdateStatus
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order\OrderDeliveryStatus $orderDeliveryStatus
     * @return void
     */

    public function getDeliveryStatusData($serviceTypeId, $deliveryId): ?Collection
    {
        // // Get the latest data of max created time
        // $order = OrderStatusMaster::select('id', 'name', 'service_type_id') // Select specific fields from OrderStatusMaster
        //     ->with([
        //         'deliveryStatuses' => function ($query) use ($deliveryId) {
        //             $query->select('id', 'order_delivery_id', 'delivery_status_id', 'created_at') // Select specific fields
        //                 ->where('order_delivery_id', $deliveryId)
        //                 ->where('created_at', function ($subQuery) use ($deliveryId) {
        //                     $subQuery->selectRaw('MAX(created_at)')
        //                         ->from('order_delivery_statuses')
        //                         ->where('order_delivery_id', $deliveryId);
        //                 })
        //                 ->latest('created_at') // Fetch only the latest status for each delivery_id
        //                 ->limit(1); // Ensure only the latest status is selected
        //         }
        //     ])
        //     ->where('service_type_id', $serviceTypeId)
        //     ->get();

        $order = OrderStatusMaster::select('id', 'name',) // Select specific fields from OrderStatusMaster
            ->with([
                'deliveryStatuses' => function ($query) use ($deliveryId) {
                    $query->select('id', 'order_delivery_id', 'delivery_status_id', 'current_status', 'created_at') // Select specific fields
                        ->where('order_delivery_id', $deliveryId)
                        ->whereIn('delivery_status_id', function ($subQuery) use ($deliveryId) {
                            // Get the latest delivery status ids for the provided delivery_id
                            $subQuery->select('delivery_status_id')
                                ->from('order_delivery_statuses')
                                ->where('order_delivery_id', $deliveryId)
                                ->groupBy('delivery_status_id');
                        })
                        ->orderBy('created_at', 'desc')
                        ->limit(1);
                }
            ])
            ->where('service_type_id', $serviceTypeId)
            ->orderBy('sort_level', 'asc')
            ->get();

        return $order;
    }

    public function updateDeliveryStatus(Request $request, $deliveryId)
    {
        // 1. update current status set to 0 for the specific order delivery status
        OrderDeliveryStatus::where('order_delivery_id', $deliveryId)->update(['current_status' => 0]);

        // 2. Create a new record with the current status set to 1
        $order = OrderDeliveryStatus::create([
            'delivery_status_id' => $request['statusId'],
            'order_delivery_id' => $deliveryId,
            'current_status' => 1,
        ]);

        return $order;
    }
}

<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
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

    public function status(Request $request, $id)
    {
        // Use the statusUpdate helper to update status
        return statusUpdate(Order::class, $id, [
            'status' => $request->status
        ]);
    }

    public function customerValidateData(Request $request, $customerId = null)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string',
            'customer_type' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|integer',
            'country' => 'required|integer',
            'zip_code' => 'required|integer',
            'company_tax_id' => 'required|string',
            'payment_terms' => 'required|string',
            'credit_limit' => 'required|string',
            // 'contact_name' => 'required|string',
            // 'phone' => [
            //     'required',
            //     'string',
            //     'min:10',
            //     'max:15',
            //     Rule::unique('customers')->ignore($customerId),
            // ],
            // 'email' => [
            //     'nullable',
            //     'string',
            //     'email',
            //     'max:255',
            //     Rule::unique('customers')->ignore($customerId),
            // ],

            // Warehouse validation for each item in the array
            // 'warehouse' => 'required|array',
            // 'warehouse.*.warehouse_name' => 'required|string',
            // 'warehouse.*.warehouse_address' => 'required|string',
            // 'warehouse.*.warehouse_city' => 'required|string',
            // 'warehouse.*.warehouse_state' => 'required|string',
            // 'warehouse.*.warehouse_country' => 'required|string',
            // 'warehouse.*.warehouse_zip_code' => 'required|string',

            // shipping validation for each item in the array
            // 'shipping' => 'required|array',
            // 'shipping.*.shipping_name' => 'required|string',
            // 'shipping.*.shipping_address' => 'required|string',
            // 'shipping.*.shipping_city' => 'required|string',
            // 'shipping.*.shipping_state' => 'required|string',
            // 'shipping.*.shipping_country' => 'required|string',
            // 'shipping.*.shipping_zip_code' => 'required|string',

            // delivery validation for each item in the array
            'delivery' => 'required|array',
            'delivery.*.delivery_name' => 'required|string',
            'delivery.*.delivery_address' => 'required|string',
            'delivery.*.delivery_city' => 'required|string',
            'delivery.*.delivery_state' => 'required|integer',
            'delivery.*.delivery_country' => 'required|integer',
            'delivery.*.delivery_zip_code' => 'required|integer',

            // contact validation for each item in the array
            'contact' => 'required|array',
            'contact.*.contact_name' => 'required|string',
            'contact.*.contact_designation' => 'required|string',
            'contact.*.contact_phone' => 'required|numeric|digits_between:10,15',
            'contact.*.contact_email' => 'required|string|email|max:255',
            'contact.*.contact_fax' => 'nullable|string',

            // finance validation for each item in the array
            'finance' => 'required|array',
            'finance.*.finance_name' => 'required|string',
            'finance.*.finance_designation' => 'required|string',
            'finance.*.finance_phone' => 'required|numeric|digits_between:10,15',
            'finance.*.finance_email' => 'required|string|email|max:255',
            'finance.*.finance_fax' => 'nullable|string',

        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    protected $customerService;
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $customer = Customer::with([
            'country',
            'state',
            'warehouse',
            'shipping',
            'delivery',
            'contact',
            'finance'
        ])->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $customer
        ], 200);
    }

    public function store(Request $request)
    {

        $validatedData = $this->customerValidateData($request);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Customer validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction

        try {
            $this->customerService->createCustomer($request);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Customer created successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert customer data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to insert customer data',
                "error" => $e->getMessage()
            ], 500); // Return error response
        }
    }

    public function edit($customerId)
    {
        // Use the findModel helper to retrieve the customer
        $customer = findModel(Customer::class, $customerId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($customer instanceof \Illuminate\Http\JsonResponse) {
            return $customer;  // Return the not found response
        }

        $customer = Customer::with([
            'warehouse',
            'shipping',
            'delivery',
            'contact',
            'finance'
        ])->find($customerId);

        $data = $customer->toArray();

        // Check if the warehouse and shipping arrays have at least one entry and flatten them
        if (!empty($data['warehouse'])) {
            $warehouse = $data['warehouse'][0]; // Take the first element of the warehouse array
            unset($data['warehouse']); // Remove the warehouse array
            $data = array_merge($data, $warehouse); // Merge the first warehouse element into the customer array
        }

        if (!empty($data['shipping'])) {
            $shipping = $data['shipping'][0]; // Take the first element of the shipping array
            unset($data['shipping']); // Remove the shipping array
            $data = array_merge($data, $shipping); // Merge the first shipping element into the customer array
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }


    public function update(Request $request, $id)
    {
        // Use the findModel helper to retrieve the customer
        $customer = findModel(Customer::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($customer instanceof \Illuminate\Http\JsonResponse) {
            return $customer;  // Return the not found response
        }

        $validatedData = $this->customerValidateData($request,  $id);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json([
                'status' => false,
                'message' => 'Customer validation failed',
                'error' => $validatedData
            ], 422);
        }

        DB::beginTransaction();  // Start the transaction

        try {
            $customerMsg = $this->customerService->updateCustomer($request, $id, $customer);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Customer updated successfully"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update customer data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update customer data',
                'error' => $e->getMessage()
            ], 500); // Return error response
        }
    }

    public function destroy($customerId)
    {
        // Use the findModel helper to retrieve the customer
        $customer = findModel(Customer::class, $customerId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($customer instanceof \Illuminate\Http\JsonResponse) {
            return $customer;  // Return the not found response
        }

        DB::transaction(function () use ($customer) {

            // Delete all related records
            $customer->warehouse()->delete();
            $customer->shipping()->delete();
            $customer->delivery()->delete();
            $customer->contact()->delete();
            $customer->finance()->delete();

            // Delete the vendor record
            $customer->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Customer deleted successfully'
        ], 200);
    }

    public function status(Request $request, $customerId)
    {
        // Use the statusUpdate helper to update status
        return statusUpdate(Customer::class, $customerId, [
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
            'state' => 'required|string',
            'country' => 'required|string',
            'zip_code' => 'required|string',
            'company_tax_id' => 'required|string',
            'payment_terms' => 'required|string',
            'credit_limit' => 'required|string',
            'contact_name' => 'required|string',
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                Rule::unique('customers')->ignore($customerId),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customerId),
            ],

            // Warehouse validation for each item in the array
            // 'warehouse' => 'required|array',
            'warehouse.*.warehouse_name' => 'required|string',
            'warehouse.*.warehouse_address' => 'required|string',
            'warehouse.*.warehouse_city' => 'required|string',
            'warehouse.*.warehouse_state' => 'required|string',
            'warehouse.*.warehouse_country' => 'required|string',
            'warehouse.*.warehouse_zip_code' => 'required|string',

            // shipping validation for each item in the array
            // 'shipping' => 'required|array',
            'shipping.*.shipping_name' => 'required|string',
            'shipping.*.shipping_address' => 'required|string',
            'shipping.*.shipping_city' => 'required|string',
            'shipping.*.shipping_state' => 'required|string',
            'shipping.*.shipping_country' => 'required|string',
            'shipping.*.shipping_zip_code' => 'required|string',

            // delivery validation for each item in the array
            'delivery' => 'required|array',
            'delivery.*.delivery_name' => 'required|string',
            'delivery.*.delivery_address' => 'required|string',
            'delivery.*.delivery_city' => 'required|string',
            'delivery.*.delivery_state' => 'required|string',
            'delivery.*.delivery_country' => 'required|string',
            'delivery.*.delivery_zip_code' => 'required|string',

            // contact validation for each item in the array
            'contact' => 'required|array',
            'contact.*.contact_name' => 'required|string',
            'contact.*.contact_designation' => 'required|string',
            'contact.*.contact_phone' => 'required|string|min:10|max:15',
            'contact.*.contact_email' => 'required|string|email|max:255',
            'contact.*.contact_fax' => 'nullable|string',

            // finance validation for each item in the array
            'finance' => 'required|array',
            'finance.*.finance_name' => 'required|string',
            'finance.*.finance_designation' => 'required|string',
            'finance.*.finance_phone' => 'required|string|min:10|max:15',
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

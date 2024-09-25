<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
            'warehouse',
            'shipping',
            'delivery',
            'contact',
            'finance'
        ])->paginate(10);
        return response()->json($customer);
    }

    public function store(Request $request)
    {

        $validatedData = $this->customerValidateData($request);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json($validatedData, 422); // Return the validation error response
        }

        DB::beginTransaction();  // Start the transaction

        try {
            $customer = $this->customerService->createCustomer($request);
            DB::commit();
            return response()->json($customer, 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert customer data: ', ['error' => $e->getMessage()]);
            $error = ['success' => false, 'message' => 'Failed to insert customer data.', $e->getMessage()];
            return response()->json($error, 500); // Return error response
        }
    }

    public function edit($customerId)
    {
        try {
            Customer::findOrFail($customerId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
        }

        $customer = Customer::with([
            'warehouse',
            'shipping',
            'delivery',
            'contact',
            'finance'
        ])->find($customerId);

        return response()->json([
            'status' => true,
            'data' => $customer
        ], 200);
    }


    public function update(Request $request, $id)
    {
        $validatedData = $this->customerValidateData($request);

        // Check if the validated data is an array (i.e., no validation errors)
        if (!is_array($validatedData)) {
            return response()->json($validatedData, 422); // Return the validation error response
        }

        DB::beginTransaction();  // Start the transaction

        try {
            $customer = $this->customerService->updateCustomer($request, $id);
            DB::commit();
            return response()->json($customer, 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to insert customer data: ', ['error' => $e->getMessage()]);
            $error = ['success' => false, 'message' => 'Failed to insert customer data.', $e->getMessage()];
            return response()->json($error, 500); // Return error response
        }
    }
    public function destroy($customerId)
    {

        try {
            // Find the vendor or fail
            $customer = Customer::findOrFail($customerId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
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

        return response()->json(['status' => true, 'message' => 'Customer deleted successfully.'], 200);
    }
    public function customerValidateData(Request $request)
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

            // Warehouse validation for each item in the array
            'warehouse' => 'required|array',
            'warehouse.*.warehouse_name' => 'required|string',
            'warehouse.*.warehouse_address' => 'required|string',
            'warehouse.*.warehouse_city' => 'required|string',
            'warehouse.*.warehouse_state' => 'required|string',
            'warehouse.*.warehouse_country' => 'required|string',
            'warehouse.*.warehouse_zip_code' => 'required|string',

            // shipping validation for each item in the array
            'shipping' => 'required|array',
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
            $status = ['status' => false, 'errors' => $validator->errors()];
            return response()->json($status, 422);
        }

        // Return validated data
        return $validator->validated();
    }
}

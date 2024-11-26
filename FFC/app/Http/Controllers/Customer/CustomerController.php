<?php

namespace App\Http\Controllers\Customer;

use App\Exports\CustomerExport;
use App\Http\Controllers\Controller;
use App\Imports\CustomerImport;
use App\Models\Customer\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    protected $customerService;
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $customer = $this->customerService->getAllCustomer($request);
        $maxFinanceCount = $customer->max('finance_count');
        $maxContactCount = $customer->max('contact_count');

        // Convert the pagination result to an array to add custom data
        $customersArray = $customer->toArray();

        // Add max counts to the pagination result
        $customersArray['max_contact_count'] = $maxContactCount;
        $customersArray['max_finance_count'] = $maxFinanceCount;

        // Return the modified result as JSON
        return response()->json([
            'status' => true,
            'data' => $customersArray,
        ]);


        // return response()->json([
        //     'status' => true,
        //     'data' => $customer
        // ], 200);
    }

    public function store(Request $request)
    {
        Log::info("Saving Customer Details...");
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
            ], 400); // Return error response
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
            // 'warehouse',
            // 'shipping',
            'delivery',
            'contact',
            'finance'
        ])->find($customerId);

        $data = $customer->toArray();

        // // Check if the warehouse and shipping arrays have at least one entry and flatten them
        // if (!empty($data['warehouse'])) {
        //     $warehouse = $data['warehouse'][0]; // Take the first element of the warehouse array
        //     unset($data['warehouse']); // Remove the warehouse array
        //     $data = array_merge($data, $warehouse); // Merge the first warehouse element into the customer array
        // }
        // if (!empty($data['shipping'])) {
        //     $shipping = $data['shipping'][0]; // Take the first element of the shipping array
        //     unset($data['shipping']); // Remove the shipping array
        //     $data = array_merge($data, $shipping); // Merge the first shipping element into the customer array
        // }

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
            ], 400); // Return error response
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
            // $customer->warehouse()->delete();
            // $customer->shipping()->delete();
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
            // 'finance' => 'required|array',
            // 'finance.*.finance_name' => 'required|string',
            // 'finance.*.finance_designation' => 'required|string',
            // 'finance.*.finance_phone' => 'required|numeric|digits_between:10,15',
            // 'finance.*.finance_email' => 'required|string|email|max:255',
            // 'finance.*.finance_fax' => 'nullable|string',

        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return  $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }

    /**
     * Zero Count means there are already record exit while uploading
     */
    public function excelUpload(Request $request)
    {
        Log::info("*******************************");
        Log::info('Importing Customer Excel sheet...');
        Log::info("*******************************");

        try {
            // Validate that the file is required, must be Excel, and not exceed 2MB
            $validatedData = $request->validate([
                'uploadFile' => 'required|file|mimes:xlsx,xls',
                // 'updatedColumns' => 'required|array'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();

            // Instantiate PortImport before the import
            $excelImport = new CustomerImport($updatedColumns);

            // Perform the import
            Excel::import($excelImport, $request->file('uploadFile'));

            // Get Rows inserted count
            $validRowcount = $excelImport->getValidRowCount();
            Log::info("Valid rows count : " . $validRowcount);

            // Get Existing row count
            $existingRowcount = $excelImport->getExistingRowCount();
            Log::info("Existing Row Count : " . $existingRowcount[0]);
            Log::info("Existing Row Record : ", $existingRowcount[1]);

            // Check for any errors after the import
            $errorsResponse = $excelImport->getErrorsResponse();
            if ($errorsResponse) {
                DB::rollBack();
                return response()->json($errorsResponse, 400);
            }

            // Manually call afterImport to handle further processing
            $excelImport->afterImport();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Excel Upload Successfully',
                // 'inserted_records_count' => $validRowcount,
                // 'existing_records_count' => $existingRowcount[0],
                // 'existing_records_row_numbers' => implode(', ', $existingRowcount[1]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during the import process.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function excelUploadOld(Request $request)
    {
        Log::info('Importing Customer Excel sheet...');

        $request->validate([
            'uploadFile' => 'required|mimes:xlsx,xls,csv',
            // 'updatedColumns' => 'required|array'
        ]);

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();
            Excel::import(new CustomerImport($updatedColumns), $request->file('uploadFile'));
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Excel Upload Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }


    public function excelExport(Request $request)
    {
        Log::info("*****************************");
        Log::info('Exporting Customer Excel sheet...');
        Log::info("*****************************");

        $customer = $this->customerService->getAllCustomer($request);
        // Export to Excel
        return Excel::download(new CustomerExport($customer), 'Export_Customer_' . date('YmdHis') . '.xlsx');
    }
}

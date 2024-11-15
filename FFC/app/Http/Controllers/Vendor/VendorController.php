<?php

namespace App\Http\Controllers\Vendor;

use App\Exports\VendorExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Imports\VendorImport;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Services\Vendor\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{
    protected $vendorService;
    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    public function index_working_15_oct(Request $request)
    {
        $vendor = Vendor::with([
            'country:id,name',
            'state:id,name',
            'sales',
            'finance'
        ])
            ->select('id', 'company_name', 'contact_name', 'phone', 'email', 'status', 'city', 'country_id', 'state_id')
            ->paginate(2);
        return response()->json([
            "status" => "true",
            "data" => $vendor,
            // "data" => VendorResource::collection($vendor),
        ], 200);
    }

    public function index(Request $request)
    {
        $vendors = $this->vendorService->getAllVendorData($request);
        $maxSalesCount = $vendors->max('sales_count');
        $maxFinanceCount = $vendors->max('finance_count');
        // Convert the pagination result to an array to add custom data
        $vendorArray = $vendors->toArray();

        // Add max counts to the pagination result
        $vendorArray['max_sales_count'] = $maxSalesCount;
        $vendorArray['max_finance_count'] = $maxFinanceCount;

        // Return the modified result as JSON
        return response()->json([
            'status' => true,
            'data' => $vendorArray,
        ]);
        // // Return the modified vendor collection
        // return response()->json([
        //     'status' => true,
        //     'data' => $vendors
        // ]);
    }


    public function store(Request $request)
    {
        Log::info("*****************************");
        Log::info('Saving Vendor Details...');
        Log::info("*****************************");
        DB::beginTransaction();  // Start the transaction
        try {
            $validatedData = $this->vendorValidateData($request);

            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor validation failed',
                    'error' => $validatedData
                ], 422);
            }

            $this->vendorService->createVendor($request);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Vendor created successfully"
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Optionally, log the error for debugging
            Log::error('Failed to create vendor data: ', ['error' => $e->getMessage()]);

            // Return error response
            return response()->json([
                'status' => false,
                'message' => 'Failed to save vendor data',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function edit($vendorId)
    {
        Log::info("*****************************");
        Log::info('Fetching Vendor Details...');
        Log::info("*****************************");
        // Use the findModel helper to retrieve the vendor
        $vendor = findModel(Vendor::class, $vendorId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($vendor instanceof \Illuminate\Http\JsonResponse) {
            return $vendor;  // Return the not found response
        }

        $vendor = Vendor::with([
            'sales',
            'finance',
            'vendorTypes'
        ])->find($vendorId);

        if ($vendor && $vendor->vendorTypes) {
            // Transform the vendor_types to return only an array of IDs
            $vendor->vendor_types_id = $vendor->vendorTypes->pluck('id');
            // Optionally, you can unset the original vendorTypes to avoid confusion
            unset($vendor->vendorTypes);
        } else {
            // Handle the case where the vendor does not exist or has no types
            $vendor->vendor_types = [];
        }


        return response()->json([
            'status' => true,
            'data' => $vendor
        ], 200);
    }

    public function update(Request $request, $id)
    {
        Log::info("*****************************");
        Log::info('Updating Vendor Details...');
        Log::info("*****************************");
        // Use the findModel helper to retrieve the vendor
        $vendor = findModel(Vendor::class, $id);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($vendor instanceof \Illuminate\Http\JsonResponse) {
            return $vendor;  // Return the not found response
        }

        DB::beginTransaction();  // Start the transaction

        try {
            $validatedData = $this->vendorValidateData($request, $id);

            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor validation failed',
                    'error' => $validatedData
                ], 422);
            }

            $this->vendorService->updateVendor($request, $id, $vendor);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Vendor updated successfully",
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update vendor data: ', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to update vendor data',
                'error' => $e->getMessage()
            ], 400); // Return error response
        }
    }

    public function destroy($vendorId)
    {
        Log::info("*****************************");
        Log::info('Deleting Vendor Details...');
        Log::info("*****************************");
        // Use the findModel helper to retrieve the vendor
        $vendor = findModel(Vendor::class, $vendorId);

        // Check if the returned value is a JSON response (meaning the model was not found)
        if ($vendor instanceof \Illuminate\Http\JsonResponse) {
            return $vendor;  // Return the not found response
        }

        DB::transaction(function () use ($vendor) {
            // Delete all vendor related records
            $vendor->sales()->delete();
            $vendor->finance()->delete();
            // Unlink images
            $this->vendorService->unlinkImage($vendor->upload_document, $vendor->id);
            // $this->vendorService->unlinkImage($vendor->void_check);
            // $this->vendorService->unlinkImage($vendor->upload_w9);
            // $this->vendorService->unlinkImage($vendor->upload_insurance_certificate);
            // Delete the vendor record
            $vendor->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Vendor deleted successfully'
        ], 200);
    }

    public function status(Request $request, $vendorId)
    {
        return statusUpdate(Vendor::class, $vendorId, [
            'status' => $request->status
        ]);
    }

    /**
     * Zero Count means there are already record exit while uploading
     */
    public function excelUpload(Request $request)
    {
        Log::info("*******************************");
        Log::info('Importing Vendor Excel sheet...');
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
            $excelImport = new VendorImport($updatedColumns);

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
        Log::info("*****************************");
        Log::info('Importing Vendor Excel sheet...');
        Log::info("*****************************");
        $request->validate([
            'uploadFile' => 'required|mimes:xlsx,xls,csv',
            // 'updatedColumns' => 'required|array'
        ]);

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();
            Excel::import(new VendorImport($updatedColumns), $request->file('uploadFile'));
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
        Log::info('Exporting Vendor Excel sheet...');
        Log::info("*****************************");

        $vendors = $this->vendorService->getAllVendorData($request);
        // Export to Excel
        return Excel::download(new VendorExport($vendors), 'Export_Venodrs_' . date('YmdHis') . '.xlsx');
    }

    public function vendorValidateData(Request $request, $vendorId = null)
    {
        $validator = Validator::make($request->all(), [
            'vendor_type' => 'nullable|string',
            'company_name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|integer',
            'country' => 'required|integer',
            'zip_code' => 'required|integer',
            'company_tax_id' => 'nullable|string',
            'mc_number' => 'required|string',
            'scac_number' => 'required|string',
            'us_dot_number' => 'required|string',
            'upload_document' => 'nullable|array',
            'upload_document.*' => 'image|mimes:jpeg,png,jpg,gif|max:3048',
            // 'upload_w9' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            // 'void_check' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            // 'upload_insurance_certificate' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            'bank_name' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            'bank_routing' => 'nullable|string',
            'bank_address' => 'nullable|string',
            'remarks' => 'nullable|string',
            'bankCountry' => 'nullable|integer',
            'bankSwiftCode' => 'nullable|string',
            'bankIBANNumber' => 'nullable|string',
            'bankIFSCCode' => 'nullable|string',
            // 'date_of_expiration' => 'required|date',
            // 'contact_name' => 'required|string',
            // 'phone' => [
            //     'required',
            //     'string',
            //     'min:10',
            //     'max:15',
            //     Rule::unique('vendors')->ignore($vendorId),
            // ],
            // 'email' => [
            //     'nullable',
            //     'string',
            //     'email',
            //     'max:255',
            //     Rule::unique('vendors')->ignore($vendorId),
            // ],
            'paymentTerm' => 'required|string',

            // Vendor sale validation for each item in the array
            'sales' => 'required|array',
            'sales.*.sales_name' => 'required|string',
            'sales.*.sales_designation' => 'required|string',
            'sales.*.sales_phone' => 'required|numeric|digits_between:10,15',
            'sales.*.sales_email' => 'required|string|email|max:255',
            'sales.*.sales_fax' => 'nullable|string',

            // Vendor finance validation for each item in the array
            'finance' => 'required|array',
            'finance.*.finance_name' => 'required|string',
            'finance.*.finance_designation' => 'required|string',
            'finance.*.finance_phone' => 'required|numeric|digits_between:10,15',
            'finance.*.finance_email' => 'required|string|email|max:255',
            'finance.*.finance_fax' => 'nullable|string',

        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $validator->errors();
        }

        // Return validated data
        return $validator->validated();
    }

    // Vendor Type API Start...
    public function getAllVendorType()
    {
        // Get all active Vendor Type
        $vendorTypes = VendorType::getVendorTypes();
        return response()->json(['status' => true, 'data' => $vendorTypes], 200);
    }

    public function storeVendorType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor Type validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $vendorType = VendorType::create(['type' => $request->type]);
        if ($vendorType) {
            return response()->json(['status' => true, 'message' => 'Vendor Type created successfully'], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Failed to Save Vendor Type data'], 400);
        }
    }

    public function vendorStatus(Request $request, $id)
    {
        return statusUpdate(VendorType::class, $id, [
            'status' => $request->status
        ]);
    }
    // Vendor Type API END...
}

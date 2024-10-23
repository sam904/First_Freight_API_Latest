<?php

namespace App\Http\Controllers\Vendor;

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
        // Return the modified vendor collection
        return response()->json([
            'status' => true,
            'data' => $vendors
        ]);
    }


    public function store(Request $request)
    {
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
            ], 500);
        }
    }

    public function edit($vendorId)
    {
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
            ], 500); // Return error response
        }
    }

    public function destroy($vendorId)
    {
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
            $this->vendorService->unlinkImage($vendor->upload_w9);
            $this->vendorService->unlinkImage($vendor->void_check);
            $this->vendorService->unlinkImage($vendor->upload_insurance_certificate);
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

    public function excelUpload(Request $request)
    {
        Log::info('Importing Vendor Excel sheet...');

        $request->validate([
            'uploadFile' => 'required|mimes:xlsx,xls,csv',
            'updatedColumns' => 'required|array'
        ]);
        Log::info($request);

        $updatedColumns = $request->input('updatedColumns');

        try {
            DB::beginTransaction();
            Excel::import(new VendorImport($updatedColumns), $request->file('uploadFile'));
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Excel Upload Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function vendorValidateData(Request $request, $vendorId = null)
    {
        $validator = Validator::make($request->all(), [
            'vendor_type' => 'required|string',
            'company_name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zip_code' => 'required|string',
            'company_tax_id' => 'required|string',
            'mc_number' => 'required|string',
            'scac_number' => 'required|string',
            'us_dot_number' => 'required|string',
            'upload_w9' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            'void_check' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            'upload_insurance_certificate' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            'date_of_expiration' => 'required|date',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_routing' => 'required|string',
            'bank_address' => 'required|string',
            'remarks' => 'required|string',
            // 'contact_name' => 'required|string',
            // 'phone' => [
            //     'required',
            //     'string',
            //     'min:10',
            //     'max:15',
            //     Rule::unique('vendors')->ignore($vendorId),
            // ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('vendors')->ignore($vendorId),
            ],
            'paymentTerm' => 'required|string',

            // Vendor sale validation for each item in the array
            'sales' => 'required|array',
            'sales.*.sales_name' => 'required|string',
            'sales.*.sales_designation' => 'required|string',
            'sales.*.sales_phone' => 'required|string|min:10|max:15',
            'sales.*.sales_email' => 'required|string|email|max:255',
            'sales.*.sales_fax' => 'nullable|string',

            // Vendor finance validation for each item in the array
            'finance' => 'required|array',
            'finance.*.finance_name' => 'required|string',
            'finance.*.finance_designation' => 'required|string',
            'finance.*.finance_phone' => 'required|string|min:10|max:15',
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
            return response()->json(['status' => false, 'message' => 'Failed to Save Vendor Type data'], 500);
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

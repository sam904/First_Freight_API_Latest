<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\Vendor\VendorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VendorController extends Controller
{
    protected $vendorService;
    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    public function index(Request $request)
    {
        $vendor = Vendor::with(['sales', 'finance'])->paginate(10);
        return response()->json([
            "status" => "success",
            "data" => $vendor
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();  // Start the transaction
        try {
            $validatedData = $this->vendorValidateData($request);

            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json($validatedData, 422); // Return the validation error response
            }

            $vendor = $this->vendorService->createVendor($request);

            DB::commit();

            return response()->json($vendor, 200);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            // DB::rollBack();

            // Optionally, log the error for debugging
            Log::error('Failed to create vendor data: ', ['error' => $e->getMessage()]);

            $error = ['success' => false, 'message' => 'Failed to save vendor data.', $e->getMessage()];
            // Return error response
            return response()->json($error, 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();  // Start the transaction

        try {

            $validatedData = $this->vendorValidateData($request);

            // Check if the validated data is an array (i.e., no validation errors)
            if (!is_array($validatedData)) {
                return response()->json($validatedData, 422); // Return the validation error response
            }

            $vendor = $this->vendorService->updateVendor($request, $id);

            DB::commit();

            return response()->json($vendor, 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if something goes wrong            
            Log::error('Failed to update vendor data: ', ['error' => $e->getMessage()]);
            $error = ['success' => false, 'message' => 'Failed to update vendor data.', $e->getMessage()];
            return response()->json($error, 500); // Return error response
        }
    }

    public function destroy($vendorId)
    {

        try {
            // Find the vendor or fail
            $vendor = Vendor::findOrFail($vendorId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'error' => 'Vendor not found'], 404);
        }

        DB::transaction(function () use ($vendor) {

            // Delete all related records
            $vendor->sales()->delete();
            $vendor->finance()->delete();

            // Delete the vendor record
            $vendor->delete();
        });

        return response()->json(['message' => 'Vendor deleted successfully.'], 200);
    }

    public function vendorValidateData(Request $request)
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
            // 'upload_w9' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            // 'void_check' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            // 'upload_insurance_certificate' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            'date_of_expiration' => 'required|date',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_routing' => 'required|string',
            'bank_address' => 'required|string',
            'remarks' => 'required|string',

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
            $status = [
                'status' => false,
                'errors' => $validator->errors()
            ];
            return response()->json($status, 422);
        }

        // Return validated data
        return $validator->validated();
    }
}

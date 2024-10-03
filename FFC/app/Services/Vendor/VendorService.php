<?php

namespace App\Services\Vendor;

use App\Models\Vendor;
use App\Models\VendorFinances;
use App\Models\VendorSales;
use Illuminate\Http\Request;

class VendorService
{
    public function __construct() {}

    public function createVendor(Request $request)
    {
        $vendorImage['upload_w9'] = $this->uploadImages($request->file('upload_w9'));
        $vendorImage['void_check'] = $this->uploadImages($request->file('void_check'));
        $vendorImage['upload_insurance_certificate'] = $this->uploadImages($request->file('upload_insurance_certificate'));

        $vendor = Vendor::create([
            'vendor_type' => $request['vendor_type'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state' => $request['state'],
            'country' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'],
            'mc_number' => $request['mc_number'],
            'scac_number' => $request['scac_number'],
            'us_dot_number' => $request['us_dot_number'],
            'upload_w9' => $vendorImage['upload_w9'],
            'void_check' => $vendorImage['void_check'],
            'upload_insurance_certificate' => $vendorImage['upload_insurance_certificate'],
            'date_of_expiration' => $request['date_of_expiration'],
            'bank_name' => $request['bank_name'],
            'bank_account_number' => $request['bank_account_number'],
            'bank_routing' => $request['bank_routing'],
            'bank_address' => $request['bank_address'],
            'remarks' => $request['remarks'],
            'contact_name' => $request['contact_name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
        ]);

        // Create sales records
        $this->storeSales($request, $vendor);

        // Create finance records
        $this->storeFinance($request, $vendor);

        return true;
    }

    public function updateVendor(Request $request, $id, Vendor $vendor)
    {

        // Store images name in $image array
        $vendorOldImages['upload_w9'] = $vendor->upload_w9;
        $vendorOldImages['void_check'] = $vendor->void_check;
        $vendorOldImages['upload_insurance_certificate'] = $vendor->upload_insurance_certificate;

        // Delete existing related records
        $vendor->sales()->delete();      // Delete all sales records related to this vendor
        $vendor->finance()->delete();   // Delete all finance records related to this vendor

        if ($image = $request->file('upload_w9')) {
            $vendorImage['upload_w9'] = $this->uploadImages($request->file('upload_w9'));
        } else {
            unset($request['upload_w9']);
        }
        if ($image = $request->file('void_check')) {
            $vendorImage['void_check'] = $this->uploadImages($request->file('void_check'));
        } else {
            unset($request['void_check']);
        }
        if ($image = $request->file('upload_insurance_certificate')) {
            $vendorImage['upload_insurance_certificate'] = $this->uploadImages($request->file('upload_insurance_certificate'));
        } else {
            unset($request['upload_insurance_certificate']);
        }

        // Update vendor details
        $vendor->update([
            'vendor_type' => $request['vendor_type'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state' => $request['state'],
            'country' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'],
            'mc_number' => $request['mc_number'],
            'scac_number' => $request['scac_number'],
            'us_dot_number' => $request['us_dot_number'],
            'upload_w9' => $vendorImage['upload_w9'],
            'void_check' => $vendorImage['void_check'],
            'upload_insurance_certificate' => $vendorImage['upload_insurance_certificate'],
            'date_of_expiration' => $request['date_of_expiration'],
            'bank_name' => $request['bank_name'],
            'bank_account_number' => $request['bank_account_number'],
            'bank_routing' => $request['bank_routing'],
            'bank_address' => $request['bank_address'],
            'remarks' => $request['remarks'],
            'contact_name' => $request['contact_name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
        ]);

        // Create sales records
        $this->storeSales($request, $vendor);

        // Create finance records
        $this->storeFinance($request, $vendor);

        // Now unlink image
        $this->unlinkImage($vendorOldImages['upload_w9']);
        $this->unlinkImage($vendorOldImages['void_check']);
        $this->unlinkImage($vendorOldImages['upload_insurance_certificate']);

        return true;
    }

    public function storeSales(Request $request, $vendor)
    {
        $salesData = $request->input('sales');
        $sales = [];

        foreach ($salesData as $salesItem) {
            $sales[] = new VendorSales([
                'sales_name' => $salesItem['sales_name'],
                'sales_designation' => $salesItem['sales_designation'],
                'sales_phone' => $salesItem['sales_phone'],
                'sales_email' => $salesItem['sales_email'],
                'sales_fax' => $salesItem['sales_fax'],
                'vendors_id' => $vendor->id
            ]);
        }

        // Save all sales related to vendor
        $vendor->sales()->saveMany($sales);
    }

    public function storeFinance(Request $request, $vendor)
    {

        $financeData = $request->input('finance');
        $finance = [];

        foreach ($financeData as $financeItem) {
            $finance[] = new VendorFinances([
                'finance_name' => $financeItem['finance_name'],
                'finance_designation' => $financeItem['finance_designation'],
                'finance_phone' => $financeItem['finance_phone'],
                'finance_email' => $financeItem['finance_email'],
                'finance_fax' => $financeItem['finance_fax'],
                'vendors_id' => $vendor->id
            ]);
        }

        $vendor->finance()->saveMany($finance);
    }

    public function uploadImages($image)
    {
        $destinationPath = 'images/profiles/vendor/';
        $img_upload = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $image->getClientOriginalExtension();
        $image->move($destinationPath, $img_upload);
        return $img_upload;
    }

    public function unlinkImage($imageName)
    {
        $filePath = public_path('images/profiles/vendor/' . $imageName); // Get full path of the image

        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file
        }
    }
}

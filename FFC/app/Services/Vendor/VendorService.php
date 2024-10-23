<?php

namespace App\Services\Vendor;

use App\Helpers\SearchHelper;
use App\Models\Country;
use App\Models\State;
use App\Models\Vendor;
use App\Models\VendorFinances;
use App\Models\VendorSales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorService
{
    public function getAllVendorData(Request $request)
    {

        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');

        // Get all column names of the 'users' table
        $model = new Vendor();

        $query = Vendor::with([
            'country:id,name',
            'state:id,name',
            'sales',
            'finance'
        ]);

        $saleFlag = false;
        $financeFlag = false;
        $countryFlag = false;
        $stateFlag = false;

        // Check if filterBy contains sales. or finance. and adjust accordingly
        if (
            strpos($filterBy, 'sales.') === 0 ||
            strpos($filterBy, 'finance.') === 0 ||
            strpos($filterBy, 'country.') === 0 ||
            strpos($filterBy, 'state.') === 0
        ) {
            // Set filterBy to null in the request
            $request->merge(['filterBy' => null]);
            if (strpos($filterBy, 'sales.') === 0) {
                $filterBy = substr($filterBy, strlen('sales.'));
                $saleFlag = true;
            } elseif (strpos($filterBy, 'finance.') === 0) {
                $filterBy = substr($filterBy, strlen('finance.'));
                $financeFlag = true;
            } elseif (strpos($filterBy, 'country.') === 0) {
                $filterBy = substr($filterBy, strlen('country.'));
                $countryFlag = true;
            } elseif (strpos($filterBy, 'state.') === 0) {
                $filterBy = substr($filterBy, strlen('state.'));
                $stateFlag = true;
            }
        }

        if ($filterBy == null) {
            Log::info('filter by is null');
            $saleFlag = true;
            $financeFlag = true;
            $stateFlag = true;
            $countryFlag = true;
        }

        // Apply search filters
        $query = SearchHelper::applySearchFilters($query, $model, $request);

        // Search by vendor type if filterBy is 'vendor_type'
        if (!empty($searchTerm) && $filterBy === 'vendor_type') {
            $query->whereHas('vendorTypes', function ($q) use ($searchTerm) {
                Log::info('vendorTypes => ' . $searchTerm);
                // Search in vendor_types table based on the search term
                $q->where('type', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Search within related Sales fields
        if ($saleFlag) {
            $saleModel = new VendorSales();
            $searchableSalesColumns = $saleModel->getSearchableColumns();
            $query->orWhereHas('sales', function ($query) use ($searchTerm, $filterBy, $searchableSalesColumns) {
                if ($filterBy && in_array($filterBy, $searchableSalesColumns)) {
                    Log::info('sales FilterBy =' . $filterBy);
                    $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                } else {
                    Log::info('Sales Search on whole table =' . $searchTerm);
                    $query->where('sales_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_designation', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_fax', 'LIKE', "%{$searchTerm}%");
                }
            });
        }

        // Search within related Finance fields
        if ($financeFlag) {
            $financeModel = new VendorFinances();
            $searchableFinanceColumns = $financeModel->getSearchableColumns();
            $query->orWhereHas('finance', function ($query) use ($searchTerm, $filterBy, $searchableFinanceColumns) {
                if ($filterBy && in_array($filterBy, $searchableFinanceColumns)) {
                    Log::info('finance FilterBy =' . $filterBy);
                    $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                } else {
                    Log::info('finance Search on whole table =' . $searchTerm);
                    $query->where('finance_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_designation', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_fax', 'LIKE', "%{$searchTerm}%");
                }
            });
        }

        if ($countryFlag) {
            $countryModel = new Country();
            $searchableCountryColumns = $countryModel->getSearchableColumns();
            $query->orWhereHas('country', function ($query) use ($searchTerm, $filterBy, $searchableCountryColumns) {
                if ($filterBy && in_array($filterBy, $searchableCountryColumns)) {
                    Log::info('country FilterBy =' . $filterBy);
                    $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                } else {
                    Log::info('country Search on whole table =' . $searchTerm);
                    $query->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('iso_code', 'LIKE', "%{$searchTerm}%");
                }
            });
        }

        if ($stateFlag) {
            $stateModel = new State();
            $searchableStateColumns = $stateModel->getSearchableColumns();
            $query->orWhereHas('state', function ($query) use ($searchTerm, $filterBy, $searchableStateColumns) {
                if ($filterBy && in_array($filterBy, $searchableStateColumns)) {
                    Log::info('state FilterBy =' . $filterBy);
                    $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                } else {
                    Log::info('state Search on whole table =' . $searchTerm);
                    $query->where('name', 'LIKE', "%{$searchTerm}%");
                }
            });
        }

        // Get the paginated results
        $vendors = $query->orderBy("id", "desc")->paginate(10);

        // Modify each vendor to flatten 'sales' and 'finance' into the main array
        // $vendors->through(function ($vendor) {
        $vendors->getCollection()->each(function ($vendor) {
            // Flatten country fields into the main vendor array
            if ($vendor->country) {
                $vendor->setAttribute('country_name', $vendor->country->name);
                unset($vendor->country); // Remove the original nested country object
            }

            // Flatten state fields into the main vendor array
            if ($vendor->state) {
                $vendor->setAttribute('state_name', $vendor->state->name);
                unset($vendor->state); // Remove the original nested state object
            }

            // Flatten sales fields into the main vendor array
            foreach ($vendor->sales as $index => $sale) {
                $vendor->setAttribute('sales_name_' . ($index + 1), $sale->sales_name);
                $vendor->setAttribute('sales_designation_' . ($index + 1), $sale->sales_designation);
                $vendor->setAttribute('sales_phone_' . ($index + 1), $sale->sales_phone);
                $vendor->setAttribute('sales_email_' . ($index + 1), $sale->sales_email);
                $vendor->setAttribute('sales_fax_' . ($index + 1), $sale->sales_fax);
                $vendor->setAttribute('sales_created_at_' . ($index + 1), $sale->created_at);
                $vendor->setAttribute('sales_updated_at_' . ($index + 1), $sale->updated_at);
            }

            // Flatten finance fields into the main vendor array
            foreach ($vendor->finance as $index => $finance) {
                $vendor->setAttribute('finance_name_' . ($index + 1), $finance->finance_name);
                $vendor->setAttribute('finance_designation_' . ($index + 1), $finance->finance_designation);
                $vendor->setAttribute('finance_phone_' . ($index + 1), $finance->finance_phone);
                $vendor->setAttribute('finance_email_' . ($index + 1), $finance->finance_email);
                $vendor->setAttribute('finance_fax_' . ($index + 1), $finance->finance_fax);
                $vendor->setAttribute('finance_created_at_' . ($index + 1), $finance->created_at);
                $vendor->setAttribute('finance_updated_at_' . ($index + 1), $finance->updated_at);
            }

            // Remove the original sales and finance arrays
            unset($vendor->sales, $vendor->finance);

            return $vendor;
        });

        return $vendors;
    }


    public function createVendor(Request $request)
    {
        $vendorImage['upload_w9'] = $this->uploadImages($request->file('upload_w9'));
        $vendorImage['void_check'] = $this->uploadImages($request->file('void_check'));
        $vendorImage['upload_insurance_certificate'] = $this->uploadImages($request->file('upload_insurance_certificate'));

        $vendor = Vendor::create([
            // 'vendor_type_id' => $request['vendor_type'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
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
            // 'contact_name' => $request['contact_name'],
            // 'phone' => $request['phone'],
            'email' => $request['email'],
            'payment_term' => $request['paymentTerm'],
        ]);

        // Create sales records
        $this->storeSales($request, $vendor);

        // Create finance records
        $this->storeFinance($request, $vendor);

        // Saving Vendor Type Id
        $vendorTypeIds = explode(',', $request->input('vendor_type'));
        $vendor->vendorTypes()->attach($vendorTypeIds);

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
            // 'vendor_type_id' => $request['vendor_type'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
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
            // 'contact_name' => $request['contact_name'],
            // 'phone' => $request['phone'],
            'email' => $request['email'],
            'payment_term' => $request['paymentTerm'],
        ]);

        // Create sales records
        $this->storeSales($request, $vendor);

        // Create finance records
        $this->storeFinance($request, $vendor);

        // Now unlink image
        if (isset($vendorOldImages['upload_w9'])) {
            $this->unlinkImage($vendorOldImages['upload_w9']);
        }
        if (isset($vendorOldImages['void_check'])) {
            $this->unlinkImage($vendorOldImages['void_check']);
        }
        if (isset($vendorOldImages['upload_insurance_certificate'])) {
            $this->unlinkImage($vendorOldImages['upload_insurance_certificate']);
        }

        // Sync the vendor types (this will remove the old types and add the new ones)
        $vendorTypeIds = explode(',', $request->input('vendor_type'));
        $vendor->vendorTypes()->sync($vendorTypeIds);

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

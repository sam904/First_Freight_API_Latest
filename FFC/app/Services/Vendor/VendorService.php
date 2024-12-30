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
    public function getAllVendorDataFilterBy(Request $request)
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
        $model = new Vendor();

        $query = Vendor::with([
            'bankCountry:id,name',
            'country:id,name',
            'state:id,name',
            'vendorTypes',
            'sales',
            'finance',
        ])->withCount(['sales', 'finance']);

        // Apply filter by IDs if they are provided
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $saleFlag = false;
        $financeFlag = false;
        $countryFlag = false;
        $stateFlag = false;
        $vendorTypeFlag = false;

        // Check if filterBy contains sales. or finance. and adjust accordingly
        if (
            strpos($filterBy, 'sales.') === 0 ||
            strpos($filterBy, 'finance.') === 0 ||
            strpos($filterBy, 'country.') === 0 ||
            strpos($filterBy, 'state.') === 0 ||
            $filterBy === 'vendorType'
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
            } elseif ($filterBy === 'vendorType') {
                $vendorTypeFlag = true;
            }
        }

        if ($filterBy == null) {
            Log::info('filter by is null');
            $saleFlag = true;
            $financeFlag = true;
            $stateFlag = true;
            $countryFlag = true;
            $vendorTypeFlag = true;
        }

        // Apply search filters
        $query = SearchHelper::applySearchFilters($query, $model, $request);

        $query->where(
            function ($query) use ($searchTerm, $filterBy, $vendorTypeFlag, $saleFlag, $financeFlag, $countryFlag, $stateFlag) {
                // Search by vendor type if filterBy is 'vendor_type'
                if ($vendorTypeFlag) {
                    // if (!empty($searchTerm) && $filterBy === 'vendorType') {
                    $query->whereHas('vendorTypes', function ($q) use ($searchTerm) {
                        Log::info('vendorTypes => ' . $searchTerm);
                        // Search in vendor_types table based on the search term
                        $q->where('type', 'LIKE', "%{$searchTerm}%");
                    });
                    // }
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
            }
        );
        if ($isExport && empty($limit)) {
            // Fetch all data without pagination
            Log::info("export is true and limit is empty");
            $startTime = microtime(true);
            $limit = $query->count();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            Log::info("Vendor Query Count = {$limit} && execution time: {$executionTime} seconds");
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        } else {
            $limit = $limit ?: 10;
            $vendors = $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        }

        return $vendors;
    }


    public function getAllVendorData(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit');
        $sortColumn = $request->input('sortColumn') ?: 'id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        $isExport = $request->input('export') ?? false;
        $ids = $request->input('ids');

        // Get all column names of the 'Vendors' table
        $model = new Vendor();

        $query = Vendor::with([
            'bankCountry:id,name',
            'country:id,name',
            'state:id,name',
            'vendorTypes',
            'sales',
            'finance',
        ])->withCount(['sales', 'finance']);

        // Apply filter by IDs if they are provided
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        // Apply search filters
        // $query = SearchHelper::applySearchFilters($query, $model, $request);

        // Apply search filters across related models
        $query->where(function ($query) use ($searchTerm) {
            if (!empty($searchTerm)) {
                $query->where('company_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('city', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('zip_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('company_tax_id', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('mc_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('scac_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('us_dot_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bank_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bank_account_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bank_routing', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bank_address', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bank_swift_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bank_iban_number', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bank_ifsc_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('remarks', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%");

                // Search within related Sales fields
                $query->orWhereHas('sales', function ($q) use ($searchTerm) {
                    $q->where('sales_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_designation', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sales_fax', 'LIKE', "%{$searchTerm}%");
                });

                // Search within related Finance fields
                $query->orWhereHas('finance', function ($q) use ($searchTerm) {
                    $q->where('finance_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_designation', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('finance_fax', 'LIKE', "%{$searchTerm}%");
                });

                // Search within related Country fields
                $query->orWhereHas('country', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('iso_code', 'LIKE', "%{$searchTerm}%");
                });

                // Search within related State fields
                $query->orWhereHas('state', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });

                // Search within Vendor Types
                $query->orWhereHas('vendorTypes', function ($q) use ($searchTerm) {
                    $q->where('type', 'LIKE', "%{$searchTerm}%");
                });
            }
        });

        // Handle export case
        if ($isExport && empty($limit)) {
            // Fetch all data without pagination
            $limit = $query->count();
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        } else {
            // Apply pagination
            $limit = $limit ?: 10;
            $vendors = $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        }

        return $vendors;
    }

    public function createVendor(Request $request)
    {
        // $vendorImage['upload_w9'] = $this->uploadImages($request->file('upload_w9'));
        // $vendorImage['void_check'] = $this->uploadImages($request->file('void_check'));
        // $vendorImage['upload_insurance_certificate'] = $this->uploadImages($request->file('upload_insurance_certificate'));

        // if ($request->hasFile('upload_document')) {
        //     $this->uploadImages($request, null);
        //     // $vendorImage['upload_document'] = $this->uploadImages($request->file('upload_document'));
        //     return;
        // }

        $vendor = Vendor::create([
            // 'vendor_type_id' => $request['vendor_type'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'] ?? null,
            'mc_number' => $request['mc_number'],
            'scac_number' => $request['scac_number'],
            'us_dot_number' => $request['us_dot_number'],
            // 'upload_w9' => $vendorImage['upload_w9'],
            // 'void_check' => $vendorImage['void_check'],
            // 'upload_insurance_certificate' => $vendorImage['upload_insurance_certificate'],
            'bank_name' => $request['bank_name'] ?? null,
            'bank_account_number' => $request['bank_account_number'] ?? null,
            'bank_routing' => $request['bank_routing'] ?? null,
            'bank_address' => $request['bank_address'] ?? null,
            'bank_country_id' => $request['bankCountry'] ?? null,
            'bank_swift_code' => $request['bankSwiftCode'] ?? null,
            'bank_iban_number' => $request['bankIBANNumber'] ?? null,
            'bank_ifsc_code' => $request['bankIFSCCode'] ?? null,
            'remarks' => $request['remarks'],
            // 'date_of_expiration' => $request['date_of_expiration'],
            // 'contact_name' => $request['contact_name'],
            // 'phone' => $request['phone'],
            // 'email' => $request['email'],
            'payment_term' => $request['paymentTerm'],
        ]);

        if ($request->hasFile('upload_document')) {
            $this->uploadImages($request, $vendor);
        }

        // Create sales records
        $this->storeSales($request, $vendor);

        // Create finance records
        $this->storeFinance($request, $vendor);

        // Saving Vendor Type Id
        if (isset($request['vendor_type']) && !empty($request['vendor_type'])) {
            $vendorTypeIds = explode(',', $request->input('vendor_type'));
            $vendor->vendorTypes()->attach($vendorTypeIds);
        }

        return true;
    }

    public function updateVendor(Request $request, $id, Vendor $vendor)
    {
        // Get Old Images 
        $vendorOldImages['upload_document'] = $vendor->upload_document;

        // Delete existing related records
        $vendor->sales()->delete();      // Delete all sales records related to this vendor
        $vendor->finance()->delete();   // Delete all finance records related to this vendor

        // Update vendor details
        $vendor->update([
            // 'vendor_type_id' => $request['vendor_type'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'] ?? null,
            'mc_number' => $request['mc_number'],
            'scac_number' => $request['scac_number'],
            'us_dot_number' => $request['us_dot_number'],
            // 'upload_w9' => $vendorImage['upload_w9'],
            // 'void_check' => $vendorImage['void_check'],
            // 'upload_insurance_certificate' => $vendorImage['upload_insurance_certificate'],
            'bank_name' => $request['bank_name'] ?? null,
            'bank_account_number' => $request['bank_account_number'] ?? null,
            'bank_routing' => $request['bank_routing'] ?? null,
            'bank_address' => $request['bank_address'] ?? null,
            'bank_country_id' => $request['bankCountry'] ?? null,
            'bank_swift_code' => $request['bankSwiftCode'] ?? null,
            'bank_iban_number' => $request['bankIBANNumber'] ?? null,
            'bank_ifsc_code' => $request['bankIFSCCode'] ?? null,
            'remarks' => $request['remarks'],
            // 'date_of_expiration' => $request['date_of_expiration'],
            // 'contact_name' => $request['contact_name'],
            // 'phone' => $request['phone'],
            // 'email' => $request['email'],
            'payment_term' => $request['paymentTerm'],
        ]);

        // Create sales records
        $this->storeSales($request, $vendor);

        // Create finance records
        $this->storeFinance($request, $vendor);

        if ($request->hasFile('upload_document')) {
            $this->uploadImages($request, $vendor);
        }

        // Now unlink(delete) image
        if (isset($vendorOldImages['upload_document'])) {
            $this->unlinkImage($vendorOldImages['upload_document'], $vendor->id);
        }

        // Sync the vendor types (this will remove the old types and add the new ones)
        $vendorTypeIds = explode(',', $request->input('vendor_type'));
        $vendor->vendorTypes()->sync($vendorTypeIds);

        return true;
    }

    public function storeSales(Request $request, $vendor)
    {
        Log::info("Saving Sales details for vendor : " . $vendor->id);
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
        Log::info("Saving Finance details for vendor : " . $vendor->id);
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

    public function uploadImages(Request $request, $vendor)
    {
        Log::info("Uploading Images for vendor = " . $vendor->id);
        $uploadedImages = [];
        $destinationPath = 'images/vendor/' . $vendor->id . '/';
        $images = $request->file('upload_document');
        if (is_array($images)) {
            Log::info("Array of Images");
            foreach ($images as $image) {
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
        $vendor->update([
            'upload_document' => implode(',', $uploadedImages)
        ]);
        // return implode(',', $uploadedImages); // Convert array to comma-separated string
    }

    public function unlinkImage($images, $id = null)
    {
        $imageArray = explode(',', $images);

        foreach ($imageArray as $imageName) {
            // Trim any whitespace around the image name
            $imageName = trim($imageName);
            if (str_contains($imageName, '/')) {
                $filePath = public_path($imageName);
            } else {
                $filePath = public_path("images/vendor/" . $id . "/" . $imageName);
            }
            // Check if the file exists before attempting to delete
            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file
            }
        }
    }

    public function uploadImagesOld($image)
    {
        $destinationPath = 'images/profiles/vendor/';
        $img_upload = date('YmdHis') . str_replace('.', '', microtime(true)) . "." . $image->getClientOriginalExtension();
        $image->move($destinationPath, $img_upload);
        return $img_upload;
    }

    public function unlinkImageOld($imageName)
    {
        $filePath = public_path('images/profiles/vendor/' . $imageName); // Get full path of the image

        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file
        }
    }
}

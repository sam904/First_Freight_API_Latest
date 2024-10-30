<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Vendor;
use App\Models\Sale;
use App\Models\Finance;
use App\Models\State;
use App\Models\VendorFinances;
use App\Models\VendorSales;
use App\Models\VendorType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class VendorImport implements ToCollection, WithHeadingRow
{
    private $currentVendor = null;
    private $updatedColumns;

    private $saleFlag = true;
    private $financeFlag = true;

    // Constructor to accept the columns to be updated
    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
    }
    /** @Process :
     * Check company name, If company name exist then do not process anything else insert row
     */
    public function collection(Collection $rows)
    {
        Log::info('Vendor update Columns Data => ' . json_encode($this->updatedColumns));
        foreach ($rows as $row) {
            Log::info($row);
            $this->currentVendor = $this->updateOrCreateVendor($row);
        }
    }

    private function updateOrCreateVendor($row)
    {
        Log::info("Find existing vendor based on unique fields (company_name) => " . $row['company_name']);
        $vendor = Vendor::where('company_name', $row['company_name'])->first();

        if (empty($vendor)) {
            Log::info("Creating vendor for => " . $row['company_name']);
            $vendorData = $this->processVendorData($row);
            $vendorData['status'] = 'inactive';
            Log::info('Saving Vendor datails...');
            $vendor = Vendor::create($vendorData);

            // Saving Vendor Type, Sales, Finance data
            $this->processVendorTypeData($vendor, $row['vendor_type']);
            $this->processSalesData($row, $vendor);
            $this->processFinanceData($row, $vendor);
        } else {
            Log::info($vendor->company_name . ' is exit. Skipping to next iteration.');
        }

        return $vendor;
    }


    private function processVendorData($row)
    {
        // Find state
        $state = $this->getState($row['state']);
        Log::info("State" . json_encode($state));
        // Find Country
        $country = $this->getCountry($row['country']);
        // Find Bank Country
        $bankCountry = $this->getCountry($row['bank_country']);

        $vendorData = [
            'company_name' => $row['company_name'],
            'address' => $row['address'],
            'city' => $row['city'],
            'state_id' => $state->id,
            'country_id' => $country->id,
            'zip_code' => $row['zip_code'],
            'company_tax_id' => $row['company_tax_id'],
            'mc_number' => $row['mc_number'],
            'scac_number' => $row['scac_number'],
            'us_dot_number' => $row['us_dot_number'],
            'payment_term' => $row['payment_term'],
            'bank_name' => $row['bank_name'],
            'bank_account_number' => $row['bank_account_number'],
            'bank_routing' => $row['bank_routing'],
            'bank_address' => $row['bank_address'],
            'bank_country_id' => $bankCountry->id,
            'bank_swift_code' => $row['bank_swift_code'],
            'bank_iban_number' => $row['bank_iban_number'],
            'bank_ifsc_code' => $row['bank_ifsc_code'],
            'remarks' => $row['remarks'],
        ];
        return $vendorData;
    }

    private function processVendorTypeData($vendor, $vendorType)
    {
        // Find Vendor Type 
        $vendorType = $this->getVendorType($vendorType);
        // Save vendor type id
        $vendor->vendorTypes()->attach($vendorType->id);
    }

    private function processSalesData($row, Vendor $vendor = null)
    {
        Log::info('Saving Sale data for vendor id = ' . $vendor->id);
        VendorSales::create([
            'vendors_id' => $vendor->id,
            'sales_name' => $row['sales_name'],
            'sales_designation' => $row['sales_designation'],
            'sales_phone' => $row['sales_phone'],
            'sales_email' => $row['sales_email'],
            'sales_fax' => $row['sales_fax'],
        ]);
    }

    private function processFinanceData($row, Vendor $vendor = null)
    {
        Log::info('Saving Finance data...' . $vendor->id);
        VendorFinances::create([
            'vendors_id' => $vendor->id,
            'finance_name' => $row['finance_name'],
            'finance_designation' => $row['finance_designation'],
            'finance_phone' => $row['finance_phone'],
            'finance_email' => $row['finance_email'],
            'finance_fax' => $row['finance_fax'],
        ]);
    }

    private function getVendorType($type)
    {
        return VendorType::where('type', $type)->firstOrFail();
    }

    private function getState($name)
    {
        return State::where('name', $name)->firstOrFail();
    }

    private function getCountry($name)
    {
        return Country::where('name', $name)->firstOrFail();
    }
}

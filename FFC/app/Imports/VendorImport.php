<?php

namespace App\Imports;

use App\Models\Vendor;
use App\Models\Sale;
use App\Models\Finance;
use App\Models\VendorFinances;
use App\Models\VendorSales;
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

    public function collection(Collection $rows)
    {
        Log::info('updateColumns:' . json_encode($this->updatedColumns));
        foreach ($rows as $row) {
            // If the first column (Vendor Type) is null, its a continuation of salesfinance
            if (!is_null($row['vendor_type'])) {
                // Start new vendor entry
                $this->currentVendor = $this->updateOrCreateVendor($row);
            }
            Log::info('vendor data : ' . $this->currentVendor->id);

            // Process sales and finance data
            if ($this->saleFlag) {
                $this->processSalesData($row);
            } else {
                $this->processSalesData($row, $this->currentVendor);
            }
            if ($this->financeFlag) {
                $this->processFinanceData($row);
            } else {
                $this->processFinanceData($row, $this->currentVendor);
            }
        }
    }

    private function updateOrCreateVendor($row)
    {
        // Check required columns
        if (!in_array('phone', $this->updatedColumns) || !in_array('email', $this->updatedColumns)) {
            throw new \Exception('Phone and Email are required.');
        }

        if (in_array('all', $this->updatedColumns)) {
            Log::info("Find existing vendor based on unique fields (phone and email)");
            $vendor = Vendor::where('phone', $row['phone'])->orWhere('email', $row['email'])->first();

            if ($vendor) {
                Log::info("If vendor exists, delete existing sales and finance data and update vendor data");

                // Delete Sales and Finance Data
                $vendor->sales()->delete();
                $vendor->finance()->delete();

                // Getting Vendor data array
                $vendorData = $this->processVendorData($row);

                Log::info("Update vendor details...");
                $vendor->update($vendorData);
            } else {
                Log::info("Creating vendor...");
                $vendorData = $this->processVendorData($row);
                $vendorData['status'] = 'deactivated';
                Log::info('Saving Vendor datails');
                $vendor = Vendor::create($vendorData);
            }
            return $vendor;
        } else {
            // Only update the specified columns
            foreach ($this->updatedColumns as $column) {
                if (isset($row[$column])) {
                    $vendorData[$column] = $row[$column];
                }
            }
            Log::info('Vendor Updated Column Data : ' . json_encode($vendorData));

            // unlink sales and finance data from vendorData
            unset($vendorData['sales_name'], $vendorData['sales_designation'], $vendorData['sales_phone'], $vendorData['sales_email'], $vendorData['sales_fax']);
            unset($vendorData['finance_name'], $vendorData['finance_designation'], $vendorData['finance_phone'], $vendorData['finance_email'], $vendorData['finance_fax']);
            Log::info('Vendor data after unset : ' . json_encode($vendorData));

            $vendor = Vendor::where('phone', $row['phone'])->orWhere('email', $row['email'])->first();

            if (!empty($vendorData)) {
                $dateOfExpiration = $this->validateDate($row['date_of_expiration']);
                $vendorData['date_of_expiration'] = $dateOfExpiration;
                if ($vendor) {
                    $vendor->update($vendorData);
                } else {
                    $vendorData['status'] = 'deactivated';
                    $vendor = Vendor::create($vendorData);
                }
            }

            // $salesArray = ["sales_name", "sales_designation", "sales_phone", "sales_email", "sales_fax"];
            // Do not delete Sales Data
            // if (in_array($this->updatedColumns, $salesArray)) {
            $this->saleFlag = false;
            // }

            // $financeArray = ["finance_name", "finance_designation", "finance_phone", "finance_email", "finance_fax"];
            // Do not delete Finance Data
            // if (in_array($this->updatedColumns, $financeArray)) {
            $this->financeFlag = false;
            // }

            return $vendor;
        }
    }

    private function processVendorData($row)
    {
        $dateOfExpiration = $this->validateDate($row['date_of_expiration']);

        $vendorData = [
            'vendor_type' => $row['vendor_type'],
            'company_name' => $row['company_name'],
            'address' => $row['address'],
            'city' => $row['city'],
            'state' => $row['state'],
            'country' => $row['country'],
            'zip_code' => $row['zip_code'],
            'company_tax_id' => $row['company_tax_id'],
            'mc_number' => $row['mc_number'],
            'scac_number' => $row['scac_number'],
            'us_dot_number' => $row['us_dot_number'],
            'date_of_expiration' => $dateOfExpiration,
            'payment_term' => $row['payment_term'],
            'bank_name' => $row['bank_name'],
            'bank_account_number' => $row['bank_account_number'],
            'bank_routing' => $row['bank_routing'],
            'bank_address' => $row['bank_address'],
            'remarks' => $row['remarks'],
            'contact_name' => $row['contact_name'],
            'phone' => $row['phone'],
            'email' => $row['email'],
        ];
        return $vendorData;
    }

    private function processSalesData($row, Vendor $vendor = null)
    {
        if ($vendor == null) {
            Log::info('Saving Sale data for vendor id = ' . $this->currentVendor->id);
            if (!is_null($row['sales_name'])) {
                VendorSales::create([
                    'vendors_id' => $this->currentVendor->id,
                    'sales_name' => $row['sales_name'],
                    'sales_designation' => $row['sales_designation'],
                    'sales_phone' => $row['sales_phone'],
                    'sales_email' => $row['sales_email'],
                    'sales_fax' => $row['sales_fax'],
                ]);
            }
        } else {
            $salesData = [];

            if (in_array('sales_name', $this->updatedColumns)) {
                $salesData['sales_name'] = $row['sales_name'];
            }

            if (in_array('sales_designation', $this->updatedColumns)) {
                $salesData['sales_designation'] = $row['sales_designation'];
            }

            if (in_array('sales_phone', $this->updatedColumns)) {
                $salesData['sales_phone'] = $row['sales_phone'];
            }

            if (in_array('sales_email', $this->updatedColumns)) {
                $salesData['sales_email'] = $row['sales_email'];
            }

            if (in_array('sales_fax', $this->updatedColumns)) {
                $salesData['sales_fax'] = $row['sales_fax'];
            }

            if (!empty($salesData)) {
                $vendorData = Vendor::where('phone', $vendor->phone)->orWhere('email', $vendor->email)->first();
                Log::info("Update sales columns for vendor = " . $vendorData->id);
                if ($vendorData) {
                    $vendor->sales()->update($salesData);
                } else {
                    VendorSales::create($salesData);
                }
            }
        }
    }

    private function processFinanceData($row, Vendor $vendor = null)
    {
        if ($vendor == null) {
            Log::info('Saving Finance data...' . $this->currentVendor->id);
            if (!is_null($row['finance_name'])) {
                VendorFinances::create([
                    'vendors_id' => $this->currentVendor->id,
                    'finance_name' => $row['finance_name'],
                    'finance_designation' => $row['finance_designation'],
                    'finance_phone' => $row['finance_phone'],
                    'finance_email' => $row['finance_email'],
                    'finance_fax' => $row['finance_fax'],
                ]);
            }
        } else {
            $financeData = [];

            if (in_array('finance_name', $this->updatedColumns)) {
                $financeData['finance_name'] = $row['finance_name'];
            }

            if (in_array('finance_designation', $this->updatedColumns)) {
                $financeData['finance_designation'] = $row['finance_designation'];
            }

            if (in_array('finance_phone', $this->updatedColumns)) {
                $financeData['finance_phone'] = $row['finance_phone'];
            }

            if (in_array('finance_email', $this->updatedColumns)) {
                $financeData['finance_email'] = $row['finance_email'];
            }

            if (in_array('finance_fax', $this->updatedColumns)) {
                $financeData['finance_fax'] = $row['finance_fax'];
            }

            if (!empty($financeData)) {
                $vendorData = Vendor::where('phone', $vendor->phone)->orWhere('email', $vendor->email)->first();
                Log::info("Update finance columns for vendor = " . $vendorData->id);
                if ($vendorData) {
                    $vendor->sales()->update($financeData);
                } else {
                    VendorFinances::create($financeData);
                }
            }
        }
    }
    private function validateDate($date)
    {
        if (is_numeric($date)) {
            // Convert Excel date to PHP date
            $timestamp = Date::excelToDateTimeObject($date);
            return $timestamp->format('Y-m-d'); // Return in desired format
        }

        // If it's not numeric, attempt to create a Carbon instance
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error("Invalid date format for date: $date", ['error' => $e->getMessage()]);
            return null; // Or set a default date if necessary
        }
    }
}

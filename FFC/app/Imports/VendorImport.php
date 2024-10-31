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
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class VendorImport implements OnEachRow, WithStartRow
{
    private $updatedColumns;
    protected $stateModel;
    protected $countryModel;
    protected $vendorType;

    // Constructor to accept the columns to be updated
    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
        $this->stateModel = new State();
        $this->countryModel = new Country();
        $this->vendorType = VendorType::pluck('id', 'type');
    }

    public function startRow(): int
    {
        return 3; // Start from the third row (where the actual headers are located)
    }

    public function chunkSize(): int
    {
        return 100; // Process 100 rows at a time for efficiency
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $lineNumber = $row->getIndex();
        if (empty($rowData[1])) {
            Log::info("Skipping line number " . $lineNumber . " because Company Name is empty.");
            return; // Skip the current iteration
        }
        Log::info("Checking " . $rowData[1] . "  is exist or not");
        $vendor = Vendor::where('company_name', $rowData[1])->first();
        if (empty($vendor)) {
            $state = $this->stateModel->getState($row[4], $lineNumber);
            $country = $this->countryModel->getCountry($row[5], $lineNumber);
            $bankCountry = $this->countryModel->getCountry($row[24], $lineNumber);
            $vendorTypeId = $this->vendorType[$rowData[0]] ?? null;
            if (!$vendorTypeId) {
                Log::error("Error on row {$lineNumber}: Vendor Type '{$rowData[0]}' not found.");
                abort(400, "Vendor Type : '{$rowData[0]}' not found at line No. " . $lineNumber);
            }
            $vendorData = [
                'company_name' => $rowData[1] ?? null, // 'Company Name'
                'address' => $rowData[2] ?? null, // 'Address'
                'city' => $rowData[3] ?? null, // 'City'
                'state_id' => $state->id, // 'State'
                'country_id' => $country->id, // 'Country'
                'zip_code' => $rowData[6] ?? null, // 'Zip Code'
                'company_tax_id' => $rowData[17] ?? null, // 'Company Tax ID'
                'mc_number' => $rowData[18] ?? null, // 'MC Number'
                'scac_number' => $rowData[19] ?? null, // 'SCAC code'
                'us_dot_number' => $rowData[20] ?? null, // 'US DOT Number'
                'payment_term' => $rowData[21] ?? null, // 'Payment Terms'
                'remarks' => $rowData[22] ?? null, // 'Remarks'
                'bank_name' => $rowData[23] ?? null, // 'Bank Name'
                'bank_country_id' => $bankCountry->id, // 'Bank Country'
                'bank_account_number' => $rowData[25] ?? null, // 'Bank Account'
                'bank_routing' => $rowData[26] ?? null, // 'Bank Routing'
                'bank_swift_code' => $rowData[27] ?? null, // 'Swift Code'
                'bank_iban_number' => $rowData[28] ?? null, // 'IBAN No.'
                'bank_ifsc_code' => $rowData[29] ?? null, // 'IFSC Code'
                'bank_address' => $rowData[30] ?? null, // 'Bank Address'
                'status' => 'inactive',
            ];
            Log::info($vendorData);
            $vendor = Vendor::create($vendorData);

            // Save vendor type id
            $vendor->vendorTypes()->attach($vendorTypeId);

            Log::info("Saving sales data");
            $vendorSaleData = [
                'vendors_id' => $vendor->id,
                'sales_name' => $rowData[7],
                'sales_designation' => $rowData[8],
                'sales_phone' => $rowData[9],
                'sales_email' => $rowData[10],
                'sales_fax' => $rowData[11],
            ];
            Log::info($vendorSaleData);
            VendorSales::create($vendorSaleData);

            Log::info("Saving finance data");
            $vendorFinanceData = [
                'vendors_id' => $vendor->id,
                'finance_name' => $rowData[12],
                'finance_designation' => $rowData[13],
                'finance_phone' => $rowData[14],
                'finance_email' => $rowData[15],
                'finance_fax' => $rowData[16],
            ];
            Log::info($vendorFinanceData);
            VendorFinances::create($vendorFinanceData);

            Log::info("Vendor creation for :" . $vendor->company_name . " completed.");
        } else {
            Log::info($vendor->company_name . ' is exit at line no. ' . $lineNumber . ' Skipping to next iteration.');
        }
    }

    private function getVendorType($type)
    {
        return VendorType::where('type', $type)->firstOrFail();
    }
}

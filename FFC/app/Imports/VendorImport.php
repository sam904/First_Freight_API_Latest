<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Vendor;
use App\Models\State;
use App\Models\VendorFinances;
use App\Models\VendorSales;
use App\Models\VendorType;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class VendorImport implements OnEachRow, WithStartRow
{
    private $updatedColumns;
    protected $state;
    protected $country;
    protected $vendorType;
    protected $errors = [];
    protected $badData = [];
    protected $validRows = [];
    protected $existingRows = [];

    // Constructor to accept the columns to be updated
    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
        $this->vendorType = VendorType::pluck('id', 'type');
        $this->state = State::pluck('id', 'name');
        $this->country = Country::pluck('id', 'name');
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
        $invalidFields = [];
        $missingFields = [];

        if (!is_string($rowData[0]) || is_numeric($rowData[0])) {
            $invalidFields[] = "Vendor Type (should be string)";
            $missingFields[] = "Vendor Type";
        } else {
            $vendorTypeId = $this->vendorType[$rowData[0]] ?? null;
            if (!$vendorTypeId) {
                $invalidFields[] = "Vendor Type (not exist)";
                $missingFields[] = "Vendor Type";
            }
        }
        if (!is_string($rowData[1])) {
            $invalidFields[] = "Company Name (should be string)";
            $missingFields[] = "Company Name";
        }
        if (!is_string($rowData[2])) {
            $invalidFields[] = "Address (should be string)";
            $missingFields[] = "Address";
        }
        if (!is_string($rowData[3]) || is_numeric($rowData[3])) {
            $invalidFields[] = "city (should be string)";
            $missingFields[] = "city";
        }
        if (!is_string($rowData[4]) || is_numeric($rowData[4])) {
            $invalidFields[] = "state (should be string)";
            $missingFields[] = "state";
        } else {
            $state = $this->state[$rowData[4]] ?? null;
            if (!$state) {
                $invalidFields[] = "state (not exist)";
                $missingFields[] = "state";
            }
        }
        if (!is_string($rowData[5]) || is_numeric($rowData[5])) {
            $invalidFields[] = "country (should be string)";
            $missingFields[] = "country";
        } else {
            $country = $this->country[$rowData[5]] ?? null;
            if (!$country) {
                $invalidFields[] = "country (not exist)";
                $missingFields[] = "country";
            }
        }
        // if (!isset($rowData[6]) || !preg_match('/^\d{6}(-\d{4})?$/', $rowData[6])) {
        if (!isset($rowData[6]) || !is_numeric($rowData[6])) {
            $invalidFields[] = "Zip Code (should be number)";
            $missingFields[] = "Zip Code";
        }
        if (!is_string($rowData[7])) {
            $invalidFields[] = "Sales Name (should be string)";
            $missingFields[] = "Sales Name";
        }
        if (!is_string($rowData[8])) {
            $invalidFields[] = "Sales Designation (should be string)";
            $missingFields[] = "Sales Designation";
        }
        if (!isset($rowData[9]) || !preg_match('/^\d{10,12}$/', $rowData[9])) {
            $invalidFields[] = "Sales Phone (should be a 10 to 12-digit number)";
            $missingFields[] = "Sales Phone";
        }
        if (!isset($rowData[10]) || !filter_var($rowData[15], FILTER_VALIDATE_EMAIL)) {
            $invalidFields[] = "Sales Email (should be email)";
            $missingFields[] = "Sales Email";
        }
        // if (!is_string($rowData[11])) {
        //     $invalidFields[] = "Sales Fax (should be email)";
        //     $missingFields[] = "Sales Fax";
        // }
        if (!is_string($rowData[12])) {
            $invalidFields[] = "Finance Name (should be string)";
            $missingFields[] = "Finance Name";
        }
        if (!is_string($rowData[13])) {
            $invalidFields[] = "Finance Designation (should be string)";
            $missingFields[] = "Finance Designation";
        }
        if (!isset($rowData[14]) || !preg_match('/^\d{10,12}$/', $rowData[14])) {
            $invalidFields[] = "Finance Phone (should be a 10 to 12-digit number)";
            $missingFields[] = "Finance Phone";
        }
        if (!isset($rowData[15]) || !filter_var($rowData[15], FILTER_VALIDATE_EMAIL)) {
            $invalidFields[] = "Finance Email (should be email)";
            $missingFields[] = "Finance Email";
        }
        // if (!is_string($rowData[16])) {
        //     $invalidFields[] = "Finance Fax (should be email)";
        //     $missingFields[] = "Finance Fax";
        // }
        if (!is_string($rowData[17])) {
            $invalidFields[] = "Company Tax ID (should be string)";
            $missingFields[] = "Company Tax ID";
        }
        if (!is_string($rowData[18])) {
            $invalidFields[] = "MC Number (should be string)";
            $missingFields[] = "MC Number";
        }
        if (!is_string($rowData[19])) {
            $invalidFields[] = "SCAC code (should be string)";
            $missingFields[] = "SCAC code";
        }
        if (!is_string($rowData[20])) {
            $invalidFields[] = "US DOT Number (should be string)";
            $missingFields[] = "US DOT Number";
        }
        if (!is_string($rowData[21])) {
            $invalidFields[] = "Payment Terms (should be string)";
            $missingFields[] = "Payment Terms";
        }
        if (!is_string($rowData[22])) {
            $invalidFields[] = "Remarks (should be string)";
            $missingFields[] = "Remarks";
        }
        if (!is_string($rowData[23])) {
            $invalidFields[] = "Bank Name (should be string)";
            $missingFields[] = "Bank Name";
        }
        if (!is_string($rowData[24]) || is_numeric($rowData[24])) {
            $invalidFields[] = "Bank country (should be string)";
            $missingFields[] = "Bank country";
        } else {
            $bankCountry = $this->country[$rowData[24]] ?? null;
            if (!$bankCountry) {
                $invalidFields[] = "country (not exist)";
                $missingFields[] = "country";
            }
        }
        if (!isset($rowData[25]) || !is_numeric($rowData[25])) {
            $invalidFields[] = "Bank Account (should be number)";
            $missingFields[] = "Bank Account";
        }
        if (!is_string($rowData[26])) {
            $invalidFields[] = "Bank Routing (should be string)";
            $missingFields[] = "Bank Routing";
        }
        if (!is_string($rowData[27])) {
            $invalidFields[] = "Bank Swift Code (should be string)";
            $missingFields[] = "Bank Swift Code";
        }
        if (!is_string($rowData[28])) {
            $invalidFields[] = "Bank IBAN No. (should be string)";
            $missingFields[] = "Bank IBAN No.";
        }
        if (!is_string($rowData[29])) {
            $invalidFields[] = "Bank IFSC Code (should be string)";
            $missingFields[] = "Bank IFSC Code";
        }
        if (!is_string($rowData[30])) {
            $invalidFields[] = "Bank Address (should be string)";
            $missingFields[] = "Bank Address";
        }

        $excelData = [
            'Vendor Type' => $rowData[0],
            'Company Name' => $rowData[1],
            'Address' => $rowData[2],
            'City' => $rowData[3],
            'State' => $rowData[4],
            'Country' => $rowData[5],
            'Zip Code' => $rowData[6],
            'Sales Name' => $rowData[7],
            'Sales Designation' => $rowData[8],
            'Sales Phone' => $rowData[9],
            'Sales Email' => $rowData[10],
            'Sales Fax' => $rowData[11],
            'Finance Name' => $rowData[12],
            'Finance Designation' => $rowData[13],
            'Finance Phone' => $rowData[14],
            'Finance Email' => $rowData[15],
            'Finance Fax' => $rowData[16],
            'Company Tax Id' => $rowData[17],
            'MC Number' => $rowData[18],
            'SCAC Code' => $rowData[19],
            'US DOT Number' => $rowData[20],
            'Payment Terms' => $rowData[21],
            'Remarks' => $rowData[22],
            'Bank Name' => $rowData[23],
            'Bank Country' => $rowData[24],
            'Bank Account Number' => $rowData[25],
            'Bank Routing' => $rowData[26],
            'Bank Swift Code' => $rowData[27],
            'Bank IBAN No.' => $rowData[28],
            'Bank IFSC Code' => $rowData[29],
            'Bank Address' => $rowData[30]
        ];

        // If validation fails, log error and save row data
        if (!empty($invalidFields)) {
            $this->errors[] = [
                'row' => $lineNumber,
                'missingFields' => $missingFields,
                'invalidFields' => $invalidFields
            ];
            $this->badData[] = array_merge($excelData, ['row' => $lineNumber]);
            return;
        }

        $vendor = Vendor::where('company_name', $rowData[1])->first();
        if ($vendor) {
            $this->existingRows[] = $lineNumber;
            return;
        }

        // Collect valid row for later processing
        $this->validRows[] = array_merge($rowData, ['row' => $lineNumber]);

        // // if (empty($rowData[1])) {
        // //     Log::info("Skipping line number " . $lineNumber . " because Company Name is empty.");
        // //     return; // Skip the current iteration
        // // }
        // // Log::info("Checking " . $rowData[1] . "  is exist or not");
        // // $vendor = Vendor::where('company_name', $rowData[1])->first();
        // // if (empty($vendor)) {
        // //     $state = $this->stateModel->getState($row[4], $lineNumber);
        // //     $country = $this->countryModel->getCountry($row[5], $lineNumber);
        // //     $bankCountry = $this->countryModel->getCountry($row[24], $lineNumber);
        // //     $vendorTypeId = $this->vendorType[$rowData[0]] ?? null;
        // //     if (!$vendorTypeId) {
        // //         Log::error("Error on row {$lineNumber}: Vendor Type '{$rowData[0]}' not found.");
        // //         abort(400, "Vendor Type : '{$rowData[0]}' not found at line No. " . $lineNumber);
        // //     }
        // //     $vendorData = [
        // //         'company_name' => $rowData[1] ?? null, // 'Company Name'
        // //         'address' => $rowData[2] ?? null, // 'Address'
        // //         'city' => $rowData[3] ?? null, // 'City'
        // //         'state_id' => $state->id, // 'State'
        // //         'country_id' => $country->id, // 'Country'
        // //         'zip_code' => $rowData[6] ?? null, // 'Zip Code'
        // //         'company_tax_id' => $rowData[17] ?? null, // 'Company Tax ID'
        // //         'mc_number' => $rowData[18] ?? null, // 'MC Number'
        // //         'scac_number' => $rowData[19] ?? null, // 'SCAC code'
        // //         'us_dot_number' => $rowData[20] ?? null, // 'US DOT Number'
        // //         'payment_term' => $rowData[21] ?? null, // 'Payment Terms'
        // //         'remarks' => $rowData[22] ?? null, // 'Remarks'
        // //         'bank_name' => $rowData[23] ?? null, // 'Bank Name'
        // //         'bank_country_id' => $bankCountry->id, // 'Bank Country'
        // //         'bank_account_number' => $rowData[25] ?? null, // 'Bank Account'
        // //         'bank_routing' => $rowData[26] ?? null, // 'Bank Routing'
        // //         'bank_swift_code' => $rowData[27] ?? null, // 'Swift Code'
        // //         'bank_iban_number' => $rowData[28] ?? null, // 'IBAN No.'
        // //         'bank_ifsc_code' => $rowData[29] ?? null, // 'IFSC Code'
        // //         'bank_address' => $rowData[30] ?? null, // 'Bank Address'
        // //         'status' => 'inactive',
        // //     ];
        // //     // Log::info($vendorData);
        // //     $vendor = Vendor::create($vendorData);

        // //     // Save vendor type id
        // //     $vendor->vendorTypes()->attach($vendorTypeId);

        // //     Log::info("Saving sales data");
        // //     $vendorSaleData = [
        // //         'vendors_id' => $vendor->id,
        // //         'sales_name' => $rowData[7],
        // //         'sales_designation' => $rowData[8],
        // //         'sales_phone' => $rowData[9],
        // //         'sales_email' => $rowData[10],
        // //         'sales_fax' => $rowData[11],
        // //     ];
        // //     // Log::info($vendorSaleData);
        // //     VendorSales::create($vendorSaleData);

        // //     Log::info("Saving finance data");
        // //     $vendorFinanceData = [
        // //         'vendors_id' => $vendor->id,
        // //         'finance_name' => $rowData[12],
        // //         'finance_designation' => $rowData[13],
        // //         'finance_phone' => $rowData[14],
        // //         'finance_email' => $rowData[15],
        // //         'finance_fax' => $rowData[16],
        // //     ];
        // //     // Log::info($vendorFinanceData);
        // //     VendorFinances::create($vendorFinanceData);

        //     Log::info("Vendor creation for :" . $vendor->company_name . " completed.");
        // } else {
        //     Log::info($vendor->company_name . ' is exit at line no. ' . $lineNumber . ' Skipping to next iteration.');
        // }
    }

    public function afterImport()
    {
        Log::info("Start Uploading Records...");
        // If no errors, proceed to save data
        if (empty($this->errors)) {
            foreach ($this->validRows as $rowData) {
                Log::info("Process valid row data", $rowData);

                $stateId = $this->state[$rowData[4]] ?? null;
                $countryId = $this->country[$rowData[5]] ?? null;
                $bankCountryId = $this->country[$rowData[24]] ?? null;

                $vendorData = [
                    'company_name' => $rowData[1] ?? null, // 'Company Name'
                    'address' => $rowData[2] ?? null, // 'Address'
                    'city' => $rowData[3] ?? null, // 'City'
                    'state_id' => $stateId, // 'State'
                    'country_id' => $countryId, // 'Country'
                    'zip_code' => $rowData[6] ?? null, // 'Zip Code'
                    'company_tax_id' => $rowData[17] ?? null, // 'Company Tax ID'
                    'mc_number' => $rowData[18] ?? null, // 'MC Number'
                    'scac_number' => $rowData[19] ?? null, // 'SCAC code'
                    'us_dot_number' => $rowData[20] ?? null, // 'US DOT Number'
                    'payment_term' => $rowData[21] ?? null, // 'Payment Terms'
                    'remarks' => $rowData[22] ?? null, // 'Remarks'
                    'bank_name' => $rowData[23] ?? null, // 'Bank Name'
                    'bank_country_id' => $bankCountryId, // 'Bank Country'
                    'bank_account_number' => $rowData[25] ?? null, // 'Bank Account'
                    'bank_routing' => $rowData[26] ?? null, // 'Bank Routing'
                    'bank_swift_code' => $rowData[27] ?? null, // 'Swift Code'
                    'bank_iban_number' => $rowData[28] ?? null, // 'IBAN No.'
                    'bank_ifsc_code' => $rowData[29] ?? null, // 'IFSC Code'
                    'bank_address' => $rowData[30] ?? null, // 'Bank Address'
                    'status' => 'inactive',
                ];
                Log::info("vendorData => ", $vendorData);
                $vendor = Vendor::create($vendorData);

                // Save vendor type id
                $vendorTypeId = $this->vendorType[$rowData[0]] ?? null;
                $vendor->vendorTypes()->attach($vendorTypeId);

                Log::info("Saving sales data for vendor =>" . $vendor->id);
                $vendorSaleData = [
                    'vendors_id' => $vendor->id,
                    'sales_name' => $rowData[7],
                    'sales_designation' => $rowData[8],
                    'sales_phone' => $rowData[9],
                    'sales_email' => $rowData[10],
                    'sales_fax' => $rowData[11],
                ];
                VendorSales::create($vendorSaleData);

                Log::info("Saving finance data for vendor =>" . $vendor->id);
                $vendorFinanceData = [
                    'vendors_id' => $vendor->id,
                    'finance_name' => $rowData[12],
                    'finance_designation' => $rowData[13],
                    'finance_phone' => $rowData[14],
                    'finance_email' => $rowData[15],
                    'finance_fax' => $rowData[16],
                ];
                VendorFinances::create($vendorFinanceData);

                Log::info("Vendor creation for :" . $vendor->company_name . " is completed.");
            }
        }
    }

    public function getErrorsResponse()
    {
        if (!empty($this->errors)) {
            return [
                "status" => 400,
                "message" => "There are errors in the Excel file",
                "errors" => $this->errors,
                "badData" => $this->badData
            ];
        }
        return null;
    }

    public function getValidRowCount()
    {
        return count($this->validRows);
    }

    public function getExistingRowCount()
    {
        return [count($this->existingRows), $this->existingRows];
    }
}

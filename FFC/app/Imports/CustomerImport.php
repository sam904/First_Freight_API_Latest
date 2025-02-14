<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerContactDetails;
use App\Models\Customer\CustomerDeliveryAddress;
use App\Models\Customer\CustomerFinanceDetails;
use App\Models\State;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CustomerImport implements OnEachRow, WithStartRow
{

    private $updatedColumns;
    protected $state;
    protected $country;
    protected $errors = [];
    protected $badData = [];
    protected $validRows = [];
    protected $existingRows = [];

    // Constructor to accept the columns to be updated
    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
        $this->state = State::pluck('id', 'name');
        $this->country = Country::pluck('id', 'name');
    }

    public function startRow(): int
    {
        return 3; // Start from the second row (where the actual headers are located)
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

        if (!is_string($rowData[0])) {
            $invalidFields[] = "Company Name (should be string)";
            $missingFields[] = "Company Name";
        }
        if (!is_string($rowData[1]) || is_numeric($rowData[1])) {
            $invalidFields[] = "Type of Customer (should be string)";
            $missingFields[] = "Type of Customer";
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
            $invalidFields[] = "Contact Name (should be string)";
            $missingFields[] = "Contact Name";
        }
        if (!is_string($rowData[8])) {
            $invalidFields[] = "Contact Designation (should be string)";
            $missingFields[] = "Contact Designation";
        }
        if (!isset($rowData[9]) || !preg_match('/^\d{10,12}$/', $rowData[9])) {
            $invalidFields[] = "Contact Phone (should be a 10 to 12-digit number)";
            $missingFields[] = "Contact Phone";
        }
        if (!isset($rowData[10]) || !filter_var($rowData[15], FILTER_VALIDATE_EMAIL)) {
            $invalidFields[] = "Contact Email (should be email)";
            $missingFields[] = "Contact Email";
        }
        // if (!is_string($rowData[11])) {
        //     $invalidFields[] = "Contact Fax (should be email)";
        //     $missingFields[] = "Contact Fax";
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
            $invalidFields[] = "Payment Terms (should be string)";
            $missingFields[] = "Payment Terms";
        }
        if (!isset($rowData[19]) || !is_numeric($rowData[19])) {
            $invalidFields[] = "Credit Limit (should be string)";
            $missingFields[] = "Credit Limit";
        }
        if (!is_string($rowData[20])) {
            $invalidFields[] = "Delivery Name (should be string)";
            $missingFields[] = "Delivery Name";
        }
        if (!is_string($rowData[21])) {
            $invalidFields[] = "Delivery Address (should be string)";
            $missingFields[] = "Delivery Address";
        }
        if (!is_string($rowData[22]) || is_numeric($rowData[22])) {
            $invalidFields[] = "Delivery city (should be string)";
            $missingFields[] = "Delivery city";
        }
        if (!is_string($rowData[23]) || is_numeric($rowData[23])) {
            $invalidFields[] = "Delivery state (should be string)";
            $missingFields[] = "Delivery state";
        } else {
            $deliveryState = $this->state[$rowData[23]] ?? null;
            if (!$deliveryState) {
                $invalidFields[] = "Delivery state (not exist)";
                $missingFields[] = "Delivery state";
            }
        }
        if (!is_string($rowData[24]) || is_numeric($rowData[24])) {
            $invalidFields[] = "Delivery country (should be string)";
            $missingFields[] = "Delivery country";
        } else {
            $deliveryCountry = $this->country[$rowData[24]] ?? null;
            if (!$deliveryCountry) {
                $invalidFields[] = "Delivery country (not exist)";
                $missingFields[] = "Delivery country";
            }
        }
        if (!isset($rowData[25]) || !is_numeric($rowData[25])) {
            $invalidFields[] = "Delivery Zip Code (should be number)";
            $missingFields[] = "Delivery Zip Code";
        }

        $excelData = [
            'Company Name' => $rowData[0],
            'Type of Customer' => $rowData[1],
            'Address' => $rowData[2],
            'City' => $rowData[3],
            'State' => $rowData[4],
            'Country' => $rowData[5],
            'Zip Code' => $rowData[6],
            'Contact Name' => $rowData[7],
            'Contact Designation' => $rowData[8],
            'Contact Phone' => $rowData[9],
            'Contact Email' => $rowData[10],
            'Contact Fax' => $rowData[11],
            'Finance Name' => $rowData[12],
            'Finance Designation' => $rowData[13],
            'Finance Phone' => $rowData[14],
            'Finance Email' => $rowData[15],
            'Finance Fax' => $rowData[16],
            'Company Tax Id' => $rowData[17],
            'Payment Terms' => $rowData[18],
            'Credit Limit' => $rowData[19],
            'Delivery Name' => $rowData[20],
            'Delivery Address' => $rowData[21],
            'Delivery City' => $rowData[22],
            'Delivery State' => $rowData[23],
            'Delivery Country' => $rowData[24],
            'Delivery Zip Code' => $rowData[25],
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

        $customer = Customer::where('company_name', $rowData[0])->first();
        if ($customer) {
            $this->existingRows[] = $lineNumber;
            return;
        }

        // Collect valid row for later processing
        $this->validRows[] = array_merge($rowData, ['row' => $lineNumber]);
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
                $customerData = [
                    'company_name' => $rowData[0] ?? null, // 'Company Name'
                    'customer_type' => $rowData[1] ?? null, // 'Type of Customer'
                    'address' => $rowData[2] ?? null, // 'Address'
                    'city' => $rowData[3] ?? null, // 'City'
                    'state_id' => $stateId, // 'State'
                    'country_id' => $countryId, // 'Country'
                    'zip_code' => $rowData[6] ?? null, // 'Zip Code'
                    'company_tax_id' => $rowData[17] ?? null, // 'Company Tax ID'
                    'payment_terms' => $rowData[18] ?? null, // 'Payment Terms'
                    'credit_limit' => $rowData[19] ?? null, // 'Credit Limit'
                ];
                $customer = Customer::create($customerData);
                Log::info("Customer created successfully " . $customer->id);

                $contactData = [
                    'customer_id' => $customer->id,
                    'contact_name' => $rowData[7] ?? null, // 'Name'
                    'contact_designation' => $rowData[8] ?? null, // 'Designation'
                    'contact_phone' => $rowData[9] ?? null, // 'Phone'
                    'contact_email' => $rowData[10] ?? null, // 'Email'
                    'contact_fax' => $rowData[11] ?? null, // 'Fax'
                ];
                CustomerContactDetails::create($contactData);
                Log::info("Customer contact details created successfully");

                $financeData = [
                    'customer_id' => $customer->id,
                    'finance_name' => $rowData[12], // 'Name'
                    'finance_designation' => $rowData[13], // 'Designation'
                    'finance_phone' => $rowData[14], // 'Phone'
                    'finance_email' => $rowData[15], // 'Email'
                    'finance_fax' => $rowData[16], // 'Fax'
                ];
                CustomerFinanceDetails::create($financeData);
                Log::info("Customer Finance details created successfully");

                $deliveryStateId = $this->state[$rowData[23]] ?? null;
                $deliveryCountryId = $this->country[$rowData[24]] ?? null;

                $deliveryData = [
                    'customer_id' => $customer->id,
                    'delivery_name' => $rowData[20], // 'Delivery Name'
                    'delivery_address' => $rowData[21], // 'Delivery Address'
                    'delivery_city' => $rowData[22], // 'Delivery City'
                    'delivery_state' => $deliveryStateId, // 'Delivery State'
                    'delivery_country' => $deliveryCountryId, // 'Delivery Country'
                    'delivery_zip_code' => $rowData[25], // 'Delivery Zip Code'
                ];
                CustomerDeliveryAddress::create($deliveryData);

                Log::info("Customer Delivery details created successfully");

                Log::info("customer is created for this company" . $customer->company_name);
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

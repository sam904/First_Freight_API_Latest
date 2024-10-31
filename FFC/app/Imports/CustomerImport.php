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
    protected $stateModel;
    protected $countryModel;

    // Constructor to accept the columns to be updated
    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
        $this->stateModel = new State();
        $this->countryModel = new Country();
    }

    public function startRow(): int
    {
        return 3; // Start from the second row (where the actual headers are located)
    }

    // public function chunkSize(): int
    // {
    //     return 100; // Process 100 rows at a time for efficiency
    // }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $lineNumber = $row->getIndex();
        if (empty($rowData[0])) {
            Log::info("Skipping line number " . $lineNumber . " because Company Name is empty.");
            return; // Skip the current iteration
        }

        Log::info("Checking Comapny name is exist or not => " . $rowData[0]);
        $customer = Customer::where('company_name', $rowData[0])->first();
        if (empty($customer)) {
            $state = $this->stateModel->getState($row[4], $lineNumber);
            $country = $this->countryModel->getCountry($row[5], $lineNumber);

            $customerData = [
                'company_name' => $rowData[0] ?? null, // 'Company Name'
                'customer_type' => $rowData[1] ?? null, // 'Type of Customer'
                'address' => $rowData[2] ?? null, // 'Address'
                'city' => $rowData[3] ?? null, // 'City'
                'state_id' => $state ? $state->id : null, // 'State'
                'country_id' => $country ? $country->id : null, // 'Country'
                'zip_code' => $rowData[6] ?? null, // 'Zip Code'
                'company_tax_id' => $rowData[17] ?? null, // 'Company Tax ID'
                'payment_terms' => $rowData[18] ?? null, // 'Payment Terms'
                'credit_limit' => $rowData[19] ?? null, // 'Credit Limit'
            ];
            $customer = Customer::create($customerData);
            Log::info("Customer created successfully " . $customer->id);

            $contactData = [
                'customer_id' => $customer->id,
                'contact_name' => $row[7] ?? null, // 'Name'
                'contact_designation' => $row[8] ?? null, // 'Designation'
                'contact_phone' => $row[9] ?? null, // 'Phone'
                'contact_email' => $row[10] ?? null, // 'Email'
                'contact_fax' => $row[11] ?? null, // 'Fax'
            ];
            CustomerContactDetails::create($contactData);
            Log::info("Customer contact details created successfully");

            $financeData = [
                'customer_id' => $customer->id,
                'finance_name' => $row[12], // 'Name'
                'finance_designation' => $row[13], // 'Designation'
                'finance_phone' => $row[14], // 'Phone'
                'finance_email' => $row[15], // 'Email'
                'finance_fax' => $row[16], // 'Fax'
            ];
            CustomerFinanceDetails::create($financeData);
            Log::info("Customer Finance details created successfully");

            $deliveryState = $this->stateModel->getState($row[23], $lineNumber);
            $deliveryCountry = $this->countryModel->getCountry($row[24], $lineNumber);

            $deliveryData = [
                'customer_id' => $customer->id,
                'delivery_name' => $row[20], // 'Delivery Name'
                'delivery_address' => $row[21], // 'Delivery Address'
                'delivery_city' => $row[22], // 'Delivery City'
                'delivery_state' => $deliveryState ? $deliveryState->id : null, // 'Delivery State'
                'delivery_country' => $deliveryCountry ? $deliveryCountry->id : null, // 'Delivery Country'
                'delivery_zip_code' => $row[25], // 'Delivery Zip Code'
            ];
            CustomerDeliveryAddress::create($deliveryData);

            Log::info("Customer Delivery details created successfully");

            Log::info("customer is created for this company" . $customer->company_name);
        } else {
            Log::info($customer->company_name . ' is exit at line no. ' . $lineNumber . ' Skipping to next iteration.');
        }
    }
}

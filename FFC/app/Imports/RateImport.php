<?php

namespace App\Imports;

use App\Models\Common\ServiceType;
use App\Models\Destination\Destination;
use App\Models\Port\Port;
use App\Models\Rate\Rate;
use App\Models\Rate\RateCharge;
use App\Models\Vendor;
use App\Traits\DateLookupTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RateImport implements ToCollection
//  OnEachRow, WithStartRow, WithHeadingRow
{
    // Use the trait
    use DateLookupTrait;
    private $updatedColumns;
    protected $serviceType;
    protected $chargeColumns = []; // To store the dynamic charge column names
    protected $originalHeaders = [];
    protected $chargeStartIndex = 9;
    protected $portModel;
    protected $destinationModel;
    protected $vendorModel;
    protected $errors = [];
    protected $badData = [];
    protected $validRows = [];
    protected $existingRows = [];

    /**
     * Summary of __construct
     * @param array $updatedColumns
     * For Rate we have 2 tables 1. rate 2. rate_charges
     * In rate_charges we are storing charges_name and amount
     * Now in excel as header we are getting this charges_name, so we have to fetch Original Header seperately
     * In saveRateCharges we are fetching data after 9th index. From 9th Index actual charges_name started
     */
    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
        $this->serviceType = ServiceType::where('status', 'active')->pluck('id', 'name');
        $this->portModel = new Port();
        $this->destinationModel = new Destination();
        $this->vendorModel = new Vendor();
    }

    /**
     * Summary of collection
     * We used collection because we required charges name as it is,
     * If we used onRow method it will create snake case for all header which will be not useful
     */
    public function collection(Collection $rows)
    {
        // Capture the original headers from the first row
        if ($rows->isNotEmpty()) {
            $this->originalHeaders = $rows->first()->toArray(); // Get headers from the first row
            Log::info("Original Headers Captured: ", $this->originalHeaders);
            // Remove the first row from the collection (header)
            $rows->shift();
        }

        // Extract charge columns starting from index 9
        $this->chargeColumns = array_slice($this->originalHeaders, $this->chargeStartIndex);
        Log::info("Charge Column name : ", $this->chargeColumns);

        // Process remaining rows
        foreach ($rows as $index =>  $row) {
            $rowData = $row->toArray();
            $lineNumber = $index + 2;
            Log::info("Linenumber: " . $lineNumber . " / index =" . $index);
            $invalidFields = [];
            $missingFields = [];

            // Perform validation checks
            if (!is_string($rowData[0]) || is_numeric($rowData[0])) {
                $invalidFields[] = "Port of loading (should be string)";
                $missingFields[] = "Port of loading";
            } else {
                $port = $this->portModel->getPort($rowData[0]);
                if (!$port) {
                    $invalidFields[] = "Port of loading (not exist)";
                    $missingFields[] = "Port of loading";
                }
            }
            if (!is_string($rowData[1]) || is_numeric($rowData[1])) {
                $invalidFields[] = "Port of Discharge (should be string)";
                $missingFields[] = "Port of Discharge";
            } else {
                $destination = $this->destinationModel->getDestination($rowData[1]);
                if (!$destination) {
                    $invalidFields[] = "Port of Discharge (not exist)";
                    $missingFields[] = "Port of Discharge";
                }
            }
            if (!is_string($rowData[2]) || is_numeric($rowData[2])) {
                $invalidFields[] = "Trucker Name (should be string)";
                $missingFields[] = "Trucker Name";
            } else {
                $vendor = $this->vendorModel->getVendor($rowData[2]);
                if (!$destination) {
                    $invalidFields[] = "Trucker Name (not exist)";
                    $missingFields[] = "Trucker Name";
                }
            }
            if (!is_string($rowData[3]) || is_numeric($rowData[3])) {
                $invalidFields[] = "Type of service (should be string)";
                $missingFields[] = "Type of service";
            } else {
                $serviceTypeId = $this->serviceType[$rowData[3]] ?? null;
                if (!$serviceTypeId) {
                    $invalidFields[] = "Type of service (not exist)";
                    $missingFields[] = "Type of service";
                }
            }
            $startDate = $this->validateDate($rowData[4]);
            if (!$startDate) {
                $invalidFields[] = "Date (invalid date format)";
                $missingFields[] = "Date";
            }
            if (!is_int($rowData[5])) {
                $invalidFields[] = "Rate Validity (should be integer)";
                $missingFields[] = "Rate Validity";
            }
            if (!is_int($rowData[6])) {
                $invalidFields[] = "Freight (should be integer)";
                $missingFields[] = "Freight";
            }
            if (!is_int($rowData[7])) {
                $invalidFields[] = "FSC (should be integer)";
                $missingFields[] = "FSC";
            }
            if (!is_int($rowData[8])) {
                $invalidFields[] = "Dray+FSC (should be integer)";
                $missingFields[] = "Dray+FSC";
            }

            $chargeNameArray = [];
            foreach ($this->chargeColumns as $index => $chargeName) {
                // Get the corresponding charge amount
                $chargeAmount = $rowData[$this->chargeStartIndex + $index] ?? null;

                if (!is_int($chargeAmount) && !is_double($chargeAmount)) {
                    $invalidFields[] = "{$chargeName} (should be integer or double)";
                    $missingFields[] =  $chargeName;
                }
                $chargeNameArray[$chargeName] = $chargeAmount;
            }

            $excelData = [
                'Port of loading' => $rowData[0],
                'Port of Discharge' => $rowData[1],
                'Trucker Name' => $rowData[2],
                'Type of service' => $rowData[3],
                'Date' => $rowData[4],
                'Rate Validity' => $rowData[5],
                'Freight' => $rowData[6],
                'FSC' => $rowData[7],
                'Dray+FSC' => $rowData[8],
            ];
            $excelData = array_merge($excelData, $chargeNameArray);

            // If validation fails, log error and save row data
            if (!empty($invalidFields)) {
                $this->errors[] = [
                    'row' => $lineNumber,
                    'missingFields' => $missingFields,
                    'invalidFields' => $invalidFields
                ];
                Log::info("errors ", $this->errors);
                $this->badData[] = array_merge($excelData, ['row' => $lineNumber]);
            }

            if (empty($this->errors)) {
                Log::info("Process of Rate Saving started...");
                // $port = $this->portModel->getPort($rowData[0]);
                // $destination = $this->destinationModel->getDestination($rowData[1]);
                // $vendor = $this->vendorModel->getVendor($rowData[2]);
                // Service Type Id
                // $serviceTypeId = $this->serviceType[$rowData[3]] ?? null;
                // if (!$serviceTypeId) {
                //     Log::error("Error on row {$lineNumber}: State '{$rowData[3]}' not found.");
                //     abort(400, "Service Type : '{$rowData[3]}' not found at line No. " . $lineNumber);
                // }
                // $startDate = $this->validateDate($row[4]);
                $rateData = [
                    'vendor_id' => $vendor->id,
                    'port_id' => $port->id,
                    'destination_id' => $destination->id,
                    'service_type_id' => $serviceTypeId,
                    'start_date' => $startDate,
                    'expiry' => $rowData[5],
                    'freight' => $rowData[6],
                    'fsc' => $rowData[7],
                ];
                $this->validRows[] = array_merge($rateData, ['row' => $lineNumber]);
                $rate = Rate::create($rateData);
                Log::info("Rate: {$rate->id} saved successfully");
                $this->saveRateCharges($rowData, $rate->id);
            }
        }
    }

    protected function saveRateCharges(array $rowData, $rateId = null)
    {
        Log::info("Saving Rate Charges for rate id: " . $rateId);
        // Iterate over the charge columns and save to the RateCharges table
        foreach ($this->chargeColumns as $index => $chargeName) {
            // Get the corresponding charge amount
            $chargeAmount = $rowData[$this->chargeStartIndex + $index] ?? null;
            // Only save if charge amount is not null
            if ($chargeAmount !== null) {
                RateCharge::create([
                    'rate_id' => $rateId,
                    'charge_name' => $chargeName,
                    'amount' => $chargeAmount
                ]);
                Log::info($chargeName . ' with Amount: ' . $chargeAmount);
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
}

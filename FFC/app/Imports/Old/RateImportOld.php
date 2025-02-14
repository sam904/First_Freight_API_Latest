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

class RateImportOld implements ToCollection
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
        Log::info($this->chargeColumns);

        // Process remaining rows
        foreach ($rows as $index =>  $row) {
            $rowData = $row->toArray();
            $lineNumber = $index + 2;

            // Get Vendor Id from Trucker Name
            $vendor = $this->vendorModel->getVendor($rowData[2], $lineNumber);
            $port = $this->portModel->getPort($rowData[0], $lineNumber);
            $destination = $this->destinationModel->getDestination($rowData[1], $lineNumber);
            // Service Type Id
            $serviceTypeId = $this->serviceType[$rowData[3]] ?? null;
            if (!$serviceTypeId) {
                Log::error("Error on row {$lineNumber}: State '{$rowData[3]}' not found.");
                abort(400, "Service Type : '{$rowData[3]}' not found at line No. " . $lineNumber);
            }
            $startDate = $this->validateDate($row[4]);
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
            $rate = Rate::create($rateData);
            $this->saveRateCharges($rowData, $rate->id);
        }
    }

    protected function saveRateCharges(array $rowData, $rateId = null)
    {
        Log::info("saveRateCharges: ", $rowData);
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
}

<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Destination\Destination;
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class DestinationImport implements OnEachRow, WithStartRow, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $updatedColumns;
    protected $state;
    protected $country;
    protected $errors = [];
    protected $badData = [];
    protected $validRows = [];
    protected $existingRows = [];

    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
        $this->state = State::pluck('id', 'name');
        $this->country = Country::pluck('id', 'name');
    }
    public function startRow(): int
    {
        return 2; // Start from the second row (where the actual headers are located)
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $lineNumber = $row->getIndex();
        $invalidFields = [];
        $missingFields = [];

        if (!is_string($rowData['city']) || is_numeric($rowData['city'])) {
            $invalidFields[] = "city (should be string)";
            $missingFields[] = "city";
        }
        if (!is_string($rowData['state']) || is_numeric($rowData['state'])) {
            $invalidFields[] = "state (should be string)";
            $missingFields[] = "state";
        } else {
            $state = $this->state[$rowData['state']] ?? null;
            if (!$state) {
                $invalidFields[] = "state (not exist)";
                $missingFields[] = "state";
            }
        }
        if (!is_string($rowData['country']) || is_numeric($rowData['country'])) {
            $invalidFields[] = "country (should be string)";
            $missingFields[] = "country";
        } else {
            $country = $this->country[$rowData['country']] ?? null;
            if (!$country) {
                $invalidFields[] = "country (not exist)";
                $missingFields[] = "country";
            }
        }
        // If validation fails, log error and save row data
        if (!empty($invalidFields)) {
            $this->errors[] = [
                'row' => $lineNumber,
                'missingFields' => $missingFields,
                'invalidFields' => $invalidFields
            ];
            $this->badData[] = array_merge($rowData, ['row' => $lineNumber]);
            return;
        }
        // Check duplicate Destination
        $destination = Destination::where('name', $rowData['city'])->first();
        if ($destination) {
            Log::info($destination->name . ' already exists at line no. ' . $lineNumber . ' Skipping to next iteration.');
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
                $stateId = $this->state[$rowData['state']] ?? null;
                $countryId = $this->country[$rowData['country']] ?? null;
                Log::info("Creating Destination Record...");
                $destinationData = [
                    'name' => $rowData['city'],
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                ];
                $dest = Destination::create($destinationData);
                Log::info("Destination is created for=>" . $dest->id);
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

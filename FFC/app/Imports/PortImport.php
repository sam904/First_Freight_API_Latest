<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Port\Port;
use App\Models\Port\PortTerminal;
use App\Models\Port\PortType;
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class PortImport implements OnEachRow, WithStartRow, WithHeadingRow
{
    private $updatedColumns;
    protected $portType;
    protected $state;
    protected $country;
    protected $errors = [];
    protected $badData = [];
    protected $validRows = [];
    protected $existingRows = [];

    public function __construct(array $updatedColumns)
    {
        Log::info("Port Constructor");
        $this->updatedColumns = $updatedColumns;
        $this->portType = PortType::pluck('id', 'name');
        $this->state = State::pluck('id', 'name');
        $this->country = Country::pluck('id', 'name');
        // $this->stateModel = new State();
        // $this->countryModel = new Country();
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

        // Validation rules
        if (!is_string($rowData['type']) || is_numeric($rowData['type'])) {
            $invalidFields[] = "type (should be string)";
            $missingFields[] = "type";
        } else {
            $portTypeId = $this->portType[$rowData['type']] ?? null;
            if (!$portTypeId) {
                $invalidFields[] = "type (not exist)";
                $missingFields[] = "type";
            }
        }
        if (empty($rowData['name'])) {
            $invalidFields[] = "name (should not be empty)";
            $missingFields[] = "name";
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
        // Check duplicate Port
        $port = Port::where('name', $rowData['name'])->first();
        if ($port) {
            Log::info($port->name . ' already exists at line no. ' . $lineNumber . ' Skipping to next iteration.');
            // $this->existingRows[] = array_merge($rowData, ['row' => $lineNumber]);
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
                $portTypeId = $this->portType[$rowData['type']] ?? null;
                $stateId = $this->state[$rowData['state']] ?? null;
                $countryId = $this->country[$rowData['country']] ?? null;
                // $state = $this->stateModel->getState($rowData['state'], $rowData['row']);
                // $country = $this->countryModel->getCountry($rowData['country'], $rowData['row']);
                $portData = [
                    'name' => $rowData['name'],
                    'port_type_id' => $portTypeId,
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                ];
                Log::info("Creating Port Record...");
                $port = Port::create($portData);
                Log::info("Inserting port terminals data for port = " . $port->id);
                PortTerminal::create([
                    'name' => $rowData['terminal'],
                    'port_id' => $port->id
                ]);
                Log::info("Port Data is saved for " . $port->name);
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

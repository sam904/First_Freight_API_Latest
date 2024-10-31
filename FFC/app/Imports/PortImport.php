<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Port\Port;
use App\Models\Port\PortTerminal;
use App\Models\Port\PortType;
use App\Models\State;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class PortImport implements OnEachRow, WithStartRow, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $updatedColumns;
    protected $portType;
    protected $stateModel;
    protected $countryModel;

    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
        // Preload portType to avoid repeated queries
        $this->portType = PortType::pluck('id', 'name');
        $this->stateModel = new State();
        $this->countryModel = new Country();
    }

    public function startRow(): int
    {
        return 2; // Start from the second row (where the actual headers are located)
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $lineNumber = $row->getIndex();

        if (empty($rowData['name'])) {
            Log::info("Skipping line number " . $lineNumber . " because Port Name is empty.");
            return;
        }
        $port = Port::where('name', $rowData['name'])->first();
        // Port name should not empty
        // If duplicate the skip the row
        if (empty($port)) {
            $portTypeId = $this->portType[$rowData['type']] ?? null;
            if (!$portTypeId) {
                Log::error("Error on row {$lineNumber}: State '{$rowData['type']}' not found.");
                return;
            }

            $state = $this->stateModel->getState($rowData['state'], $lineNumber);
            $country = $this->countryModel->getCountry($rowData['country'], $lineNumber);

            $portData = [
                'name' => $rowData['name'],
                'port_type_id' => $portTypeId,
                'country_id' => $country->id,
                'state_id' => $state->id,
            ];
            $port = Port::create($portData);

            Log::info("Saving port terminals data");
            PortTerminal::create([
                'name' => $rowData['terminal'],
                'port_id' => $port->id
            ]);

            Log::info("Port Data is saved for " . $port->name);
        } else {
            Log::info($port->name . ' is exit at line no. ' . $lineNumber . ' Skipping to next iteration.');
        }
    }
}

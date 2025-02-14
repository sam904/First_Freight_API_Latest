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

class DestinationImportOld implements OnEachRow, WithStartRow, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private $updatedColumns;
    protected $stateModel;
    protected $countryModel;

    public function __construct(array $updatedColumns)
    {
        $this->updatedColumns = $updatedColumns;
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

        if (empty($rowData['city'])) {
            Log::info("Skipping line number " . $lineNumber . " because Destination City is empty.");
            return;
        }
        $destination = Destination::where('name', $rowData['city'])->first();
        if (empty($destination)) {
            $state = $this->stateModel->getState($rowData['state'], $lineNumber);
            $country = $this->countryModel->getCountry($rowData['country'], $lineNumber);

            $destinationData = [
                'name' => $rowData['city'],
                'country_id' => $country->id,
                'state_id' => $state->id,
            ];
            Destination::create($destinationData);
            Log::info("Destination is created");
        } else {
            Log::info($destination->name . ' is exit at line no. ' . $lineNumber . ' Skipping to next iteration.');
        }
    }
}

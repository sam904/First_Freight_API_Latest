<?php

namespace App\Exports;

use App\Models\Rate;
use App\Models\Rate\RateCharge;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RateExport implements FromArray, WithHeadings, WithCustomStartCell, WithStyles
{
    protected $chargeNameArray = [
        'Chassis',
        'Prepull',
        'Overweight',
        'Triaxle',
        'Tolls',
        'Chassis Split',
        'Stopover/Redelivery',
        'Dray & Transload',
        'Port Congestion',
        'Layover',
        'Dry van',
        'Flatbed',
        'Storage',
        'Detention',
        'Free Time',
        'Chassis Flip'
    ];

    protected $data;

    public function __construct($filteredData)
    {
        $this->data = is_string($filteredData) ? json_decode($filteredData, true) : $filteredData;
    }

    // 1. Define the start cell for the data to position it under headers
    public function startCell(): string
    {
        return 'A1'; // Start data from the third row, leaving space for two headers
    }

    // 2. Define the two header rows
    public function headings(): array
    {
        return [
            array_merge(
                [
                    'Port of loading',
                    'Port of Discharge',
                    'Trucker Name',
                    'Type of service',
                    'Date',
                    'Rate Validity',
                    'Freight',
                    'FSC',
                    'Dray+FSC',
                ],
                $this->chargeNameArray
            )
        ];
    }

    // 3. Define the data as an array (custom data mapping to match columns)
    public function array(): array
    {
        // Log::info($this->data);
        $exportData = [];
        foreach ($this->data as $filterData) {
            $data = (array) $filterData; // Cast to array, because Object of class stdClass could not be converted to string,
            $exportRow = [
                $data['port_name'] ?? '',
                $data['destination_name'] ?? '',
                $data['vendor_name'] ?? '',
                $data['serviceType'] ?? '',
                $data['start_date'] ?? '',
                $data['expiry'] ?? '',
                $data['freight'] ?? '',
                $data['fsc'] ?? '',
                '',
            ];
            $rateCharge = RateCharge::where('rate_id', $data['rate_id'])->get();
            // Log::info($rateCharge);
            $amounts = array_fill_keys($this->chargeNameArray, '');
            foreach ($rateCharge as $charge) {
                Log::info($charge);
                if (in_array($charge['charge_name'], $this->chargeNameArray)) {
                    $amounts[$charge['charge_name']] = $charge['amount'];
                }
            }
            // Log::info($amounts);
            // Append the amounts to the export row in the order specified by $chargeArray
            $exportRow = array_merge($exportRow, array_values($amounts));

            // Add the row to the export data
            $exportData[] = $exportRow;
        }
        return $exportData;
    }
    // public function array(): array
    // {
    //     $exportData = [];
    //     foreach ($this->data as $filterData) {
    //         // Decode JSON to an associative array
    //         $data = json_decode($filterData, true);

    //         $port = $data['port']['name'] ?? '';
    //         $destination = $data['destination']['name'] ?? '';
    //         $vendor = $data['vendor']['company_name'] ?? '';
    //         $serviceType = $data['service_type']['name'] ?? '';

    //         $exportRow = [
    //             $port,
    //             $destination,
    //             $vendor,
    //             $serviceType,
    //             $data['start_date'] ?? '',
    //             $data['expiry'] ?? '',
    //             $data['freight'] ?? '',
    //             $data['fsc'] ?? '',
    //             '',
    //         ];

    //         $rateCharge = RateCharge::where('rate_id', $data['rate_id'])->get();
    //         Log::info($rateCharge);
    //         // Initialize $amounts with default empty values for each charge name in $chargeArray
    //         $amounts = array_fill_keys($this->chargeNameArray, '');

    //         if (isset($data['charges']) && is_array($data['charges'])) {
    //             foreach ($data['charges'] as $charge) {
    //                 if (in_array($charge['charge_name'], $this->chargeNameArray)) {
    //                     $amounts[$charge['charge_name']] = $charge['amount'];
    //                 }
    //             }
    //         }
    //         // Log::info($amounts);
    //         // Append the amounts to the export row in the order specified by $chargeArray
    //         $exportRow = array_merge($exportRow, array_values($amounts));

    //         // Add the row to the export data
    //         $exportData[] = $exportRow;
    //     }
    //     return $exportData; // Return the structured export data
    // }

    // 4. Apply styling for headers, including colors
    public function styles(Worksheet $sheet)
    {
        // Set row height for the header rows
        $sheet->getRowDimension(1)->setRowHeight(24); // Set the height of the first header row

        // Style the first header row (e.g., background color and bold font)
        $sheet->getStyle('A1:Y1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => '000000'], // White font
                'size' => 10
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['argb' => 'ADDFFF'], // Blue background
            ],
            'alignment' => [
                'horizontal' => 'left',
                'vertical' => 'center',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // Border color (black)
                ],
            ],
        ]);

        return [];
    }
}

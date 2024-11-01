<?php

namespace App\Exports;

use App\Models\Port\PortTerminal;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PortExport implements FromArray, WithHeadings, WithCustomStartCell, WithStyles
{
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
            // First Header Row: Group Headers
            [
                'Type',
                'Name',
                'Terminal',
                'State',
                'Country'
            ]
        ];
    }

    // 3. Define the data as an array (custom data mapping to match columns)
    public function array(): array
    {
        Log::info('Data structure before processing:', (array) $this->data);
        $ports = $this->data->items(); // Use the public method to access items

        return $ports ? array_map(function ($port) {
            // access port Terminal 
            $portTerminal = PortTerminal::where('port_id', $port->portId)->first();
            return [
                $port->portType,
                $port->portName,
                $portTerminal ? $portTerminal->name : '',
                $port->state,
                $port->country,
            ];
        }, $ports) : []; // Convert to array if necessary
    }

    // 4. Apply styling for headers, including colors
    public function styles(Worksheet $sheet)
    {
        // Set row height for the header rows
        $sheet->getRowDimension(1)->setRowHeight(24); // Set the height of the first header row

        // Style the first header row (e.g., background color and bold font)
        $sheet->getStyle('A1:E1')->applyFromArray([
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

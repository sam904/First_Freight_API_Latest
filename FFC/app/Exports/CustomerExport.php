<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromArray, WithHeadings, WithCustomStartCell, WithStyles
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
                'Basic Information',
                '',
                '',
                '',
                '',
                '',
                '',
                'Contact Details',
                '',
                '',
                '',
                '',
                'Finance Contact',
                '',
                '',
                '',
                '',
                'Account Details',
                '',
                '',
                'Deliver Address',
                '',
                '',
                '',
                '',
                '',
            ],

            // Second Header Row: Detailed Column Names
            [
                'Company Name',
                'Type of Customer',
                'Address',
                'City',
                'State',
                'Country',
                'Zip Code',
                'Name',
                'Designation',
                'Phone',
                'Email',
                'Fax',
                'Name',
                'Designation',
                'Phone',
                'Email',
                'Fax',
                'Company Tax ID',
                'Payment Terms',
                'Credit Limit',
                'Name',
                'Address',
                'City',
                'State',
                'Country',
                'Zip Code',
            ]
        ];
    }

    // 3. Define the data as an array (custom data mapping to match columns)
    public function array(): array
    {
        $exportData = [];
        foreach ($this->data as $filterData) {
            // Decode JSON to an associative array
            $data = json_decode($filterData, true);

            // Basic Information
            $exportRow = [
                $data['company_name'] ?? '',
                $data['customer_type'] ?? '',
                $data['address'] ?? '',
                $data['city'] ?? '',
                $data['state']['name'] ?? '',
                $data['country']['name'] ?? '',
                $data['zip_code'] ?? ''
            ];

            // contact Details
            if (isset($data['contact']) && is_array($data['contact']) && count($data['contact']) > 0) {
                $firstContact = $data['contact'][0];
                $exportRow = array_merge($exportRow, [
                    $firstContact['contact_name'] ?? '',
                    $firstContact['contact_designation'] ?? '',
                    $firstContact['contact_phone'] ?? '',
                    $firstContact['contact_email'] ?? '',
                    $firstContact['contact_fax'] ?? '',
                ]);
            } else {
                // Add empty sales fields if no sales record exists
                $exportRow = array_merge($exportRow, ['', '', '', '', '']);
            }

            // Finance Details
            if (isset($data['finance']) && is_array($data['finance']) && count($data['finance']) > 0) {
                $firstFinance = $data['finance'][0];
                $exportRow = array_merge($exportRow, [
                    $firstFinance['finance_name'] ?? '',
                    $firstFinance['finance_designation'] ?? '',
                    $firstFinance['finance_phone'] ?? '',
                    $firstFinance['finance_email'] ?? '',
                    $firstFinance['finance_fax'] ?? '',
                ]);
            } else {
                // Add empty finance fields if no finance record exists
                $exportRow = array_merge($exportRow, ['', '', '', '', '']);
            }

            // Company Details
            $exportRow = array_merge($exportRow, [
                $data['company_tax_id'] ?? '',
                $data['payment_terms'] ?? '',
                $data['credit_limit'] ?? '',
            ]);

            // Delivery Details
            if (isset($data['delivery']) && is_array($data['delivery']) && count($data['delivery']) > 0) {
                $firstDelivery = $data['delivery'][0];
                $exportRow = array_merge($exportRow, [
                    $firstDelivery['delivery_name'] ?? '',
                    $firstDelivery['delivery_address'] ?? '',
                    $firstDelivery['delivery_city'] ?? '',
                    $firstDelivery['state']['name'] ?? '',
                    $firstDelivery['country']['name'] ?? '',
                    $firstDelivery['delivery_zip_code'] ?? '',
                ]);
            } else {
                // Add empty finance fields if no finance record exists
                $exportRow = array_merge($exportRow, ['', '', '', '', '', '']);
            }

            // Add the row to the export data
            $exportData[] = $exportRow;
        }
        return $exportData; // Return the structured export data
    }

    // 4. Apply styling for headers, including colors
    public function styles(Worksheet $sheet)
    {
        // Set row height for the header rows
        $sheet->getRowDimension(1)->setRowHeight(24); // Set the height of the first header row
        $sheet->getRowDimension(2)->setRowHeight(28); // Set the height of the second header row if needed

        $borderRanges = [
            'A1:G1',
            'H1:L1',
            'M1:Q1',
            'R1:T1',
            'U1:Z1',
        ];

        // Loop through each range to merge cells
        foreach ($borderRanges as $range) {
            $sheet->mergeCells($range);
        }
        // Style the first header row (e.g., background color and bold font)
        $sheet->getStyle('A1:Z1')->applyFromArray([
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
            // 'borders' => [
            //     'outline' => [
            //         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            //         'color' => ['argb' => '000000'], // Border color (black)
            //     ],
            // ],
        ]);


        // // Apply borders only to the merged header cells
        // $sheet->getStyle('A1:G1')->applyFromArray([
        //     'borders' => [
        //         'outline' => [
        //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //             'color' => ['argb' => '000000'], // Border color (black)
        //         ],
        //     ],
        // ]);
        $borderStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // Border color (black)
                ],
            ],
        ];
        // Apply borders to each range
        foreach ($borderRanges as $range) {
            $sheet->getStyle($range)->applyFromArray($borderStyle);
        }
        // Style the second header row (e.g., background color and bold font)
        $sheet->getStyle('A2:Z2')->applyFromArray([
            // 'font' => [
            //     'bold' => true,
            //     'color' => ['argb' => '000000'], // White font
            // ],
            // 'fill' => [
            //     'fillType' => 'solid',
            //     'color' => ['argb' => '8DB4E2'], // Light blue background
            // ],
            // 'alignment' => [
            //     'horizontal' => 'center',
            // ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // Border color (black)
                ],
            ],
        ]);

        // Add this after the data is set in your `styles()` method:
        // $highestColumn = $sheet->getHighestColumn();
        // $sheet->getColumnDimension($highestColumn)->setAutoSize(true);
        // // Set column widths to auto size based on content
        // foreach (range('A2', 'AE2') as $columnID) {
        //     $sheet->getColumnDimension($columnID)->setAutoSize(true);
        // }
        return [];
    }
}

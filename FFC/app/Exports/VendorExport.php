<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorExport implements FromArray, WithHeadings, WithCustomStartCell, WithStyles
{
    protected $data;

    public function __construct($filteredData)
    {
        Log::info("Constructor");
        // Assume $filteredData is an array of data to be exported
        // $this->data = $filteredData;
        $this->data = is_string($filteredData) ? json_decode($filteredData, true) : $filteredData;
        Log::info($this->data);
        Log::info("Constructor end");
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
                'Ops/Dispatch/Sales Contact',
                '',
                '',
                '',
                '',
                'Finance Contact',
                '',
                '',
                '',
                '',
                'Company Details',
                '',
                '',
                '',
                '',
                '',
                'Bank Details',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],

            // Second Header Row: Detailed Column Names
            [
                'Vendor Type',
                'Company Name',
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
                'MC Number',
                'SCAC Code',
                'US DOT Number',
                'Payment Terms',
                'Remarks',
                'Bank Name',
                'Country',
                'Bank Account',
                'Routing',
                'Swift Code',
                'IBAN No.',
                'IFSC Code',
                'Address'
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

            $vendorType = '';
            // Get the first vendor type if available
            if (isset($data['vendor_types']) && is_array($data['vendor_types']) && count($data['vendor_types']) > 0) {
                $vendorType = $data['vendor_types'][0]['type'] ?? ''; // Get first vendor type
            }

            // Basic Information
            $exportRow = [
                $vendorType,
                $data['company_name'] ?? '',
                $data['address'] ?? '',
                $data['city'] ?? '',
                $data['state']['name'] ?? '',
                $data['country']['name'] ?? '',
                $data['zip_code'] ?? ''
            ];

            // Sales Details
            if (isset($data['sales']) && is_array($data['sales']) && count($data['sales']) > 0) {
                $firstSales = $data['sales'][0];
                $exportRow = array_merge($exportRow, [
                    $firstSales['sales_name'] ?? '',
                    $firstSales['sales_designation'] ?? '',
                    $firstSales['sales_phone'] ?? '',
                    $firstSales['sales_email'] ?? '',
                    $firstSales['sales_fax'] ?? '',
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
                $data['mc_number'] ?? '',
                $data['scac_number'] ?? '',
                $data['us_dot_number'] ?? '',
                $data['payment_term'] ?? '',
                $data['remarks'] ?? '',
            ]);

            // Bank Details
            $exportRow = array_merge(
                $exportRow,
                [
                    $data['bank_name'] ?? '',
                    $data['bank_country']['name'] ?? '',
                    $data['bank_account_number'] ?? '',
                    $data['bank_routing'] ?? '',
                    $data['bank_swift_code'] ?? '',
                    $data['bank_iban_number'] ?? '',
                    $data['bank_ifsc_code'] ?? '',
                    $data['bank_address'] ?? '',
                ]
            );

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

        // Merge cells for the first header row
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('H1:L1');
        $sheet->mergeCells('M1:Q1');
        $sheet->mergeCells('R1:W1');
        $sheet->mergeCells('X1:AE1');

        // Style the first header row (e.g., background color and bold font)
        $sheet->getStyle('A1:AE1')->applyFromArray([
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

        $borderRanges = [
            'A1:G1',
            'H1:L1',
            'M1:Q1',
            'R1:W1',
            'X1:AE1',
        ];

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
        $sheet->getStyle('A2:AE2')->applyFromArray([
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

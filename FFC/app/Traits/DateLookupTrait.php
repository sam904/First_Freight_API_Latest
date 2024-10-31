<?php

namespace App\Traits;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

trait DateLookupTrait
{
    public function validateDate($date)
    {
        if (is_numeric($date)) {
            // Convert Excel date to PHP date
            $timestamp = Date::excelToDateTimeObject($date);
            return $timestamp->format('Y-m-d'); // Return in desired format
        }

        // If it's not numeric, attempt to create a Carbon instance
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error("Invalid date format for date: $date", ['error' => $e->getMessage()]);
            return null; // Or set a default date if necessary
        }
    }
}

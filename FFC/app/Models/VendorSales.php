<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class VendorSales extends Model
{
    use HasFactory;

    protected $table = 'vendors_sales';

    protected $fillable = [
        "sales_name",
        "sales_designation",
        "sales_phone",
        "sales_email",
        "sales_fax",
        "vendors_id",
    ];

    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
        'vendors_id',
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        return array_diff($columns, $this->excludedColumns);
    }


    public function vendor()
    {
        return $this->belongsTo(Vendor::class, "vendors_id");
    }
}

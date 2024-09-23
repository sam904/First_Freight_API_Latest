<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, "vendors_id");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorFinances extends Model
{
    use HasFactory;

    protected $table = 'vendors_finances';

    protected $fillable = [
        "finance_name",
        "finance_designation",
        "finance_phone",
        "finance_email",
        "finance_fax",
        "vendors_id",
    ];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, "vendors_id");
    }
}

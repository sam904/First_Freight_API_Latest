<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_type',
        'company_name',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'company_tax_id',
        'mc_number',
        'scac_number',
        'us_dot_number',
        'upload_w9',
        'void_check',
        'upload_insurance_certificate',
        'date_of_expiration',
        'bank_name',
        'bank_account_number',
        'bank_routing',
        'bank_address',
        'remarks',
        'status',
    ];

    public function sales()
    {
        return $this->hasMany(VendorSales::class, 'vendors_id');
    }


    public function finance()
    {
        return $this->hasMany(VendorFinances::class, 'vendors_id');
    }
}

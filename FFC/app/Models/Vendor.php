<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_type',
        'company_name',
        'address',
        'city',
        'state_id',
        'country_id',
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
        // 'contact_name',
        // 'phone',
        'email',
        'payment_term'
    ];

    protected $excludedColumns = [
        'id',
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        return array_diff($columns, $this->excludedColumns);
    }

    public function sales()
    {
        return $this->hasMany(VendorSales::class, 'vendors_id');
    }

    public function finance()
    {
        return $this->hasMany(VendorFinances::class, 'vendors_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function type()
    {
        return $this->belongsTo(VendorType::class);
    }
}

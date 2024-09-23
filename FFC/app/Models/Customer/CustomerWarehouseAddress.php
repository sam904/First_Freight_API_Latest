<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerWarehouseAddress extends Model
{
    use HasFactory;

    protected $table = "customer_warehouse_addresses";

    protected $fillable = [
        'warehouse_name',
        'warehouse_address',
        'warehouse_city',
        'warehouse_state',
        'warehouse_country',
        'warehouse_zip_code',
        'customer_id',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

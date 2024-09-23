<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerShippingAddress extends Model
{
    use HasFactory;

    protected $table = "customer_shipping_addresses";

    //CustomerShipping	
    protected $fillable = [
        "shipping_name",
        "shipping_address",
        "shipping_city",
        "shipping_state",
        "shipping_country",
        "shipping_zip_code",
        "customer_id"
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

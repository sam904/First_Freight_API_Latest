<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDeliveryAddress extends Model
{
    use HasFactory;

    protected $table = "customer_delivery_addresses";

    //CustomerShipping	
    protected $fillable = [
        "delivery_name",
        "delivery_address",
        "delivery_city",
        "delivery_state",
        "delivery_country",
        "delivery_zip_code",
        "customer_id"
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

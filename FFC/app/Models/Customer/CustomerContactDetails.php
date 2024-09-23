<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContactDetails extends Model
{
    use HasFactory;

    protected $table = "customer_contact_details";
    protected $fillable = [
        "contact_name",
        "contact_designation",
        "contact_phone",
        "contact_email",
        "contact_fax",
        "customer_id",
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

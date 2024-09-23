<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFinanceDetails extends Model
{
    use HasFactory;

    protected $table = "customer_finance_details";

    protected $fillable = [
        "finance_name",
        "finance_designation",
        "finance_phone",
        "finance_email",
        "finance_fax",
        "customer_id",
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

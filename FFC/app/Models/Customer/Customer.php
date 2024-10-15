<?php

namespace App\Models\Customer;

use App\Models\Country;
use App\Models\Customer\CustomerContactDetails;
use App\Models\Customer\CustomerDeliveryAddress;
use App\Models\Customer\CustomerFinanceDetails;
use App\Models\Customer\CustomerShippingAddress;
use App\Models\Customer\CustomerWarehouseAddress;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        "company_name",
        "customer_type",
        "material_type",
        "address",
        "city",
        "state",
        "country",
        "zip_code",
        "company_tax_id",
        "payment_terms",
        "credit_limit",
        "status",
        'contact_name',
        'phone',
        'email',
    ];

    public function delivery()
    {
        return $this->hasMany(CustomerDeliveryAddress::class, 'customer_id');
    }

    public function contact()
    {
        return $this->hasMany(CustomerContactDetails::class, 'customer_id');
    }

    public function finance()
    {
        return $this->hasMany(CustomerFinanceDetails::class, 'customer_id');
    }
    public function shipping()
    {
        return $this->hasMany(CustomerShippingAddress::class, 'customer_id');
    }
    public function warehouse()
    {
        return $this->hasMany(CustomerWarehouseAddress::class, 'customer_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
}

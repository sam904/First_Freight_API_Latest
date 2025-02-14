<?php

namespace App\Models\Customer;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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


    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
        'customer_id',
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        return array_diff($columns, $this->excludedColumns);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'delivery_country', 'id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'delivery_state', 'id');
    }
}

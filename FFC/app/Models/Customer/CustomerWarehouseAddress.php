<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CustomerWarehouseAddress extends Model
{
    use HasFactory;

    protected $table = "customer_warehouse_addresses";

    protected $hidden = ['id', 'customer_id'];
    protected $fillable = [
        'warehouse_name',
        'warehouse_address',
        'warehouse_city',
        'warehouse_state',
        'warehouse_country',
        'warehouse_zip_code',
        'customer_id',
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
}

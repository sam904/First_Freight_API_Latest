<?php

namespace App\Models\Order;

use App\Models\Customer\Customer;
use App\Models\Quote\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'received_date',
        'overweight',
        'quote_id',
        'customer_id',
        'address_id',
        'created_by',
    ];

    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
        'quote_id',
        'customer_id',
        'address_id',
        'created_by',
        'received_date',
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        // $columns = array_merge($columns, ['sales_name']);
        return array_diff($columns, $this->excludedColumns);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function orderContainerDetails()
    {
        return $this->hasManyThrough(OrderContainerDetails::class, OrderDetails::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function address()
    {
        return $this->belongsTo(Customer::class, 'address_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

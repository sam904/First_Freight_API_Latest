<?php

namespace App\Models\Order;

use App\Models\Customer\Customer;
use App\Models\Quote\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class, 'order_id', 'id');
    }

    public function orderContainerDetails()
    {
        return $this->hasMany(OrderContainerDetails::class);
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

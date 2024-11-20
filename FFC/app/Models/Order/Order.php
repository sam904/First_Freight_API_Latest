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
        'customer_id',
        'quote_id',
        'received_date',
        'address_id',
        'container_no',
        'container_size',
        'po',
        'cpo',
        'overweight',
        'created_by',
        'order_status_id',
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class, 'order_id', 'id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function customerAddress()
    {
        return $this->belongsTo(Customer::class, 'address_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function orderStatuses()
    {
        return $this->hasMany(OrderStatus::class, 'order_id', 'id');
    }
}

<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_delivery_id',
        'delivery_status_id',
        'current_status',
    ];

    public function orderDelivery()
    {
        return $this->belongsTo(OrderDelivery::class, 'order_delivery_id');
    }

    public function deliveryStatus()
    {
        return $this->belongsTo(OrderStatusMaster::class, 'delivery_status_id');
    }
}

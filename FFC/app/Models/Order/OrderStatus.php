<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'status_id'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}

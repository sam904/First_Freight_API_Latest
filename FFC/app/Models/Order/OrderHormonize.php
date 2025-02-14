<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHormonize extends Model
{
    use HasFactory;

    protected $fillable = ['tarriff_schedule_number', 'order_details_id'];

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetails::class, 'order_details_id');
    }
}

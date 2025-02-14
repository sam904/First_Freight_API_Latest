<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderContainerDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'container_no',
        'container_size',
        'po',
        'cpo',
        'order_id',
        'overweight',
        'container_type',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetails::class, 'order_details_id');
    }
}

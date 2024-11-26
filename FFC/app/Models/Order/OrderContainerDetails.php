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
        'order_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}

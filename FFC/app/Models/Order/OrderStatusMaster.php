<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusMaster extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'service_type_id', 'sort_level'];

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class);
    }
}

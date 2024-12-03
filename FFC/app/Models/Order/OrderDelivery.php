<?php

namespace App\Models\Order;

use App\Models\Common\ServiceType;
use App\Models\Destination\Destination;
use App\Models\Port\Port;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_sent_date',
        'is_vendor_confirmed',
        'picked_up_date',
        'schedule_date',
        'empty_return_on_date',
        'empty_pick_up_cutoff_date',
        'transit_time',
        'mode',
        'free_days',
        'order_details_id',
        'port_of_loading_id',
        'port_of_discharge_id',
        'destination_id',
        'vendor_id',
        'transhipment_port_id',
        'delivery_created_by',
        'country_of_origin',
        'ignate_cutoff_date',
    ];

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetails::class, 'order_details_id');
    }

    public function portOfLoading()
    {
        return $this->belongsTo(Port::class, 'port_of_loading_id');
    }

    public function portOfDischarge()
    {
        return $this->belongsTo(Port::class, 'port_of_discharge_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function transhipmentPort()
    {
        return $this->belongsTo(Port::class, 'transhipment_port_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'delivery_created_by');
    }

    public function statuses()
    {
        return $this->hasMany(OrderDeliveryStatus::class, 'order_delivery_id');
    }

    public function latestStatus()
    {
        return $this->hasOne(OrderDeliveryStatus::class)->latest('created_at');
    }
}

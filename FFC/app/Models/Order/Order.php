<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_type_id',
        'customer_id',
        'vendor_id',
        'port_id',
        'destination_id',
        'received_date',
        'address',
        'container_no',
        'bl',
        'po',
        'cpo',
        'seal',
        'last_free_day',
        'container_size',
        'weight',
        'overweight',
        'pallets',
        'streamship_line',
        'discharge_date',
        'freight_location',
        'firm_code',
        'vessel_voyage',
        'eta',
        'commodity',
        'special_instructions',
        'notes',
        'upload_documents',
        'free_days',
        'delivery_order_sent_date',
        'delivery_vendor_confirm',
        'delivery_picked_up_date',
        'delivery_schedule_date',
        'delivery_empty_return_date',
        'msc_chassis',
        'pierpass_fees',
        'clean_truck_fees',
        'accessorial_charges'
    ];
}

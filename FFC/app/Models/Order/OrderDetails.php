<?php

namespace App\Models\Order;

use App\Models\Common\ServiceType;
use App\Models\Customer\Customer;
use App\Models\Destination\Destination;
use App\Models\Port\Port;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_type_id',
        'vendor_id',
        'port_id',
        'destination_id',
        'created_by',
        'transhipment_port_id',
        'shipper',
        'shipper_address',
        'consignee',
        'consignee_address',
        'buyer',
        'buyer_address',
        'notify_party',
        'booking_request_sent_date',
        'booking_date',
        'bc_sent_to_shipper',
        'bl',
        'seal',
        'weight',
        'pallets',
        'etd',
        'eta',
        'streamship_line',
        'discharge_date',
        'cargo_ready_date',
        'vessel_voyage',
        'commodity',
        'si_cut_off',
        'vgm_cut_off',
        'cy_cut_off',
        'isf',
        'isf_date',
        'isf_no',
        'custom_filed',
        'last_free_day',
        'freight_location',
        'dangerous_goods',
        'freight_prepaid',
        'all_inclusive_rate',
        'hts_code',
        'firm_code',
        'special_instructions',
        'notes',
        'delivery_order_sent_date',
        'delivery_vendor_confirm',
        'delivery_picked_up_date',
        'delivery_schedule_date',
        'delivery_empty_return_date',
        'delivery_empty_pick_up_cutoff_date',
        'transmit_time',
        'delivery_mode',
        'delivery_free_days',
        'msc_chassis',
        'pierpass_fees',
        'clean_truck_fees',
        'accessorial_charges',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function port()
    {
        return $this->belongsTo(Port::class, 'port_id', 'id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function transhipmentPort()
    {
        return $this->belongsTo(Port::class, 'transhipment_port_id', 'id');
    }
}

<?php

namespace App\Models\Order;

use App\Models\Common\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;
    protected $fillable = [
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
        'seal',
        'weight',
        'pallets',
        'etd',
        'eta',
        'streamship_line',
        'discharge_date',
        'cargo_ready_date',
        'master_bl',
        'house_bl',
        'freight_location',
        'vessel_voyage',
        'firm_code',
        'commodity',
        'special_instructions',
        'upload_documents',
        'notes',
        'si_cut_off',
        'vgm_cut_off',
        'cy_cut_off',
        'isf',
        'isf_date',
        'isf_no',
        'isf_confirmation',
        'custom_filed',
        'custom_clearance_date',
        'custom_confirmation',
        'last_free_day',
        'dangerous_goods',
        'freight_prepaid',
        'all_inclusive_rate',
        'hts_code',
        'consolidator',
        'importer_of_record_name',
        'importer_of_record_number',
        'msc_chassis',
        'pierpass_fees',
        'clean_truck_fees',
        'accessorial_charges',
        'order_id',
        'service_type_id',
        'sort_level'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class);
    }
}

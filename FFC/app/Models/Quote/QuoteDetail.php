<?php

namespace App\Models\Quote;

use App\Models\Common\ServiceType;
use App\Models\Destination\Destination;
use App\Models\Port\Port;
use App\Models\Rate\Rate;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'quote_id',
        'container_weight',
        'port_of_loading_id',
        'port_of_discharge_id',
        'destination_id',
        // 'vendor_id',
        'rate_id',
        'freight',
        'fsc',
        'service_type_id',
        "fsc_amount",
        "dry_fsc",
        "quote_tab_name",
        'isChecked',
    ];

    protected $appends = ['routeName']; // Ensure 'routeName' is appended to the JSON output.

    public function getRouteNameAttribute()
    {
        $port = $this->portOfLoading->name ?? $this->portOfDischarge->name ?? '';
        $destination = $this->destination->name ?? '';
        return "{$port} - {$destination}";
    }

    public function charges()
    {
        return $this->hasMany(QuoteCharge::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class, 'port_id');
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

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}

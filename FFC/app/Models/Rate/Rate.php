<?php

namespace App\Models\Rate;

use App\Models\Destination\Destination;
use App\Models\Port\Port;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'port_id',
        'destination_id',
        'start_date',
        'expiry',
        'status',
        'freight',
        'fsc'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function charges()
    {
        return $this->hasMany(RateCharge::class);
    }
}

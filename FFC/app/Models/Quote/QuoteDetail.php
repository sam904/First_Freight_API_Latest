<?php

namespace App\Models\Quote;

use App\Models\Destination\Destination;
use App\Models\Port\Port;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'quote_id',
        'container_weight',
        'port_id',
        'destination_id',
        'vendor_id',
        'rate_id',
        'freight',
        'fsc',
    ];


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

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }
}

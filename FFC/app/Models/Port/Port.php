<?php

namespace App\Models\Port;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'port_type_id', 'country_id', 'state_id', 'status'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function states()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function portType()
    {
        return $this->belongsTo(PortType::class);
    }

    public function portTerminals()
    {
        return $this->hasMany(PortTerminal::class);
    }

    public function getPort($name)
    {
        return Port::where('name', $name)->first();
    }

    public function getPortOld($name, $lineNo)
    {
        try {
            $port = Port::where('name', $name)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, "Port : '{$name}' not found at line number : " . $lineNo);
        }
        return $port;
    }
}

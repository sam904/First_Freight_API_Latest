<?php

namespace App\Models\Destination;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'county_id', 'state_id', 'country_id', 'status', 'zip_code'];

    protected $hidden  = ['status'];

    public function county()
    {
        return $this->belongsTo(County::class . "county_id");
    }

    public function states()
    {
        return $this->belongsTo(State::class . "state_id");
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function getDestination($name)
    {
        return Destination::where('name', $name)->first();
    }

    public function getDestinationOld($name, $lineNo)
    {
        try {
            $destination = Destination::where('name', $name)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, "Destination : '{$name}' not found at line number : " . $lineNo);
        }
        return $destination;
    }
}

<?php

namespace App\Models\Destination;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'county_id', 'state_id', 'country_id', 'status'];

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
}

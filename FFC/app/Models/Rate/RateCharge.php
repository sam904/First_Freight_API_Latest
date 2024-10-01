<?php

namespace App\Models\Rate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateCharge extends Model
{
    use HasFactory;
    protected $fillable = ['charge_name', 'amount', 'rate_id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }
}

<?php

namespace App\Models\Quote;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteCharge extends Model
{
    use HasFactory;

    protected $fillable = ['quote_detail_id', 'charge_name', 'amount'];

    public function quoteDetail()
    {
        return $this->belongsTo(QuoteDetail::class);
    }
}

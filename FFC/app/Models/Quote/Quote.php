<?php

namespace App\Models\Quote;

use App\Models\Customer\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'status',
        'created_by',
        'quote_status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quoteDetails()
    {
        return $this->hasMany(QuoteDetail::class);
    }

    public function quoteCharges()
    {
        return $this->hasMany(QuoteCharge::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

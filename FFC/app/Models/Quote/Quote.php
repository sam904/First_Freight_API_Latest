<?php

namespace App\Models\Quote;

use App\Models\Customer\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Quote extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'status',
        'created_by',
        'quote_status',
    ];


    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
        'customer_id',
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        return array_diff($columns, $this->excludedColumns);
    }

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

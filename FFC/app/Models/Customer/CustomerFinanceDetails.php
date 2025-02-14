<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CustomerFinanceDetails extends Model
{
    use HasFactory;

    protected $table = "customer_finance_details";

    protected $fillable = [
        "finance_name",
        "finance_designation",
        "finance_phone",
        "finance_email",
        "finance_fax",
        "customer_id",
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
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

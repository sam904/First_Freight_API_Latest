<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CustomerContactDetails extends Model
{
    use HasFactory;

    protected $table = "customer_contact_details";
    protected $fillable = [
        "contact_name",
        "contact_designation",
        "contact_phone",
        "contact_email",
        "contact_fax",
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

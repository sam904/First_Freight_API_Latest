<?php

namespace App\Models\Rate;

use App\Models\Common\ServiceType;
use App\Models\Destination\Destination;
use App\Models\Port\Port;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'port_of_loading_id',
        'port_of_discharge_id',
        'destination_id',
        'start_date',
        'expiry',
        'status',
        'freight',
        'fsc',
        'service_type_id'
    ];


    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
        'vendor_id',
        'port_id',
        'destination_id',
        'service_type_id'
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        // $columns = array_merge($columns, ['sales_name']);
        return array_diff($columns, $this->excludedColumns);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function charges()
    {
        return $this->hasMany(RateCharge::class);
    }
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}

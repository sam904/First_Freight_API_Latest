<?php

namespace App\Models\Port;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Port extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'port_type_id', 'country_id', 'state_id', 'status'];

    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
        'port_type_id',
        'country_id',
        'state_id',
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        return array_diff($columns, $this->excludedColumns);
    }

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
}

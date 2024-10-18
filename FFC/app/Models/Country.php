<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'iso_code'];
    protected $hidden = ['created_at', 'updated_at'];


    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        return array_diff($columns, $this->excludedColumns);
    }

    public function states()
    {
        return $this->hasMany(State::class);
    }
}

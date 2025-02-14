<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class OrderNote extends Model
{
    use HasFactory;


    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['description', 'title', 'order_id', 'tag', 'pin', 'status', 'user_id'];

    protected $excludedColumns = [
        'id',
        'created_at',
        'updated_at',
        'user_id',
        'order_id',
        'pin'
    ];

    public function getSearchableColumns()
    {
        // Fetch all columns of the table dynamically, and exclude specific ones
        $table = $this->getTable();
        $columns = Schema::getColumnListing($table);
        // $columns = array_merge($columns, ['sales_name']);
        return array_diff($columns, $this->excludedColumns);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

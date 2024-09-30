<?php

namespace App\Models\Port;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortTerminal extends Model
{
    use HasFactory;

    protected $table = "port_terminals";

    protected $fillable = ['name', 'port_id', 'status'];

    protected $hidden = ['status'];

    public function port()
    {
        return $this->belongsTo(Port::class);
    }
}

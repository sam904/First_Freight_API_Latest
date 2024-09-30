<?php

namespace App\Models\Port;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortType extends Model
{
    use HasFactory;

    protected $table = "port_types";
    protected $fillable = ['name'];
    protected $hidden = ['created_at', 'updated_at'];
}

<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiverName extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    protected $hidden = ['status', 'created_at', 'updated_at'];
}

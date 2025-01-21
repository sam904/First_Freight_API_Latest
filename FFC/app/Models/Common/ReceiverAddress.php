<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiverAddress extends Model
{
    use HasFactory;

    protected $fillable = ['address', 'status'];

    protected $hidden = ['status', 'created_at', 'updated_at'];
}

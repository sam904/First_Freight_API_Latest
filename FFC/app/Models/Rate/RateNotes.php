<?php

namespace App\Models\Rate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateNotes extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
}

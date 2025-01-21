<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuggestionAddress extends Model
{
    use HasFactory;

    protected $fillable = ['address'];

    protected $hidden = ['created_at', 'updated_at'];
}

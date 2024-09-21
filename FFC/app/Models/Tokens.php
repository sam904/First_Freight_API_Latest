<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tokens extends Model
{
    use HasFactory;

    // If this is set to false, timestamps won't be managed
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'iso_code'];
    protected $hidden = ['created_at', 'updated_at'];

    public function states()
    {
        return $this->hasMany(State::class);
    }
}

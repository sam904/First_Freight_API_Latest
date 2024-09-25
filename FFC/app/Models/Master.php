<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    use HasFactory;

    protected $fillable = ["name"];
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'master_id');
    }
}

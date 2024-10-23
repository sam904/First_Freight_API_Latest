<?php

namespace App\Models\Common;

use App\Models\Rate\Rate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    protected $hidden = ['created_at', 'updated_at'];

    public function rates()
    {
        return $this->hasMany(Rate::class, 'service_type_id');
    }
}

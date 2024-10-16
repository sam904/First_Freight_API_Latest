<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorType extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'status'];

    protected $hidden = ['created_at', 'updated_at'];

    public static function getVendorTypes()
    {
        return self::select('id', 'type')->where('status', 'active')->orderBy('id', 'desc')->get();
    }
}

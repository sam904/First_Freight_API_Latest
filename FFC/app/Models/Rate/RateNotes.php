<?php

namespace App\Models\Rate;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateNotes extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['description', 'title', 'rate_id', 'tag', 'pin', 'status', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

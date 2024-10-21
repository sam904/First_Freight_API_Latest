<?php

namespace App\Models\Quote;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteNotes extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['description', 'title', 'quote_id', 'tag', 'pin', 'status'];
}

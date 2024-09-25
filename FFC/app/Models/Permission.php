<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = "users_permission";

    protected $fillable = ["user_id", "master_id", "can_create", "can_edit", "can_delete", "can_view", "granted_by"];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function masters()
    {
        return $this->belongsToMany(Master::class, 'master_id');
    }
}

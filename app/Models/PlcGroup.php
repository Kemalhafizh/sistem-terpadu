<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlcGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'type', 'coordinator_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function members()
    {
        return $this->hasMany(PlcGroupMember::class);
    }

    public function discussions()
    {
        return $this->hasMany(PlcDiscussion::class);
    }
}

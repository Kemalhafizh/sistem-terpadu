<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutabaahItem extends Model
{
    protected $fillable = ['name', 'category', 'is_active'];

    public function logs()
    {
        return $this->hasMany(MutabaahLog::class);
    }
}

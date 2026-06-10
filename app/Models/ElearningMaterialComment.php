<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ElearningMaterialComment extends Model
{
    use HasUuid;

    protected $fillable = ['uuid', 'elearning_material_id', 'user_id', 'content'];

    public function material()
    {
        return $this->belongsTo(ElearningMaterial::class, 'elearning_material_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

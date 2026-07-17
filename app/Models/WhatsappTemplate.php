<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'content',
        'placeholders',
        'is_active',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function broadcasts()
    {
        return $this->hasMany(WhatsappBroadcast::class, 'template_id');
    }

    public function automations()
    {
        return $this->hasMany(WhatsappAutomation::class, 'template_id');
    }
}

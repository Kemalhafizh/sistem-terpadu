<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasFactory;

    protected $table = 'system_configurations';

    protected $fillable = [
        'environment',
        'config_group',
        'config_key',
        'config_value_encrypted',
        'description',
        'is_sensitive',
        'last_updated_by',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
    ];
}

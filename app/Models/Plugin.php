<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'entry_class',
        'config',
        'is_enabled',
        'installed_at',
    ];

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'boolean',
        'installed_at' => 'datetime',
    ];

    public function hooks(): HasMany
    {
        return $this->hasMany(PluginHook::class, 'plugin_id');
    }
}

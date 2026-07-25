<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginHook extends Model
{
    use HasFactory;

    protected $fillable = [
        'plugin_id',
        'hook_name',
        'priority',
        'handler_class',
        'handler_method',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
}

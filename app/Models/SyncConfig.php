<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_target',
        'local_table',
        'field_mapping',
    ];

    protected $casts = [
        'field_mapping' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReleaseVersion extends Model
{
    use HasFactory;

    protected $table = 'release_versions';

    protected $fillable = [
        'version',
        'release_type',
        'changelog_summary',
        'breaking_changes',
        'migration_count',
        'released_by',
        'released_at',
        'rollback_tested',
    ];

    protected $casts = [
        'breaking_changes' => 'array',
        'released_at' => 'datetime',
        'rollback_tested' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_id',
        'direction',
        'status',
        'records_processed',
        'records_failed',
        'error_details',
        'summary',
    ];

    protected $casts = [
        'error_details' => 'array',
        'records_processed' => 'integer',
        'records_failed' => 'integer',
    ];

    public function config()
    {
        return $this->belongsTo(IntegrationConfig::class, 'config_id');
    }
}

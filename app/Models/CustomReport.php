<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomReport extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'config',
        'is_shared',
        'schedule_cron',
        'schedule_recipients',
    ];

    protected $casts = [
        'config' => 'array',
        'is_shared' => 'boolean',
        'schedule_recipients' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

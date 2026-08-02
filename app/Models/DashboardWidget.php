<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'dashboard_id',
        'widget_type',
        'title',
        'grid_x',
        'grid_y',
        'grid_width',
        'grid_height',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(CustomDashboard::class, 'dashboard_id');
    }
}

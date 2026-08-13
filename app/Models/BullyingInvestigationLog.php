<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BullyingInvestigationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bullying_report_id', 'investigator_id', 'action', 'notes', 'is_confidential'
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(BullyingReport::class, 'bullying_report_id');
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCardPdf extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'generated_at' => 'datetime',
        'is_official' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(ReportCardTemplate::class, 'report_card_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

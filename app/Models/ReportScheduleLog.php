<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportScheduleLog extends Model
{
    use HasFactory;

    protected $fillable = ['conditional_report_id', 'status', 'execution_note'];

    public function conditionalReport()
    {
        return $this->belongsTo(ConditionalReport::class);
    }
}

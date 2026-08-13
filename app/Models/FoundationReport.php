<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class FoundationReport extends Model {
    use HasFactory;
    protected $fillable = ['title', 'report_type', 'period', 'period_start', 'period_end', 'data_payload', 'executive_summary', 'status', 'prepared_by', 'reviewed_by'];
    protected $casts = ['data_payload' => 'array', 'period_start' => 'date', 'period_end' => 'date'];
}

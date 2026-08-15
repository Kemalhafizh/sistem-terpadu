<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ConditionalReport extends Model {
    use HasFactory;
    protected $fillable = ['report_name', 'view_template', 'cron_schedule', 'distribution_list', 'trigger_condition_sql', 'is_active', 'created_by'];
    protected $casts = ['distribution_list' => 'array'];
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function logs() { return $this->hasMany(ReportScheduleLog::class); }
}

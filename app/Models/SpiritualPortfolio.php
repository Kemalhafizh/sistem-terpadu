<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpiritualPortfolio extends Model {
    use HasFactory;
    protected $fillable = ['student_id', 'record_date', 'daily_prayers', 'fasting_record', 'akhlaq_peer_score', 'akhlaq_teacher_score', 'character_building_notes', 'evaluator_id'];
    protected $casts = ['record_date' => 'date', 'daily_prayers' => 'array', 'fasting_record' => 'array'];
    public function student() { return $this->belongsTo(Student::class); }
    public function evaluator() { return $this->belongsTo(Employee::class, 'evaluator_id'); }
}
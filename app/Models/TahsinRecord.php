<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahsinRecord extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['student_id', 'teacher_id', 'surah_name', 'start_ayah', 'end_ayah', 'tajwid_errors', 'maqam_recognized', 'fluency_score', 'makhraj_score', 'teacher_notes', 'assessment_date'];
    protected $casts = ['tajwid_errors' => 'array', 'maqam_recognized' => 'array', 'assessment_date' => 'date'];
    public function student() { return $this->belongsTo(Student::class); }
    public function teacher() { return $this->belongsTo(Employee::class, 'teacher_id'); }
}
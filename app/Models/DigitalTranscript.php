<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalTranscript extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'transcript_hash', 'competency_radar', 'verification_url', 'status'];
    protected $casts = [
        'competency_radar' => 'array',
    ];
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}

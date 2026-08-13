<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SharedResource extends Model {
    use HasFactory;
    protected $fillable = ['uuid', 'uploader_id', 'title', 'description', 'resource_type', 'subject_id', 'grade_level', 'file_path', 'file_type', 'file_size', 'download_count', 'average_rating', 'tags', 'status', 'reviewed_by'];
    protected $casts = ['tags' => 'array', 'average_rating' => 'decimal:2'];
    public function uploader() { return $this->belongsTo(User::class, 'uploader_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function ratings() { return $this->hasMany(SharedResourceRating::class, 'resource_id'); }
}
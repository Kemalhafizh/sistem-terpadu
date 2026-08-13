<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SharedResourceRating extends Model {
    use HasFactory;
    protected $fillable = ['resource_id', 'user_id', 'rating', 'review'];
    public function resource() { return $this->belongsTo(SharedResource::class, 'resource_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
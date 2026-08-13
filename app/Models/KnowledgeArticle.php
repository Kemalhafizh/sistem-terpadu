<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class KnowledgeArticle extends Model {
    use HasFactory;
    protected $fillable = ['uuid', 'author_id', 'title', 'content', 'category', 'tags', 'status', 'view_count', 'like_count', 'is_featured', 'attachment_path'];
    protected $casts = ['tags' => 'array', 'is_featured' => 'boolean'];
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function comments() { return $this->hasMany(KnowledgeArticleComment::class, 'article_id'); }
    public function likes() { return $this->hasMany(KnowledgeArticleLike::class, 'article_id'); }
}
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class KnowledgeArticleComment extends Model {
    use HasFactory;
    protected $fillable = ['article_id', 'user_id', 'content'];
    public function article() { return $this->belongsTo(KnowledgeArticle::class, 'article_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
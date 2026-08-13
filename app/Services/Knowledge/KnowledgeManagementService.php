<?php
namespace App\Services\Knowledge;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleLike;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
class KnowledgeManagementService {
    public function publishArticle(array $data): KnowledgeArticle {
        $data['uuid'] = Str::uuid();
        return KnowledgeArticle::create($data);
    }
    public function incrementViewCount(int $articleId): void {
        KnowledgeArticle::where('id', $articleId)->increment('view_count');
    }
    public function toggleLike(int $articleId, int $userId): bool {
        $like = KnowledgeArticleLike::where('article_id', $articleId)->where('user_id', $userId)->first();
        if ($like) {
            $like->delete();
            KnowledgeArticle::where('id', $articleId)->decrement('like_count');
            return false;
        }
        KnowledgeArticleLike::create(['article_id' => $articleId, 'user_id' => $userId]);
        KnowledgeArticle::where('id', $articleId)->increment('like_count');
        return true;
    }
    public function getPopularArticles(int $limit = 10): Collection {
        return KnowledgeArticle::where('status', 'published')->orderBy('view_count', 'desc')->take($limit)->get();
    }
    public function searchArticles(string $query): Collection {
        return KnowledgeArticle::where('status', 'published')->where('title', 'like', "%$query%")->orWhere('content', 'like', "%$query%")->get();
    }
}
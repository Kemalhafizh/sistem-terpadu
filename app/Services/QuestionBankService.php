<?php

namespace App\Services;

use App\Models\QuestionBankCategory;
use App\Models\QuestionBankItem;
use App\Models\Exam;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QuestionBankService
{
    public function getCategories(?int $subjectId = null)
    {
        $query = QuestionBankCategory::withCount('items')->with('subject');
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        return $query->latest()->get();
    }

    public function getItems(array $filters = [])
    {
        $query = QuestionBankItem::with(['category.subject', 'creator']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['difficulty'])) {
            $query->byDifficulty($filters['difficulty']);
        }
        if (!empty($filters['cognitive_level'])) {
            $query->byCognitive($filters['cognitive_level']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['is_validated'])) {
            $query->where('is_validated', (bool) $filters['is_validated']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('question_text', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function createQuestion(array $data): QuestionBankItem
    {
        $similarityCheck = $this->getDuplicateCheck($data['question_text'], $data['category_id'] ?? null);
        if ($similarityCheck['is_duplicate']) {
            throw new \InvalidArgumentException("Pertanyaan serupa terdeteksi ({$similarityCheck['similarity']}% kemiripan dengan soal ID: {$similarityCheck['similar_item_id']})");
        }

        return QuestionBankItem::create([
            'uuid' => (string) Str::uuid(),
            'category_id' => $data['category_id'],
            'created_by' => $data['created_by'],
            'type' => $data['type'] ?? 'pilihan_ganda',
            'question_text' => $data['question_text'],
            'options' => $data['options'] ?? null,
            'correct_answer' => $data['correct_answer'],
            'explanation' => $data['explanation'] ?? null,
            'difficulty' => $data['difficulty'] ?? 'sedang',
            'cognitive_level' => $data['cognitive_level'] ?? 'C3',
            'media_attachments' => $data['media_attachments'] ?? null,
            'is_validated' => $data['is_validated'] ?? false,
        ]);
    }

    public function updateQuestion(int $id, array $data): QuestionBankItem
    {
        $item = QuestionBankItem::findOrFail($id);
        $item->update($data);
        return $item;
    }

    public function validateQuestion(int $id, int $validatorId): QuestionBankItem
    {
        $item = QuestionBankItem::findOrFail($id);
        $item->update([
            'is_validated' => true,
            'validated_by' => $validatorId,
            'validated_at' => now(),
        ]);
        return $item;
    }

    public function autoGenerateExam(array $criteria): array
    {
        $categoryId = $criteria['category_id'] ?? null;
        $totalQuestions = (int) ($criteria['total_questions'] ?? 10);
        $difficultyDistribution = $criteria['difficulty_distribution'] ?? [
            'mudah' => 30,
            'sedang' => 50,
            'sulit' => 20,
        ];

        $mudahCount = (int) round(($difficultyDistribution['mudah'] / 100) * $totalQuestions);
        $sulitCount = (int) round(($difficultyDistribution['sulit'] / 100) * $totalQuestions);
        $sedangCount = $totalQuestions - ($mudahCount + $sulitCount);

        $selectedItems = collect();

        $queryBuilder = function ($diff, $limit) use ($categoryId) {
            $q = QuestionBankItem::where('difficulty', $diff);
            if ($categoryId) {
                $q->where('category_id', $categoryId);
            }
            return $q->inRandomOrder()->limit($limit)->get();
        };

        $selectedItems = $selectedItems->merge($queryBuilder('mudah', $mudahCount));
        $selectedItems = $selectedItems->merge($queryBuilder('sedang', $sedangCount));
        $selectedItems = $selectedItems->merge($queryBuilder('sulit', $sulitCount));

        if ($selectedItems->count() < $totalQuestions) {
            $remaining = $totalQuestions - $selectedItems->count();
            $existingIds = $selectedItems->pluck('id')->toArray();
            $fallback = QuestionBankItem::whereNotIn('id', $existingIds)
                ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
                ->inRandomOrder()
                ->limit($remaining)
                ->get();
            $selectedItems = $selectedItems->merge($fallback);
        }

        // If exam_id provided, link them
        if (!empty($criteria['exam_id'])) {
            $examId = $criteria['exam_id'];
            DB::table('exam_question_links')->where('exam_id', $examId)->delete();

            foreach ($selectedItems->values() as $order => $item) {
                DB::table('exam_question_links')->insert([
                    'exam_id' => $examId,
                    'question_bank_item_id' => $item->id,
                    'order_number' => $order + 1,
                    'weight' => (float) ($criteria['weight_per_question'] ?? 1.00),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $item->increment('usage_count');
            }
        }

        return [
            'total_generated' => $selectedItems->count(),
            'items' => $selectedItems,
            'distribution' => [
                'mudah' => $selectedItems->where('difficulty', 'mudah')->count(),
                'sedang' => $selectedItems->where('difficulty', 'sedang')->count(),
                'sulit' => $selectedItems->where('difficulty', 'sulit')->count(),
            ],
        ];
    }

    public function getDuplicateCheck(string $questionText, ?int $categoryId = null): array
    {
        $normalizedNew = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $questionText)));
        $candidates = QuestionBankItem::when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->limit(100)
            ->get();

        foreach ($candidates as $candidate) {
            $normalizedCandidate = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $candidate->question_text)));
            similar_text($normalizedNew, $normalizedCandidate, $percent);
            if ($percent > 90) {
                return [
                    'is_duplicate' => true,
                    'similarity' => round($percent, 1),
                    'similar_item_id' => $candidate->id,
                ];
            }
        }

        return ['is_duplicate' => false, 'similarity' => 0, 'similar_item_id' => null];
    }
}

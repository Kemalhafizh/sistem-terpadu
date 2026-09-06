<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBankItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $casts = [
        'options' => 'array',
        'media_attachments' => 'array',
        'is_validated' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(QuestionBankCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_question_links', 'question_bank_item_id', 'exam_id')
            ->withPivot('order_number', 'weight')
            ->withTimestamps();
    }

    public function scopeValidated($query)
    {
        return $query->where('is_validated', true);
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty', $level);
    }

    public function scopeByCognitive($query, $level)
    {
        return $query->where('cognitive_level', $level);
    }
}

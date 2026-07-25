<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SopDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'category',
        'content_html',
        'version',
        'status',
        'effective_date',
        'review_date',
        'author_id',
        'approved_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'effective_date' => 'date',
        'review_date' => 'date',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(SopAcknowledgement::class, 'sop_document_id');
    }

    public function isAcknowledgedBy(int $userId): bool
    {
        return $this->acknowledgements()->where('user_id', $userId)->exists();
    }
}

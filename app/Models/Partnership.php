<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partnership extends Model
{
    use HasFactory;

    protected $table = 'partnerships';

    protected $fillable = [
        'organization_name',
        'partner_type',
        'mou_document_path',
        'contact_person',
        'contact_email',
        'status',
        'valid_until',
        'created_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(PartnershipActivity::class, 'partnership_id');
    }
}

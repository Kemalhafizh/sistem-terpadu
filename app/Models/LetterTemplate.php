<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterTemplate extends Model
{
    protected $fillable = [
        'name', 'code', 'body_template', 'header_html', 'footer_html',
        'required_fields', 'signature_path', 'signer_name', 'signer_title', 'is_active',
    ];

    protected $casts = [
        'required_fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class, 'template_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatisfactionResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_id',
        'respondent_id',
        'score',
        'text_response',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalizationString extends Model
{
    use HasFactory;

    protected $table = 'localization_strings';

    protected $fillable = [
        'group_name',
        'string_key',
        'text_id',
        'text_en',
        'text_ar',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NlqQueryLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'natural_query', 'generated_sql', 'is_successful', 'execution_time_ms'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

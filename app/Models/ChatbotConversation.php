<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_message',
        'bot_response',
        'intent',
        'context_data',
        'confidence',
        'was_helpful',
    ];

    protected $casts = [
        'context_data' => 'array',
        'confidence' => 'float',
        'was_helpful' => 'boolean',
    ];

    /**
     * Relationship to the User who made the conversation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'student_id',
        'message',
        'attachment_path',
        'attachment_type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the message.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the student (child) this message refers to.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Scope for unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for message history between two specific users relating to a student.
     */
    public function scopeBetweenUsers($query, $user1Id, $user2Id, $studentId)
    {
        return $query->where('student_id', $studentId)
            ->where(function ($q) use ($user1Id, $user2Id) {
                $q->where(function ($sub) use ($user1Id, $user2Id) {
                    $sub->where('sender_id', $user1Id)->where('recipient_id', $user2Id);
                })->orWhere(function ($sub) use ($user1Id, $user2Id) {
                    $sub->where('sender_id', $user2Id)->where('recipient_id', $user1Id);
                });
            });
    }
}

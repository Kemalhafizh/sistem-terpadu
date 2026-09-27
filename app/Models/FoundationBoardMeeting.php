<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundationBoardMeeting extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'meeting_date', 'start_time', 'agenda', 'minutes', 'attendees', 'decisions', 'action_items', 'status', 'secretary_id'];

    protected $casts = ['meeting_date' => 'date', 'attendees' => 'array', 'decisions' => 'array', 'action_items' => 'array'];
}

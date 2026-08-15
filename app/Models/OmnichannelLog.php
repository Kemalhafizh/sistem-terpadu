<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmnichannelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel',
        'recipient_address',
        'template_id',
        'delivery_status',
        'error_message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(OmnichannelTemplate::class, 'template_id');
    }
}

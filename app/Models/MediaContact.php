<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'journalist_name',
        'media_outlet',
        'email',
        'phone_number',
        'relationship_status',
    ];
}

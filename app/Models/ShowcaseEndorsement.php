<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowcaseEndorsement extends Model
{
    use HasFactory;

    protected $fillable = [
        'showcase_id', 'endorser_id', 'endorsement_text',
    ];

    public function showcase()
    {
        return $this->belongsTo(StudentShowcase::class, 'showcase_id');
    }

    public function endorser()
    {
        return $this->belongsTo(User::class, 'endorser_id');
    }
}

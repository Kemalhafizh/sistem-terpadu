<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutabaahLog extends Model
{
    protected $fillable = ['student_id', 'mutabaah_item_id', 'is_completed', 'date', 'verified_by'];

    protected $casts = [
        'is_completed' => 'boolean',
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function item()
    {
        return $this->belongsTo(MutabaahItem::class, 'mutabaah_item_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

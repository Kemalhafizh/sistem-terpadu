<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCardCharacter extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function template()
    {
        return $this->belongsTo(ReportCardTemplate::class, 'report_card_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}

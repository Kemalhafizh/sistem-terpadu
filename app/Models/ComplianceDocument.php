<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'standard_id',
        'name',
        'file_path',
        'uploaded_by',
        'notes',
    ];

    public function standard()
    {
        return $this->belongsTo(EdsStandard::class, 'standard_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

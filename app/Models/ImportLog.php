<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'imported_by',
        'file_name',
        'total_rows',
        'success_count',
        'error_count',
        'error_details',
        'created_ids',
        'status',
    ];

    protected $casts = [
        'error_details' => 'array',
        'created_ids' => 'array',
        'total_rows' => 'integer',
        'success_count' => 'integer',
        'error_count' => 'integer',
    ];

    /**
     * Get the user who imported the data.
     */
    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}

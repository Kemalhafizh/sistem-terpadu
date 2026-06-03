<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CareerOpportunity extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
        'title',
        'company_name',
        'type',
        'description',
        'requirements',
        'contact_email',
        'closing_date',
        'file_path',
        'created_by',
    ];

    protected $casts = [
        'closing_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('closing_date', '>=', now()->toDateString());
    }
}

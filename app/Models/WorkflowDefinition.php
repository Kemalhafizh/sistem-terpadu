<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'steps', 'is_active'];
    
    protected $casts = [
        'steps' => 'array', 
        'is_active' => 'boolean'
    ];

    public function requests()
    {
        return $this->hasMany(WorkflowRequest::class, 'definition_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrisisResponseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'crisis_incident_id', 'responder_id', 'action_type', 'description', 'evidence_file_path',
    ];

    public function incident()
    {
        return $this->belongsTo(CrisisIncident::class, 'crisis_incident_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }
}

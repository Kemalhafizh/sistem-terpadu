<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class EventStream extends Model {
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['id', 'aggregate_type', 'aggregate_id', 'event_type', 'payload', 'metadata', 'version', 'created_at'];
    protected $casts = ['payload' => 'array', 'metadata' => 'array', 'id' => 'string'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffRotation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'from_unit_id', 'to_unit_id', 'rotation_type', 'effective_date', 'end_date', 'reason', 'status', 'approved_by'];

    protected $casts = ['effective_date' => 'date', 'end_date' => 'date'];
}

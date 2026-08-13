<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class UnitAccreditationRecord extends Model {
    use HasFactory;
    protected $fillable = ['foundation_unit_id', 'accreditation_body', 'accreditation_grade', 'accreditation_score', 'valid_from', 'valid_until', 'certificate_number', 'status'];
    protected $casts = ['valid_from' => 'date', 'valid_until' => 'date', 'accreditation_score' => 'decimal:2'];
}

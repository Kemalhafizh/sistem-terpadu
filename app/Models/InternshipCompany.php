<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'industry_sector', 'address', 'pic_name', 'pic_contact', 'is_verified_partner'
    ];

    protected $casts = [
        'is_verified_partner' => 'boolean',
    ];

    public function applications()
    {
        return $this->hasMany(InternshipApplication::class, 'company_id');
    }
}

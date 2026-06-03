<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}

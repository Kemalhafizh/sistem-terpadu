<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'serve_date',
        'canteen_menu_id',
        'meal_time',
        'preparation_quota',
    ];

    protected $casts = [
        'serve_date' => 'date',
    ];

    public function menu()
    {
        return $this->belongsTo(CanteenMenu::class, 'canteen_menu_id');
    }
}

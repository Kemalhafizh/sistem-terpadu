<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanteenMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_name',
        'ingredients',
        'allergens',
        'calories',
        'price',
        'is_healthy_certified',
    ];

    protected $casts = [
        'allergens' => 'array',
        'price' => 'decimal:2',
        'is_healthy_certified' => 'boolean',
    ];

    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class);
    }
}

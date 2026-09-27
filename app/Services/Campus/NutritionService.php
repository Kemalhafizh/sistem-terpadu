<?php

namespace App\Services\Campus;

use App\Models\MealPlan;
use Carbon\Carbon;

class NutritionService
{
    public function planMenu(array $data)
    {
        return MealPlan::create($data);
    }

    public function calculateDailyCalories($date, $mealTime = null)
    {
        $query = MealPlan::with('menu')->whereDate('serve_date', $date);

        if ($mealTime) {
            $query->where('meal_time', $mealTime);
        }

        $plans = $query->get();

        return $plans->sum(function ($plan) {
            return $plan->menu->calories;
        });
    }

    public function getWeeklyMenu(Carbon $startDate)
    {
        $endDate = $startDate->copy()->addDays(6);

        return MealPlan::with('menu')
            ->whereBetween('serve_date', [$startDate, $endDate])
            ->orderBy('serve_date')
            ->get()
            ->groupBy('serve_date');
    }
}

<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CanteenMenu;
use App\Models\MealPlan;
use App\Services\Campus\NutritionService;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    protected $nutritionService;

    public function __construct(NutritionService $nutritionService)
    {
        $this->nutritionService = $nutritionService;
    }

    public function menuPlanner()
    {
        $plans = MealPlan::with('menu')->get();
        $menus = CanteenMenu::all();

        return view('canteen.menu-planner', compact('plans', 'menus'));
    }

    public function storeMenu(Request $request)
    {
        $data = $request->validate([
            'serve_date' => 'required|date',
            'canteen_menu_id' => 'required|exists:canteen_menus,id',
            'meal_time' => 'required|in:breakfast,lunch,snack',
            'preparation_quota' => 'required|integer',
        ]);

        $this->nutritionService->planMenu($data);

        return back()->with('success', 'Meal plan added successfully');
    }

    public function menuItems()
    {
        $menus = CanteenMenu::all();

        return view('canteen.menu-planner', compact('menus'));
    }

    public function nutritionDashboard()
    {
        $dailyCalories = $this->nutritionService->calculateDailyCalories(now()->toDateString());

        return view('canteen.nutrition-tracker', compact('dailyCalories'));
    }
}

<?php

namespace App\Services\Platform;

use App\Models\CustomDashboard;
use App\Models\DashboardWidget;

class DashboardBuilderService
{
    public function createDashboard(int $userId, string $title, ?string $desc = null): CustomDashboard
    {
        return CustomDashboard::create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $desc,
            'is_default' => false,
        ]);
    }

    public function addWidget(int $dashboardId, string $type, string $title): DashboardWidget
    {
        return DashboardWidget::create([
            'dashboard_id' => $dashboardId,
            'widget_type' => $type,
            'title' => $title,
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_width' => 4,
            'grid_height' => 3,
        ]);
    }
}

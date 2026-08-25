<?php

declare(strict_types=1);

namespace App\Services\InteractiveUi;

use App\Models\ClassroomLayout;
use App\Models\SeatAssignment;
use App\Models\Student;

class ClassroomLayoutService
{
    /**
     * Create or update classroom layout
     */
    public function saveLayout(array $data, ?int $layoutId = null): ClassroomLayout
    {
        $payload = [
            'classroom_id' => $data['classroom_id'],
            'room_id' => $data['room_id'] ?? null,
            'layout_name' => $data['layout_name'] ?? 'Denah Tempat Duduk',
            'grid_rows' => $data['grid_rows'] ?? 5,
            'grid_columns' => $data['grid_columns'] ?? 6,
            'furniture_positions' => $data['furniture_positions'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ];

        if ($layoutId) {
            $layout = ClassroomLayout::find($layoutId);
            if ($layout) {
                $layout->update($payload);
                return $layout;
            }
        }

        return ClassroomLayout::create($payload);
    }

    /**
     * Assign student to seat coordinate
     */
    public function assignSeat(int $layoutId, int $studentId, int $row, int $col, ?string $behaviorNote = null): SeatAssignment
    {
        return SeatAssignment::updateOrCreate(
            [
                'classroom_layout_id' => $layoutId,
                'student_id' => $studentId,
            ],
            [
                'grid_row' => $row,
                'grid_column' => $col,
                'behavior_note' => $behaviorNote,
            ]
        );
    }

    /**
     * Generate behavior heatmap data for layout
     */
    public function generateBehaviorHeatmap(ClassroomLayout $layout): array
    {
        $assignments = $layout->seatAssignments()->with('student')->get();
        $grid = [];

        for ($r = 0; $r < $layout->grid_rows; $r++) {
            for ($c = 0; $c < $layout->grid_columns; $c++) {
                $grid[$r][$c] = null;
            }
        }

        foreach ($assignments as $seat) {
            $grid[$seat->grid_row][$seat->grid_column] = [
                'student_id' => $seat->student_id,
                'student_name' => $seat->student->nama_lengkap ?? 'Siswa',
                'behavior_note' => $seat->behavior_note,
                'point' => 100,
            ];
        }

        return [
            'layout_id' => $layout->id,
            'layout_name' => $layout->layout_name,
            'grid_rows' => $layout->grid_rows,
            'grid_columns' => $layout->grid_columns,
            'seats' => $grid,
        ];
    }
}

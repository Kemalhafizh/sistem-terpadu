<?php

namespace App\Services\Student;

use App\Models\StudentShowcase;
use App\Models\ShowcaseEndorsement;

class ShowcaseService
{
    public function publishProject(array $data)
    {
        return StudentShowcase::create($data);
    }

    public function endorseProject(array $data)
    {
        return ShowcaseEndorsement::create($data);
    }

    public function getPopularShowcases()
    {
        return StudentShowcase::orderByDesc('view_count')->take(10)->get();
    }
}

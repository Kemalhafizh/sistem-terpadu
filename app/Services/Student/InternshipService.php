<?php

namespace App\Services\Student;

use App\Models\InternshipApplication;
use App\Models\InternshipJournal;

class InternshipService
{
    public function processApplication(array $data)
    {
        return InternshipApplication::create($data);
    }

    public function logDailyJournal(array $data)
    {
        return InternshipJournal::create($data);
    }
    
    public function postOpening(array $data)
    {
        return true;
    }
}

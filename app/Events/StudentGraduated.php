<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentGraduated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $student,
        public string $graduationYear = '2026'
    ) {}
}

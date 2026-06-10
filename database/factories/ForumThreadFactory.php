<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\ElearningClass;
use App\Models\ForumThread;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumThreadFactory extends Factory
{
    protected $model = ForumThread::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(5),
            'body' => $this->faker->paragraph(5),
            'user_id' => User::factory(),
            'classroom_id' => Classroom::factory(),
            'subject_id' => Subject::factory(),
            'elearning_class_id' => ElearningClass::factory(),
            'scope' => 'class',
            'is_pinned' => false,
            'is_locked' => false,
            'is_announcement' => false,
            'attachment_path' => null,
            'reply_count' => 0,
            'last_reply_at' => null,
        ];
    }
}

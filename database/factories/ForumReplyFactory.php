<?php

namespace Database\Factories;

use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumReplyFactory extends Factory
{
    protected $model = ForumReply::class;

    public function definition(): array
    {
        return [
            'thread_id' => ForumThread::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'body' => $this->faker->paragraph(2),
            'attachment_path' => null,
            'is_best_answer' => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\LibraryBook;
use App\Models\LibraryCategory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryBookFactory extends Factory
{
    protected $model = LibraryBook::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'category_id' => LibraryCategory::factory(),
            'subject_id' => Subject::factory(),
            'type' => $this->faker->randomElement(['physical', 'pdf', 'video']),
            'file_path' => null,
            'cover_path' => null,
            'video_url' => null,
            'is_physical_available' => true,
            'download_count' => 0,
            'description' => $this->faker->paragraph(),
            'created_by' => User::factory()->state(['role' => 'admin']),
        ];
    }
}

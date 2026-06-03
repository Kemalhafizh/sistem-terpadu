<?php

namespace Database\Factories;

use App\Models\PortfolioItem;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PortfolioItemFactory extends Factory
{
    protected $model = PortfolioItem::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'student_id' => Student::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(['prestasi', 'sertifikat', 'karya', 'proyek', 'organisasi', 'other']),
            'date' => $this->faker->date(),
            'file_path' => null,
            'external_link' => $this->faker->url,
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'is_public' => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\LibraryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryCategoryFactory extends Factory
{
    protected $model = LibraryCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'icon' => $this->faker->randomElement(['book', 'bookmark', 'academic-cap', 'user']),
        ];
    }
}

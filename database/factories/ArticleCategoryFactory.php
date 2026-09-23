<?php

namespace Database\Factories;

use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleCategory>
 */
class ArticleCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'section' => 'news',
            'is_active' => true,
        ];
    }

    public function publicSection(): static
    {
        return $this->state(['section' => 'public']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}

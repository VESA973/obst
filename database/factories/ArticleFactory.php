<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'article_category_id' => ArticleCategory::factory(),
            'excerpt' => fake()->sentence(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ArticleStatus::Draft]);
    }

    public function scheduled(): static
    {
        return $this->state(['status' => ArticleStatus::Scheduled, 'published_at' => now()->addWeek()]);
    }
}

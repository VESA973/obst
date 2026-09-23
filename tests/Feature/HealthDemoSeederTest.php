<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use Database\Seeders\HealthDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HealthDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_never_reuses_a_news_category_with_the_same_slug(): void
    {
        Http::fake(['picsum.photos/*' => Http::response('', 404)]);
        $news = ArticleCategory::factory()->create(['name' => 'Prévention santé', 'slug' => 'prevention-sante']);

        $this->seed(HealthDemoSeeder::class);

        // La rubrique en conflit est ignorée ; les trois autres sont créées normalement.
        $this->assertSame(0, $news->articles()->count());
        $this->assertSame(12, Article::whereHas('category', fn ($query) => $query->where('section', 'public'))->count());
        $this->assertSame(3, ArticleCategory::where('section', 'public')->count());
    }
}

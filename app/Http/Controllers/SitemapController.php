<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\PageSetting;
use Illuminate\Http\Response;

/**
 * Plan du site XML : pages publiques, catégories et articles visibles.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $pages = PageSetting::menuPages()->map(fn (array $page) => ['loc' => route($page['route']), 'lastmod' => null]);

        $categories = ArticleCategory::query()->active()->ordered()->get()->map(fn (ArticleCategory $category) => [
            'loc' => $category->section === 'public' ? route('public.category', $category) : route('news.category', $category->slug),
            'lastmod' => $category->updated_at,
        ]);

        $articles = Article::query()->onSite()->whereHas('category')->with('category')->latest('published_at')->get()
            ->map(fn (Article $article) => ['loc' => $article->publicUrl(), 'lastmod' => $article->updated_at]);

        return response()
            ->view('sitemap', [
                'urls' => collect([['loc' => route('home'), 'lastmod' => null]])
                    ->merge($pages)->merge($categories)->merge($articles)
                    ->filter(fn (array $url) => filled($url['loc']))
                    ->unique('loc'),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

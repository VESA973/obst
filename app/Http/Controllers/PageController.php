<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Event;
use App\Models\ResourceFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'latestArticles' => Article::where('is_published', true)
                ->with('categoryModel')
                ->latest('published_at')
                ->latest()
                ->take(3)
                ->get(),
            'upcomingEvents' => Event::where('is_published', true)
                ->withCount('registrations')
                ->orderByRaw('event_date IS NULL')
                ->orderBy('event_date')
                ->take(3)
                ->get(),
        ]);
    }

    public function news(): View
    {
        return view('pages.news', [
            'articles' => Article::where('is_published', true)
                ->with(['assets', 'categoryModel'])
                ->where(function ($query): void {
                    $query->whereHas('categoryModel', fn ($categoryQuery) => $categoryQuery->where('section', 'news'))
                        ->orWhereDoesntHave('categoryModel');
                })
                ->latest('published_at')
                ->latest()
                ->get(),
        ]);
    }

    public function article(Article $article): View
    {
        abort_unless($article->is_published, 404);
        abort_if($article->categoryModel?->section === 'public', 404);
        $article->load(['assets', 'categoryModel']);

        return view('pages.article', [
            'article' => $article,
        ]);
    }

    public function public(): View
    {
        return view('pages.public', [
            'categories' => ArticleCategory::where('section', 'public')
                ->withCount(['articles' => fn ($query) => $query->where('is_published', true)])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function publicCategory(ArticleCategory $category): View
    {
        abort_unless($category->section === 'public', 404);

        return view('pages.public-category', [
            'category' => $category,
            'articles' => $category->articles()
                ->where('is_published', true)
                ->with('assets')
                ->latest('published_at')
                ->latest()
                ->get(),
        ]);
    }

    public function publicArticle(ArticleCategory $category, Article $article): View
    {
        abort_unless($category->section === 'public', 404);
        abort_unless($article->is_published && $article->article_category_id === $category->id, 404);

        $article->load(['assets', 'categoryModel']);

        return view('pages.article', [
            'article' => $article,
            'backRoute' => route('public.category', $category),
            'backLabel' => 'Retour à '.$category->name,
        ]);
    }

    public function agenda(): View
    {
        return view('pages.agenda', [
            'events' => Event::where('is_published', true)
                ->with('assets')
                ->withCount('registrations')
                ->orderByRaw('event_date IS NULL')
                ->orderBy('event_date')
                ->get(),
        ]);
    }

    public function professionalResources(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('pro')->with('status', 'Connectez-vous pour accéder aux ressources.');
        }

        if ((! $user->is_member && ! $user->is_admin) || ! $user->hasVerifiedEmail()) {
            return redirect()->route('pro')->with('status', 'Votre compte doit être confirmé pour accéder aux ressources.');
        }

        return view('pages.pro-resources', [
            'resources' => ResourceFile::latest()->get(),
        ]);
    }
}

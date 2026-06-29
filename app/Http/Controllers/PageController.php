<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
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
                ->latest('published_at')
                ->latest()
                ->get(),
        ]);
    }

    public function article(Article $article): View
    {
        abort_unless($article->is_published, 404);
        $article->load(['assets', 'categoryModel']);

        return view('pages.article', [
            'article' => $article,
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
}

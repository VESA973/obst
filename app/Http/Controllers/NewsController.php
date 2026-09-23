<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\NewsBlocks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Rubrique publique « Actualités ».
 */
class NewsController extends Controller
{
    private const PER_PAGE = 12;

    public function index(): View
    {
        return view('news.index', NewsBlocks::home());
    }

    /**
     * Page d'une catégorie. Les anciennes adresses d'articles (/actualites/{article})
     * arrivent aussi ici et sont redirigées en 301 vers la nouvelle adresse.
     */
    public function category(Request $request, string $slug): View|RedirectResponse|Response
    {
        $category = $this->findCategory($slug);

        if (! $category) {
            $article = NewsBlocks::articles()->where('slug', $slug)->first();

            abort_unless($article, 404);

            return redirect()->to($article->publicUrl(), 301);
        }

        $articles = NewsBlocks::articles()
            ->whereBelongsTo($category, 'category')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Bouton « Charger plus » : renvoie uniquement les cartes, et l'adresse de la page suivante en en-tête.
        if ($request->boolean('fragment')) {
            return response()
                ->view('news.partials.card-grid', ['articles' => $articles, 'skipFirst' => false])
                ->header('X-Next-Page', $articles->hasMorePages() ? route('news.category', ['slug' => $slug, 'page' => $articles->currentPage() + 1]) : '');
        }

        return view('news.category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }

    public function show(string $categorySlug, string $articleSlug): View|RedirectResponse
    {
        $article = NewsBlocks::articles()->with('assets')->where('slug', $articleSlug)->first();

        abort_unless($article && $article->category->is_active, 404);

        // Mauvaise catégorie dans l'URL (article déplacé) : redirection vers la bonne adresse.
        if ($article->category->slug !== $categorySlug) {
            return redirect()->to($article->publicUrl(), 301);
        }

        return view('news.show', ['article' => $article, ...NewsBlocks::neighbours($article)]);
    }

    public function search(Request $request): View
    {
        $term = trim((string) $request->string('q'));

        $articles = mb_strlen($term) >= 2
            ? NewsBlocks::articles()
                ->where(fn (Builder $query) => $query
                    ->where('title', 'like', '%'.$this->escapeLike($term).'%')
                    ->orWhere('excerpt', 'like', '%'.$this->escapeLike($term).'%')
                    ->orWhere('body', 'like', '%'.$this->escapeLike($term).'%'))
                ->paginate(self::PER_PAGE)
                ->withQueryString()
            : null;

        return view('news.search', [
            'term' => $term,
            'articles' => $articles,
        ]);
    }

    public function feed(): Response
    {
        return response()
            ->view('news.feed', ['articles' => NewsBlocks::articles()->limit(30)->get()])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    private function findCategory(string $slug): ?ArticleCategory
    {
        return NewsBlocks::categories()->firstWhere('slug', $slug);
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}

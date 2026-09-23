<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Event;
use App\Models\ResourceFile;
use App\Support\AgendaEvent;
use App\Support\NewsBlocks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    /** Articles par page sur une rubrique « Santé de la femme » (comme les Actualités). */
    private const HEALTH_PER_PAGE = 12;

    public function home(): View
    {
        return view('pages.home', [
            // Carrousels de l'accueil (lecture seule, mêmes requêtes que les sections concernées).
            'latestArticles' => NewsBlocks::articles()->limit(8)->get(),
            'healthArticles' => NewsBlocks::articles('public')->limit(8)->get(),
            'healthRubriques' => NewsBlocks::categories('public')->filter(fn ($rubrique) => $rubrique->articles_count > 0)->values(),
            'upcomingEvents' => Event::where('is_published', true)
                ->withCount('registrations')
                ->orderByRaw('event_date IS NULL')
                ->orderBy('event_date')
                ->take(6)
                ->get(),
        ]);
    }

    /**
     * Accueil « Santé de la femme » : même mise en page que les Actualités (vues news.*),
     * avec les rubriques de la section. Avec ?q=, résultats de recherche limités à la section.
     */
    public function public(Request $request): View
    {
        $term = trim((string) $request->string('q'));

        if ($request->has('q')) {
            $like = '%'.addcslashes($term, '%_\\').'%';

            return view('news.search', [
                'term' => $term,
                'articles' => mb_strlen($term) >= 2
                    ? NewsBlocks::articles('public')
                        ->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('body', 'like', $like))
                        ->paginate(self::HEALTH_PER_PAGE)
                        ->withQueryString()
                    : null,
            ]);
        }

        return view('news.index', NewsBlocks::home('public'));
    }

    public function publicCategory(Request $request, ArticleCategory $category): View|Response
    {
        abort_unless($category->section === 'public' && $category->is_active, 404);

        $articles = NewsBlocks::articles('public')
            ->whereBelongsTo($category, 'category')
            ->paginate(self::HEALTH_PER_PAGE);

        // Bouton « Charger plus » : uniquement les cartes, et l'adresse de la page suivante en en-tête.
        if ($request->boolean('fragment')) {
            return response()
                ->view('news.partials.card-grid', ['articles' => $articles, 'skipFirst' => false])
                ->header('X-Next-Page', $articles->hasMorePages() ? route('public.category', ['category' => $category, 'page' => $articles->currentPage() + 1]) : '');
        }

        return view('news.category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }

    public function publicArticle(ArticleCategory $category, Article $article): View
    {
        abort_unless($category->section === 'public' && $category->is_active, 404);
        abort_unless($article->isOnSite() && $article->article_category_id === $category->id, 404);

        $article->load(['assets', 'category', 'categoryModel', 'author']);

        return view('news.show', ['article' => $article, ...NewsBlocks::neighbours($article)]);
    }

    /**
     * Agenda : événements publiés (à venir et passés), avec leur type, commune et horaires
     * déduits côté front (App\Support\AgendaEvent). Les ancres #event-{id} des QR codes sont conservées.
     */
    public function agenda(): View
    {
        $events = Event::where('is_published', true)
            ->withCount('registrations')
            ->orderByRaw('event_date IS NULL')
            ->orderBy('event_date')
            ->get()
            ->map(fn (Event $event) => new AgendaEvent($event));

        [$past, $upcoming] = $events->partition(fn (AgendaEvent $item) => $item->isPast());

        return view('agenda.index', [
            'upcoming' => $upcoming->values(),
            // Passés : du plus récent au plus ancien.
            'past' => $past->sortByDesc(fn (AgendaEvent $item) => $item->startsAt())->values(),
        ]);
    }

    /**
     * Fiche d'un événement (nouvelle page ; l'agenda reste la liste principale).
     */
    public function agendaShow(Event $event): View
    {
        abort_unless($event->is_published, 404);

        $event->load('assets')->loadCount('registrations');

        return view('agenda.show', [
            'item' => new AgendaEvent($event),
            'others' => Event::where('is_published', true)
                ->whereKeyNot($event->id)
                ->whereDate('event_date', '>=', today())
                ->withCount('registrations')
                ->orderBy('event_date')
                ->limit(6)
                ->get()
                ->map(fn (Event $other) => new AgendaEvent($other)),
        ]);
    }

    /**
     * Fichier .ics (« Ajouter à mon agenda »), généré à la volée.
     */
    public function agendaIcs(Event $event): Response
    {
        abort_unless($event->is_published && $event->event_date, 404);

        return response((new AgendaEvent($event))->ics(), 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.Str::slug($event->title).'.ics"',
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

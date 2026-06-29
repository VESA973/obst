<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $pages = collect([
            ['title' => "L'association", 'type' => 'Page', 'url' => route('about'), 'text' => 'association mission equipe quinzaine obstetricale'],
            ['title' => 'Nos actions', 'type' => 'Page', 'url' => route('actions'), 'text' => 'actions prevention formation information terrain'],
            ['title' => 'Sante des femmes', 'type' => 'Page', 'url' => route('public'), 'text' => 'sante femmes prevention public ressources'],
            ['title' => 'Espace pro', 'type' => 'Page', 'url' => route('pro'), 'text' => 'professionnels ressources connexion dossiers'],
            ['title' => 'Agenda', 'type' => 'Page', 'url' => route('agenda'), 'text' => 'agenda evenement inscription atelier conference'],
            ['title' => 'Actualites', 'type' => 'Page', 'url' => route('news'), 'text' => 'actualites articles presse publications'],
            ['title' => 'Contact', 'type' => 'Page', 'url' => route('contact'), 'text' => 'contact email reseaux adresse'],
        ])->filter(fn (array $page) => str_contains(mb_strtolower($page['title'].' '.$page['text']), mb_strtolower($query)))
            ->take(4)
            ->values();

        $articles = Article::where('is_published', true)
            ->where(function ($builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->take(5)
            ->get()
            ->map(fn (Article $article) => [
                'title' => $article->title,
                'type' => 'Actualite',
                'url' => $article->external_url ?: route('news').'#article-'.$article->id,
            ]);

        $events = Event::where('is_published', true)
            ->where(function ($builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%");
            })
            ->take(5)
            ->get()
            ->map(fn (Event $event) => [
                'title' => $event->title,
                'type' => 'Agenda',
                'url' => route('agenda').'#event-'.$event->id,
            ]);

        return response()->json($pages->concat($articles)->concat($events)->take(8)->values());
    }
}

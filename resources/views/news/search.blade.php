{{-- Recherche dans les articles : titre, extrait et contenu. --}}
@extends('layouts.news')

@section('title', ($term ? 'Recherche « '.$term.' »' : 'Recherche').' — '.$editorial->title.' | La Quinzaine Obstétricale')

@push('news-head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('news')
    <div class="mx-auto max-w-3xl">
        <h1 class="font-serif text-3xl font-bold tracking-tight sm:text-4xl">Rechercher un article</h1>

        <form action="{{ $editorial->searchUrl() }}" method="GET" role="search" class="mt-6 flex gap-2">
            <label for="news-search-page" class="sr-only">Mots-clés</label>
            <input id="news-search-page" name="q" type="search" value="{{ $term }}" minlength="2" autofocus
                   placeholder="Ex. : endométriose, allaitement…"
                   class="min-w-0 flex-1 rounded-lg border border-[var(--news-line)] bg-[var(--news-surface)] px-4 py-3 text-lg focus:border-accent focus:outline-none">
            <button type="submit" class="rounded-lg bg-accent px-6 font-semibold text-white hover:bg-accent-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                Rechercher
            </button>
        </form>

        <div class="mt-10" aria-live="polite">
            @if ($articles === null)
                @if (filled($term))
                    <p class="text-[var(--news-muted)]">Saisissez au moins 2 caractères.</p>
                @endif
            @elseif ($articles->isEmpty())
                <div class="rounded-xl border border-dashed border-[var(--news-line)] px-6 py-16 text-center">
                    <p class="font-serif text-xl font-bold">Aucun article ne correspond à « {{ $term }} ».</p>
                    <p class="mt-2 text-[var(--news-muted)]">Essayez un autre mot-clé, ou parcourez nos rubriques.</p>
                    <ul class="mt-6 flex flex-wrap justify-center gap-2">
                        @foreach ($newsCategories as $category)
                            <li><x-news.category-badge :category="$category" size="md" /></li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="mb-6 text-sm text-[var(--news-muted)]">
                    {{ trans_choice(':count résultat|:count résultats', $articles->total()) }} pour « <strong class="text-current">{{ $term }}</strong> »
                </p>
                <div class="divide-y divide-[var(--news-line)]">
                    @foreach ($articles as $article)
                        <x-news.card :article="$article" variant="compact" :highlight="$term" :heading-level="2" class="py-5" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $articles->links() }}</div>
            @endif
        </div>
    </div>
@endsection

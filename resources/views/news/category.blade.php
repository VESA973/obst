{{-- Page d'une rubrique (Actualités ou Santé de la femme) : en-tête, article mis en avant, grille paginée avec « Charger plus ». --}}
@extends('layouts.news')

@php
    $color = \App\Support\NewsBlocks::color($category);
    $headingTitle = $category->title ?: $category->name;
    $metaDescription = $category->description ?: 'Tous les articles de la rubrique '.$category->name.'.';
    $lead = $articles->onFirstPage() ? $articles->first() : null;
@endphp

@section('title', $category->name.' — '.$editorial->title.' | La Quinzaine Obstétricale')
@section('description', \Illuminate\Support\Str::limit($metaDescription, 160))

@section('news')
    <x-news.seo :title="$category->name.' — '.$editorial->title" :description="$metaDescription"
                :canonical="$articles->onFirstPage() ? \App\Support\EditorialSection::categoryUrl($category) : $articles->url($articles->currentPage())"
                :image="$category->image_path ?? $lead?->image_path"
                :prev="$articles->previousPageUrl()" :next="$articles->nextPageUrl()" />

    <nav aria-label="Fil d'Ariane" class="mb-6 text-sm text-[var(--news-muted)]">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home') }}" class="hover:underline">Accueil</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ $editorial->homeUrl() }}" class="hover:underline">{{ $editorial->title }}</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="font-medium text-current">{{ $category->name }}</li>
        </ol>
    </nav>

    {{-- En-tête de rubrique --}}
    <header style="--cat: {{ $color }}" class="relative isolate mb-12 overflow-hidden rounded-2xl bg-[var(--cat)] px-6 py-12 text-white sm:px-10 sm:py-16">
        @if ($category->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image_path) }}" alt=""
                 class="absolute inset-0 -z-10 size-full object-cover opacity-30 mix-blend-luminosity">
        @endif
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[var(--cat)] via-[color-mix(in_srgb,var(--cat)_80%,transparent)] to-transparent" aria-hidden="true"></div>
        <p class="text-xs font-bold uppercase tracking-widest text-white/80">Rubrique</p>
        <h1 class="mt-2 font-serif text-4xl font-bold tracking-tight sm:text-5xl">{{ $headingTitle }}</h1>
        @if ($category->description)
            <p class="mt-4 max-w-2xl text-lg text-white/90">{{ $category->description }}</p>
        @endif
        <p class="mt-6 text-sm text-white/80">{{ trans_choice(':count article|:count articles', $articles->total()) }}</p>
    </header>

    @if ($articles->isEmpty())
        <p class="rounded-xl border border-dashed border-[var(--news-line)] px-6 py-16 text-center text-[var(--news-muted)]">
            Aucun article dans cette rubrique pour le moment.
        </p>
    @else
        @if ($lead)
            <x-news.card :article="$lead" variant="featured" :eager="true" :heading-level="2" :wide="true" class="mb-14" />
        @endif

        <section aria-label="Articles de la rubrique {{ $category->name }}">
            <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-news-list>
                @include('news.partials.card-grid', ['skipFirst' => true])
            </div>

            @if ($articles->hasMorePages())
                <div class="mt-12 text-center" data-load-more-container>
                    {{-- Sans JavaScript, le lien mène simplement à la page suivante. --}}
                    <a href="{{ $articles->nextPageUrl() }}" data-load-more
                       class="inline-flex items-center gap-2 rounded-full border-2 border-accent px-6 py-2.5 font-semibold text-accent transition-colors hover:bg-accent hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:text-accent-light dark:hover:text-white">
                        Charger plus d'articles
                    </a>
                </div>
            @endif

            @if (! $articles->onFirstPage())
                <div class="mt-8">{{ $articles->links() }}</div>
            @endif
        </section>
    @endif
@endsection

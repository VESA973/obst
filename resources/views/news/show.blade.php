{{-- Page article : fil d'Ariane, en-tête éditorial, contenu, partage, précédent / suivant, à lire aussi. --}}
@extends('layouts.news')

@php
    $url = $article->publicUrl();
    $metaTitle = $article->meta_title ?: $article->title;
    $metaDescription = $article->meta_description ?: $article->summary(160);
    $wasUpdated = $article->updated_at->gt($article->published_at->copy()->addHour());
    $photos = $article->assets->where('type', 'photo');
@endphp

@section('title', $metaTitle.' | La Quinzaine Obstétricale')
@section('description', $metaDescription)
@section('reading_progress', true)

@section('news')
    <x-news.seo :title="$metaTitle" :description="$metaDescription" :canonical="$url"
                :image="$article->image_path" :article="$article" />

    <article class="mx-auto max-w-4xl" data-reading-article>
        <nav aria-label="Fil d'Ariane" class="mb-8 text-sm text-[var(--news-muted)]">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:underline">Accueil</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ $editorial->homeUrl() }}" class="hover:underline">{{ $editorial->title }}</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ \App\Support\EditorialSection::categoryUrl($article->category) }}" class="hover:underline">{{ $article->category->name }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="line-clamp-1 max-w-[16rem] font-medium text-current sm:max-w-md">{{ $article->title }}</li>
            </ol>
        </nav>

        <header class="mx-auto max-w-3xl">
            <x-news.category-badge :category="$article->category" size="md" />
            <h1 class="mt-4 font-serif text-3xl font-bold leading-tight tracking-tight sm:text-5xl sm:leading-[1.1]">{{ $article->title }}</h1>

            @if ($article->excerpt)
                <p class="mt-5 text-lg leading-relaxed text-[var(--news-muted)] sm:text-xl">{{ $article->excerpt }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 border-y border-[var(--news-line)] py-4 text-sm">
                <span class="font-semibold">Par {{ $article->author?->name ?? 'La Quinzaine Obstétricale' }}</span>
                <span class="text-[var(--news-muted)]">
                    Publié le <time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->translatedFormat('j F Y à H\hi') }}</time>
                </span>
                @if ($wasUpdated)
                    <span class="text-[var(--news-muted)]">
                        Mis à jour le <time datetime="{{ $article->updated_at->toIso8601String() }}">{{ $article->updated_at->translatedFormat('j F Y à H\hi') }}</time>
                    </span>
                @endif
                <span class="text-[var(--news-muted)]">{{ $article->readingTime() }} min de lecture</span>
            </div>
        </header>

        @if ($article->image_path)
            <figure class="my-10">
                <div class="aspect-video overflow-hidden rounded-xl">
                    <x-news.image :article="$article" :eager="true" sizes="(min-width: 896px) 896px, 100vw" />
                </div>
                @if ($article->source_name)
                    <figcaption class="mt-2 text-sm text-[var(--news-muted)]">Source : {{ $article->source_name }}</figcaption>
                @endif
            </figure>
        @endif

        {{--
            Santé de la femme : colonne de lecture de même largeur que le contenu ; le sommaire se place
            en tête sur mobile, et dans la marge gauche (collé en haut) sur grand écran, sans décaler le texte.
        --}}
        @if ($editorial->health)
            <div class="relative mx-auto max-w-[70ch] text-lg">
                <aside class="mb-10 text-base xl:absolute xl:right-full xl:top-0 xl:mb-0 xl:mr-8 xl:h-full xl:w-48">
                    <div class="xl:sticky xl:top-24">
                        <x-news.toc />
                    </div>
                </aside>
        @endif

        {{-- Contenu issu de l'éditeur riche, nettoyé avant affichage. --}}
        <div data-article-body @class(['news-article-body prose prose-lg mx-auto dark:prose-invert prose-headings:font-serif prose-a:text-accent prose-blockquote:border-l-rose dark:prose-a:text-accent-light', 'max-w-none' => $editorial->health, 'max-w-[70ch]' => ! $editorial->health])>
            @if ($article->body)
                {!! str($article->body)->sanitizeHtml() !!}
            @elseif ($article->excerpt)
                <p>{{ $article->excerpt }}</p>
            @endif

            @if ($article->external_url)
                <p>
                    <a href="{{ $article->external_url }}" target="_blank" rel="noopener noreferrer">
                        Lire l'article complet{{ $article->source_name ? ' sur '.$article->source_name : '' }} ↗
                    </a>
                </p>
            @endif
        </div>

        @if ($editorial->health)
                <aside role="note" class="mt-10 flex gap-3 rounded-xl text-base border border-[var(--news-line)] bg-[var(--news-surface)] p-5 text-sm leading-relaxed text-[var(--news-muted)]">
                        <svg class="mt-0.5 size-5 shrink-0 text-accent dark:text-accent-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.7 9a1 1 0 0 1-.7 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.7a1.2 1.2 0 0 1 1.6 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1Z"/><path d="m9 12 2 2 4-4"/></svg>
                    <p>Ces informations ne remplacent pas l'avis d'un professionnel de santé. En cas de doute ou de symptôme inhabituel, consultez votre médecin ou votre sage-femme.</p>
                </aside>
            </div>
        @endif

        @if ($photos->isNotEmpty())
            <section aria-label="Photos" class="mx-auto mt-10 grid max-w-3xl grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($photos as $photo)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->path) }}" target="_blank" rel="noopener"
                       class="block aspect-square overflow-hidden rounded-lg focus-visible:outline-2 focus-visible:outline-accent">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo->path) }}" alt="Photo {{ $loop->iteration }} — {{ $article->title }}"
                             loading="lazy" class="size-full object-cover transition-transform duration-500 hover:scale-105">
                    </a>
                @endforeach
            </section>
        @endif

        <x-news.share :url="$url" :title="$article->title" class="mx-auto mt-12 max-w-3xl border-t border-[var(--news-line)] pt-6" />

        {{-- Article précédent / suivant dans la rubrique --}}
        @if ($previous || $next)
            <nav aria-label="Autres articles de la rubrique" class="mx-auto mt-10 grid max-w-3xl gap-4 sm:grid-cols-2">
                @if ($previous)
                    <a href="{{ $previous->publicUrl() }}" rel="prev" class="group rounded-xl border border-[var(--news-line)] p-5 transition-colors hover:border-accent">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[var(--news-muted)]">← Article précédent</span>
                        <span class="mt-2 line-clamp-2 block font-serif font-semibold group-hover:text-accent dark:group-hover:text-accent-light">{{ $previous->title }}</span>
                    </a>
                @endif
                @if ($next)
                    <a href="{{ $next->publicUrl() }}" rel="next" class="group rounded-xl border border-[var(--news-line)] p-5 text-right transition-colors hover:border-accent sm:col-start-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[var(--news-muted)]">Article suivant →</span>
                        <span class="mt-2 line-clamp-2 block font-serif font-semibold group-hover:text-accent dark:group-hover:text-accent-light">{{ $next->title }}</span>
                    </a>
                @endif
            </nav>
        @endif
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="a-lire-aussi" class="mt-20 border-t border-[var(--news-line)] pt-10">
            <x-news.section-heading title="À lire aussi" id="a-lire-aussi"
                                    :href="\App\Support\EditorialSection::categoryUrl($article->category)"
                                    :color="\App\Support\NewsBlocks::color($article->category)" />
            <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-news.card :article="$item" class="news-reveal" />
                @endforeach
            </div>
        </section>
    @endif
@endsection

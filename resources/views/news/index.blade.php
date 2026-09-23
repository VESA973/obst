{{-- Accueil d'une section (Actualités ou Santé de la femme) : à la une, derniers articles, une section par rubrique, barre latérale. --}}
@extends('layouts.news')

@section('title', $editorial->title.' | La Quinzaine Obstétricale')
@section('description', $editorial->description)

@section('news')
    <x-news.seo :title="$editorial->title.' — La Quinzaine Obstétricale'" :description="$editorial->description"
                :canonical="$editorial->homeUrl()" :image="$featured?->image_path" />

    <h1 class="sr-only">{{ $editorial->title }}</h1>

    @if (! $featured)
        <div class="grid place-items-center rounded-xl border border-dashed border-[var(--news-line)] px-6 py-24 text-center">
            <p class="font-serif text-2xl font-bold">Aucun article publié pour le moment.</p>
            <p class="mt-2 text-[var(--news-muted)]">Revenez bientôt : nos prochaines actualités arrivent.</p>
        </div>
    @else
        {{-- À la une --}}
        <section aria-labelledby="a-la-une" class="grid gap-6 lg:grid-cols-12">
            <h2 id="a-la-une" class="sr-only">À la une</h2>
            <x-news.card :article="$featured" variant="featured" :eager="true" :heading-level="2" class="lg:col-span-8" />

            @if ($secondary->isNotEmpty())
                <div class="flex flex-col divide-y divide-[var(--news-line)] lg:col-span-4">
                    @foreach ($secondary as $article)
                        <x-news.card :article="$article" variant="compact" class="py-4 first:pt-0 last:pb-0" />
                    @endforeach
                </div>
            @endif
        </section>

        <div class="mt-14 grid gap-12 lg:grid-cols-12">
            <div class="space-y-16 lg:col-span-8 xl:col-span-9">
                {{-- Dernières actualités --}}
                @if ($latest->isNotEmpty())
                    <section aria-labelledby="dernieres-actualites">
                        <x-news.section-heading :title="$editorial->health ? 'Derniers articles' : 'Dernières actualités'" id="dernieres-actualites" />
                        <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($latest as $article)
                                <x-news.card :article="$article" class="news-reveal" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Une section par rubrique --}}
                @foreach ($sections as $section)
                    @php($color = \App\Support\NewsBlocks::color($section['category']))
                    <section aria-labelledby="rubrique-{{ $section['category']->slug }}" class="news-reveal">
                        <x-news.section-heading :title="$section['category']->title ?: $section['category']->name"
                                                :id="'rubrique-'.$section['category']->slug"
                                                :href="\App\Support\EditorialSection::categoryUrl($section['category'])"
                                                :color="$color" />
                        <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($section['articles'] as $article)
                                <x-news.card :article="$article" />
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <x-news.sidebar :recent="$recent" :categories="$newsCategories" class="lg:col-span-4 xl:col-span-3 lg:sticky lg:top-24 lg:self-start" />
        </div>
    @endif
@endsection

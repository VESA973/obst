{{--
    Agenda (/agenda) : hero compact (réglages « Pages du site »), prochain événement, filtres,
    vue liste groupée par mois ou calendrier mensuel, événements passés repliables.
    Script : resources/js/agenda.js (cette page et la fiche uniquement).
--}}
@extends('layouts.site')

@php
    $page = \App\Models\PageSetting::forKey('agenda');
    $heroImage = ! empty($page['hero_image_path'])
        ? (str_starts_with($page['hero_image_path'], 'images/') ? asset($page['hero_image_path']) : Storage::url($page['hero_image_path']))
        : null;

    $featured = $upcoming->first();

    // Options des filtres : uniquement les valeurs présentes dans les événements à venir.
    $types = $upcoming->mapWithKeys(fn ($item) => [$item->type()['key'] => $item->type()])->sortBy('label')->all();
    $communes = $upcoming->map->commune()->filter()->unique()->sort()->mapWithKeys(fn ($commune) => [$commune => ['label' => $commune]])->all();
    $months = $upcoming->map->startsAt()->filter()->mapWithKeys(fn ($date) => [$date->format('Y-m') => ['label' => ucfirst($date->translatedFormat('F Y'))]])->all();

    $byMonth = $upcoming->groupBy(fn ($item) => $item->startsAt()?->format('Y-m') ?? 'a-venir');
@endphp

@section('title', 'Agenda | La Quinzaine Obstétricale')
@section('description', \Illuminate\Support\Str::limit($page['description'] ?? 'Les prochains événements de l\'association en Guyane.', 160))
@section('body_class', 'home-page agenda-page')

@section('content')
    <x-news.seo :title="($page['title'] ?? 'Agenda').' — La Quinzaine Obstétricale'" :description="$page['description'] ?? null" :canonical="route('agenda')" />

    {{-- Hero compact --}}
    <section class="assoc-hero agenda-hero" aria-labelledby="agenda-title">
        @if ($heroImage)
            <img class="assoc-img assoc-hero-img" src="{{ $heroImage }}" alt="" fetchpriority="high">
        @else
            <div class="assoc-img assoc-img--placeholder assoc-hero-img"></div>
        @endif
        <div class="assoc-hero-overlay" aria-hidden="true"></div>
        <x-home.leaf-pattern variant="fern" class="home-leaf home-leaf--hero-left" />
        <div class="home-container assoc-hero-content">
            <nav aria-label="Fil d'Ariane" class="assoc-breadcrumb home-appear">
                <ol><li><a href="{{ route('home') }}">Accueil</a></li><li aria-current="page">Agenda</li></ol>
            </nav>
            <p class="eyebrow home-appear" style="--delay: 80ms">{{ $page['eyebrow'] ?? 'Agenda' }}</p>
            <h1 id="agenda-title" class="home-appear assoc-title-{{ $page['title_size'] ?? 'normal' }}" style="--delay: 160ms">{{ $page['title'] ?? 'Agenda' }}</h1>
            @if (! empty($page['description']))
                <p class="lead home-appear" style="--delay: 240ms">{{ $page['description'] }}</p>
            @endif
        </div>
        <x-home.wave-divider fill="var(--mint)" class="home-wave--hero" />
    </section>

    <div class="home-tinted agenda-main">
        <div class="home-container">
            @if ($featured)
                <x-agenda.featured-event :item="$featured" />
            @endif

            <section class="agenda-upcoming" aria-labelledby="agenda-upcoming-title">
                <x-home.section-title eyebrow="À venir" title="Les prochains événements" id="agenda-upcoming-title" />

                @if ($upcoming->isNotEmpty())
                    <x-agenda.filters :types="$types" :communes="$communes" :months="$months" />

                    {{-- Vue liste (par défaut) : un groupe par mois. --}}
                    <div class="agenda-list" data-agenda-list>
                        @foreach ($byMonth as $month => $items)
                            <section class="agenda-month" data-agenda-month aria-labelledby="mois-{{ $month }}">
                                <h3 id="mois-{{ $month }}" class="agenda-month-title">
                                    {{ $month === 'a-venir' ? 'Date à venir' : ucfirst($items->first()->startsAt()->translatedFormat('F Y')) }}
                                </h3>
                                <div class="agenda-month-items">
                                    @foreach ($items as $item)
                                        <x-agenda.event-card :item="$item" />
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <x-agenda.calendar />

                    {{-- Aucun résultat pour les filtres choisis. --}}
                    <div class="agenda-empty" data-agenda-empty hidden>
                        <x-home.leaf-pattern variant="liana" class="agenda-empty-leaf" />
                        <p class="agenda-empty-title">Aucun événement ne correspond à votre recherche.</p>
                        <p>Essayez un autre mot-clé ou <button type="button" class="agenda-link-button" data-filter-reset>réinitialisez les filtres</button>.</p>
                    </div>
                @else
                    <div class="agenda-empty">
                        <x-home.leaf-pattern variant="liana" class="agenda-empty-leaf" />
                        <p class="agenda-empty-title">Aucun événement n'est programmé pour le moment.</p>
                        <p>Revenez bientôt, ou <a href="{{ route('contact') }}">écrivez-nous</a> pour être informé des prochaines dates.</p>
                    </div>
                @endif
            </section>

            {{-- Événements passés : repliés, style atténué. --}}
            @if ($past->isNotEmpty())
                <details class="agenda-past">
                    <summary>
                        <span>Événements passés</span>
                        <span class="agenda-past-count">{{ $past->count() }}</span>
                    </summary>
                    <div class="agenda-month-items">
                        @foreach ($past as $item)
                            <x-agenda.event-card :item="$item" :past="true" />
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    </div>
@endsection

@push('head')
    @stack('news-head')
    <script>
        if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('home-has-reveal');
        }
    </script>
    @vite('resources/js/agenda.js')
@endpush

{{--
    Page « L'association » (/qui-sommes-nous). Textes : contenus existants de la page et des réglages
    « Pages du site » ; photos : Filament > Contenus > Page L'association ; chiffres et frise : config/association.php.
    Même langage visuel que l'accueil (composants x-home.*). Script : resources/js/association.js (cette page uniquement).
--}}
@extends('layouts.site')

@php
    $page = \App\Models\PageSetting::forKey('about');
    $actionsPage = \App\Models\PageSetting::forKey('actions');
    ['slots' => $images, 'gallery' => $gallery] = \App\Models\AssociationPageImage::forPage();

    // Bannière : photo de la page association, sinon image d'en-tête de « Pages du site ».
    $heroImage = $images->get('hero');
    $fallbackHero = ! $heroImage && ! empty($page['hero_image_path'])
        ? (str_starts_with($page['hero_image_path'], 'images/') ? asset($page['hero_image_path']) : Storage::url($page['hero_image_path']))
        : null;

    // Icônes au trait (style Lucide) pour les cinq missions existantes.
    $missions = [
        ['title' => 'Former les professionnels', 'text' => 'Congrès, formations et ateliers pour renforcer les pratiques et soutenir la montée en compétence des équipes.', 'icon' => '<path d="M21.42 10.92a1 1 0 0 0-.02-1.84L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0Z"/><path d="M22 10v6M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>'],
        ['title' => 'Informer les publics', 'text' => 'Rendre accessibles les enjeux de la santé de la femme au grand public, aux familles et aux acteurs de santé.', 'icon' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>'],
        ['title' => 'Sensibiliser aux enjeux majeurs', 'text' => 'Périnatalité, endométriose, SOPK, fibromes, ménopause, cancers féminins, santé mentale et prévention.', 'icon' => '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/><path d="M3.2 12H9l.5-1 2 4.5 2-7 1.5 3.5h5.3"/>'],
        ['title' => 'Favoriser la recherche', 'text' => "Encourager l'innovation et les partenariats au service d'une médecine plus performante et plus humaine.", 'icon' => '<path d="M6 18h8M3 22h18M14 22a7 7 0 1 0 0-14h-1M9 14h2M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2Z"/><path d="M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3"/>'],
        ['title' => "Améliorer l'organisation des soins", 'text' => 'Contribuer à une santé plus équitable en Guyane en rapprochant les acteurs et les ressources du territoire.', 'icon' => '<circle cx="12" cy="5" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M10.4 7.6 6.6 16.4M13.6 7.6l3.8 8.8M8 19h8"/>'],
    ];
@endphp

@section('title', "L'association | La Quinzaine Obstétricale")
@section('description', \Illuminate\Support\Str::limit($page['description'] ?? '', 160))
@section('body_class', 'home-page assoc-page')

@section('content')
    <x-news.seo :title="$page['title'].' — La Quinzaine Obstétricale'" :description="$page['description'] ?? null"
                :canonical="route('about')" :image="$heroImage?->path" />

    {{-- Hero : image modifiable, overlay aux couleurs du site, fil d'Ariane, vague. --}}
    <section class="assoc-hero" aria-labelledby="assoc-hero-title">
        @if ($heroImage)
            <x-association.image :image="$heroImage" :eager="true" class="assoc-hero-img" />
        @elseif ($fallbackHero)
            <img class="assoc-img assoc-hero-img" src="{{ $fallbackHero }}" alt="" fetchpriority="high">
        @else
            <x-association.image class="assoc-hero-img" />
        @endif
        <div class="assoc-hero-overlay" aria-hidden="true"></div>
        <x-home.leaf-pattern variant="fern" class="home-leaf home-leaf--hero-left" />

        <div class="home-container assoc-hero-content">
            <nav aria-label="Fil d'Ariane" class="assoc-breadcrumb home-appear">
                <ol>
                    <li><a href="{{ route('home') }}">Accueil</a></li>
                    <li aria-current="page">{{ $page['menu_label'] ?? "L'association" }}</li>
                </ol>
            </nav>
            <p class="eyebrow home-appear" style="--delay: 80ms">{{ $page['eyebrow'] ?? "L'association" }}</p>
            {{-- Taille du titre : réglage « Pages du site ». Espace insécable avant « ? ! : ; » (typographie française). --}}
            <h1 id="assoc-hero-title" class="home-appear assoc-title-{{ $page['title_size'] ?? 'normal' }}" style="--delay: 160ms">{!! preg_replace('/ ([?!:;])/u', '&nbsp;$1', e($page['title'] ?? 'Qui sommes-nous ?')) !!}</h1>
            @if (! empty($page['description']))
                <p class="lead home-appear" style="--delay: 240ms">{{ $page['description'] }}</p>
            @endif
        </div>

        <x-home.wave-divider fill="var(--mint)" class="home-wave--hero" />
    </section>

    {{-- Notre mission (texte existant « Notre ambition ») : texte + image, mise en page asymétrique. --}}
    <section class="home-section home-tinted assoc-split home-reveal" aria-labelledby="assoc-mission-title">
        <x-home.leaf-pattern variant="liana" class="home-leaf home-leaf--about" />
        <div class="home-container assoc-split-grid">
            <div class="assoc-split-text">
                <p class="home-eyebrow">Notre ambition</p>
                <h2 id="assoc-mission-title">Faire de la Guyane un territoire d'excellence en santé de la femme, en périnatalité et en prévention.</h2>
                <p class="home-section-intro">Nous plaçons la formation, la coopération et l'innovation au cœur de nos actions pour construire une santé plus accessible, plus performante et plus humaine.</p>
            </div>
            <figure class="assoc-split-media">
                <x-association.image :image="$images->get('mission')" sizes="(min-width: 900px) 45vw, 100vw" leaf="palm" />
                @if ($images->get('mission')?->caption)
                    <figcaption>{{ $images->get('mission')->caption }}</figcaption>
                @endif
            </figure>
        </div>
    </section>

    <x-home.wave-divider fill="var(--white)" class="home-wave--on-tinted" />

    {{-- Nos missions : cinq cartes avec icônes. --}}
    <section class="home-section home-reveal" aria-labelledby="assoc-missions-title">
        <div class="home-container">
            <x-home.section-title eyebrow="Nos missions" title="Former, informer, sensibiliser et faire coopérer" id="assoc-missions-title" />
            <div class="assoc-values">
                @foreach ($missions as $mission)
                    <x-association.value-card :number="sprintf('%02d', $loop->iteration)" :title="$mission['title']" :icon="$mission['icon']">
                        {{ $mission['text'] }}
                    </x-association.value-card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Chiffres clés (config/association.php) : compteurs animés au défilement. --}}
    <section class="assoc-stats home-reveal" aria-label="Chiffres clés">
        <x-home.leaf-pattern variant="fern" class="home-leaf home-leaf--stats" />
        <div class="home-container assoc-stats-grid">
            @foreach (config('association.stats') as $stat)
                <x-association.stat-counter :value="$stat['value']" :suffix="$stat['suffix']" :label="$stat['label']" />
            @endforeach
        </div>
    </section>

    {{-- Notre histoire (texte existant) : image + texte, frise si des étapes sont renseignées. --}}
    <section class="home-section assoc-split assoc-split--reverse home-reveal" aria-labelledby="assoc-history-title">
        <div class="home-container assoc-split-grid">
            <figure class="assoc-split-media">
                <x-association.image :image="$images->get('history')" sizes="(min-width: 900px) 45vw, 100vw" leaf="fern" />
                <div class="assoc-years-badge" aria-hidden="true"><span>Depuis</span><strong>15 ans</strong></div>
                @if ($images->get('history')?->caption)
                    <figcaption>{{ $images->get('history')->caption }}</figcaption>
                @endif
            </figure>
            <div class="assoc-split-text">
                <p class="home-eyebrow">Notre histoire</p>
                <h2 id="assoc-history-title">Agir durablement pour la santé sur le territoire guyanais</h2>
                <p class="home-section-intro">Depuis plus de 15 ans, nous développons des actions de formation, d'information, de prévention et de recherche afin d'améliorer la qualité des soins et de réduire les inégalités d'accès à la santé sur notre territoire.</p>
            </div>
        </div>
        <div class="home-container">
            <x-association.timeline :steps="config('association.timeline')" />
        </div>
    </section>

    <x-home.wave-divider fill="var(--mint)" />

    {{-- Nos actions en Guyane : textes de la page « Nos actions » (Pages du site) + image. --}}
    <section class="home-section home-tinted assoc-split home-reveal" aria-labelledby="assoc-actions-title">
        <x-home.leaf-pattern variant="palm" class="home-leaf home-leaf--health" />
        <div class="home-container assoc-split-grid">
            <div class="assoc-split-text">
                <p class="home-eyebrow">{{ $actionsPage['eyebrow'] ?? 'Nos actions' }}</p>
                <h2 id="assoc-actions-title">{{ $actionsPage['title'] ?? 'Nos actions' }}</h2>
                @if (! empty($actionsPage['description']))
                    <p class="home-section-intro">{{ $actionsPage['description'] }}</p>
                @endif
                <a class="home-link-arrow" href="{{ route('actions') }}">
                    Découvrir nos actions
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>
            <figure class="assoc-split-media">
                <x-association.image :image="$images->get('actions')" sizes="(min-width: 900px) 45vw, 100vw" leaf="liana" />
                @if ($images->get('actions')?->caption)
                    <figcaption>{{ $images->get('actions')->caption }}</figcaption>
                @endif
            </figure>
        </div>
    </section>

    {{-- Galerie (masquée tant qu'aucune photo visible n'est enregistrée). --}}
    @if ($gallery->isNotEmpty())
        <x-home.wave-divider fill="var(--white)" class="home-wave--on-tinted" />
        <section class="home-section home-reveal" aria-labelledby="assoc-gallery-title">
            <div class="home-container">
                <x-home.section-title eyebrow="En images" title="Galerie" id="assoc-gallery-title" />
                <x-association.gallery :images="$gallery" />
            </div>
        </section>
    @endif

    <x-association.cta />
@endsection

@push('head')
    @stack('news-head')
    {{-- Données structurées : organisation à but non lucratif. --}}
    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NGO',
            'name' => 'La Quinzaine Obstétricale',
            'url' => route('home'),
            'logo' => asset('images/quinzaine-logo.png'),
            'description' => $page['description'] ?? null,
            'areaServed' => ['@type' => 'AdministrativeArea', 'name' => 'Guyane'],
            'image' => $heroImage ? \Illuminate\Support\Facades\Storage::disk('public')->url($heroImage->path) : null,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
    @vite('resources/js/association.js')
    <script>
        if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('home-has-reveal');
        }
    </script>
@endpush

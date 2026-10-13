{{--
    Accueil : hero (grande photo), accès rapides, « Qui sommes-nous ? », carrousels (actualités,
    santé des femmes, agenda), chiffres clés et engagement. Styles et carrousels : resources/js/home.js
    (chargé uniquement sur cette page). Couleurs : variables existantes du site.
--}}
@extends('layouts.site')

@section('title', 'Accueil | La Quinzaine Obstétricale')
@section('description', 'Informer, former, innover et agir pour la santé de la femme et de la périnatalité en Guyane.')
@section('body_class', 'home-page')

@push('head')
    {{-- Animations d'apparition activées avant le premier affichage (sans JS : tout reste visible). --}}
    <script>
        if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('home-has-reveal');
        }
    </script>
    @vite('resources/js/home.js')
@endpush

@section('content')
<x-home.hero />

<div class="home-tinted">
    <x-home.quick-access />

    {{-- Qui sommes-nous ? (texte existant) --}}
    <section class="home-section home-about" aria-labelledby="home-about-title">
        <x-home.leaf-pattern variant="liana" class="home-leaf home-leaf--about" />
        <div class="home-container home-about-grid">
            <div class="home-about-heading">
                <span class="pulse-icon"></span>
                <p class="home-eyebrow">L'association</p>
                <h2 id="home-about-title">Qui sommes-nous ?</h2>
                <a class="home-link-arrow" href="{{ route('about') }}">
                    Découvrir l'association
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>
            <ul class="home-about-lines">
                <li>La Quinzaine Obstétricale est une association guyanaise mobilisée pour la santé de la femme et la périnatalité.</li>
                <li>Elle rassemble professionnels, partenaires et citoyens autour de l'information, de la prévention et de la formation.</li>
                <li>Elle rend les savoirs plus accessibles et soutient les parcours de soin.</li>
                <li>Elle encourage l'innovation locale et les actions adaptées au territoire.</li>
                <li>Elle agit au plus près des réalités de la Guyane, pour le public comme pour les professionnels.</li>
            </ul>
        </div>
    </section>
</div>

<x-home.wave-divider fill="var(--white)" class="home-wave--on-tinted" />

{{-- Actualités : carrousel principal (défilement automatique lent). --}}
<section class="home-section home-reveal" aria-labelledby="home-news-title">
    <div class="home-container">
        <x-home.section-title eyebrow="Actualités" title="Les actualités" id="home-news-title" :href="route('news')" link-label="Voir toutes les actualités">
            Tribunes, interviews et publications de l'association.
        </x-home.section-title>

        <x-home.carousel label="Dernières actualités" :autoplay="true">
            @forelse ($latestArticles as $article)
                <div class="swiper-slide">
                    <x-home.card :article="$article" :href="$article->publicUrl()" :title="$article->title"
                                 :badge="$article->display_category" :text="$article->summary(130)"
                                 :meta="$article->published_at->translatedFormat('j F Y').' · '.$article->readingTime().' min de lecture'" />
                </div>
            @empty
                <div class="swiper-slide">
                    <x-home.card badge="Tribune" title="Parler santé de la femme en Guyane" text="Regards croisés sur les besoins, les freins d'accès et les leviers d'action." />
                </div>
                <div class="swiper-slide">
                    <x-home.card badge="Interview" title="Voix de terrain" text="Professionnels et partenaires partagent leurs expériences de prévention." />
                </div>
                <div class="swiper-slide">
                    <x-home.card badge="Publication" title="Nouvelles ressources" text="Supports simples pour accompagner les grandes étapes de la vie des femmes." />
                </div>
            @endforelse
        </x-home.carousel>
    </div>
</section>

{{-- Santé des femmes en Guyane : fiches existantes de la section « Santé de la femme ». --}}
@if ($healthArticles->isNotEmpty())
    <x-home.wave-divider fill="var(--mint)" />
    <section class="home-section home-tinted home-reveal" aria-labelledby="home-health-title">
        <x-home.leaf-pattern variant="palm" class="home-leaf home-leaf--health" />
        <div class="home-container">
            <x-home.section-title eyebrow="Santé des femmes en Guyane" title="Des repères pour chaque étape" id="home-health-title" :href="route('public')" link-label="Toutes les fiches santé">
                Des fiches simples, classées par thème, pour mieux comprendre et savoir quand consulter.
            </x-home.section-title>

            @if ($healthRubriques->isNotEmpty())
                <ul class="home-chips" aria-label="Thèmes santé">
                    @foreach ($healthRubriques as $rubrique)
                        <li><a href="{{ route('public.category', $rubrique) }}">{{ $rubrique->name }} <span>{{ $rubrique->articles_count }}</span></a></li>
                    @endforeach
                </ul>
            @endif

            <x-home.carousel label="Fiches santé">
                @foreach ($healthArticles as $article)
                    <div class="swiper-slide">
                        <x-home.card :article="$article" :href="$article->publicUrl()" :title="$article->title"
                                     :badge="$article->category?->name" :text="$article->summary(120)"
                                     :meta="$article->readingTime().' min de lecture'" />
                    </div>
                @endforeach
            </x-home.carousel>
        </div>
    </section>
    <x-home.wave-divider fill="var(--white)" class="home-wave--on-tinted" />
@endif

{{-- Agenda (contenu existant, en carrousel). --}}
<section class="home-section home-reveal" aria-labelledby="home-agenda-title">
    <div class="home-container">
        <x-home.section-title eyebrow="Agenda" title="Les prochains événements" id="home-agenda-title" :href="route('agenda')" link-label="Voir tout l'agenda" />

        <x-home.carousel label="Prochains événements">
            @forelse ($upcomingEvents as $event)
                <div class="swiper-slide">
                    <x-home.card :href="route('agenda')" :title="$event->title" :text="\Illuminate\Support\Str::limit($event->description, 140)"
                                 :date="$event->event_date"
                                 :image-url="$event->flyer_is_image ? \Illuminate\Support\Facades\Storage::url($event->image_path) : null"
                                 :image-position="$event->flyer_position"
                                 :badge="$event->event_date ? $event->event_date->translatedFormat('d M') : 'À venir'"
                                 :meta="$event->location" />
                </div>
            @empty
                <div class="swiper-slide">
                    <x-home.card badge="Sept." title="Les Jeudis M" text="Rencontre thématique autour de la ménopause et de la qualité de vie." />
                </div>
                <div class="swiper-slide">
                    <x-home.card badge="Oct." title="Prévention HPV" text="Temps d'information, vaccination et ressources pour les familles." />
                </div>
                <div class="swiper-slide">
                    <x-home.card badge="Nov." title="Assises Amazoniennes" text="Échanges entre acteurs de santé, chercheurs, institutions et associations." />
                </div>
            @endforelse
        </x-home.carousel>
    </div>
</section>

{{-- Chiffres clés (config/association.php, partagés avec la page « L'association »). --}}
<section class="stats-band home-stats home-reveal" aria-label="Chiffres clés">
    <x-home.leaf-pattern variant="fern" class="home-leaf home-leaf--stats" />
    @foreach (collect(config('association.stats'))->where('on_home', true) as $stat)
        <div><strong>{{ $stat['value'] }}{{ $stat['suffix'] }}</strong><span>{{ $stat['label'] }}</span></div>
    @endforeach
</section>

{{-- Agir avec l'association (contenu existant). --}}
<section class="home-section home-engage home-reveal" aria-labelledby="home-engage-title">
    <div class="home-container home-engage-grid">
        <div>
            <p class="home-eyebrow">Accès rapides</p>
            <h2 id="home-engage-title">Agir avec l'association</h2>
            <p class="home-section-intro">Adhésion, don, partenariat, bénévolat ou simple prise de contact : chaque engagement renforce les actions d'information et de prévention.</p>
        </div>
        <div class="home-engage-links">
            <a href="{{ route('actions') }}">Découvrir nos actions <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
            <a href="{{ route('public') }}">Consulter les fiches santé <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
            <a href="{{ route('join') }}">Nous soutenir <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
        </div>
    </div>
</section>
@endsection

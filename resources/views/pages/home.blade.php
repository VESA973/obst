@extends('layouts.site')

@section('title', 'Accueil | La Quinzaine Obstetricale')
@section('description', 'Informer, former, innover et agir pour la sante des femmes et de la perinatalite en Guyane.')

@section('content')
<section class="hero">
    <img src="{{ asset('images/guyane-hero.png') }}" alt="Paysage de Guyane avec foret amazonienne et fleuve">
    <div class="hero-content">
        <p class="eyebrow">Sante des femmes et perinatalite en Guyane</p>
        <h1>Informer. Former. Innover. Agir.</h1>
        <p class="lead">Ensemble pour une meilleure sante des femmes et de la perinatalite en Guyane.</p>
        <div class="hero-actions">
            <a class="btn primary" href="{{ route('join') }}">Adherer</a>
            <a class="btn secondary" href="{{ route('join') }}#don">Faire un don</a>
            <a class="btn secondary" href="{{ route('contact') }}">Nous contacter</a>
        </div>
    </div>
</section>

<section class="intro-band">
    <div>
        <span class="pulse-icon"></span>
        <h2>Qui sommes-nous ?</h2>
    </div>
    <div class="intro-lines">
        <p>La Quinzaine Obstetricale est une association guyanaise mobilisee pour la sante des femmes et la perinatalite.</p>
        <p>Elle rassemble professionnels, partenaires et citoyens autour de l'information, de la prevention et de la formation.</p>
        <p>Elle rend les savoirs plus accessibles et soutient les parcours de soin.</p>
        <p>Elle encourage l'innovation locale et les actions adaptees au territoire.</p>
        <p>Elle agit au plus pres des realites de la Guyane, pour le public comme pour les professionnels.</p>
    </div>
</section>

<section class="grid-section section-headed">
    <div class="section-title">
        <p class="eyebrow">Actualites</p>
        <h2>Les actualites</h2>
    </div>
    @forelse ($latestArticles as $article)
        <article class="feature-card">
            <span>{{ $article->category ?: ($article->external_url ? 'Article externe' : 'Actualite') }}</span>
            <h3>{{ $article->title }}</h3>
            <p>{{ $article->excerpt ?: Str::limit(strip_tags($article->body), 130) }}</p>
        </article>
    @empty
        <article class="feature-card">
            <span>Tribune</span>
            <h3>Parler sante des femmes en Guyane</h3>
            <p>Regards croises sur les besoins, les freins d'acces et les leviers d'action.</p>
        </article>
        <article class="feature-card">
            <span>Interview</span>
            <h3>Voix de terrain</h3>
            <p>Professionnels et partenaires partagent leurs experiences de prevention.</p>
        </article>
        <article class="feature-card">
            <span>Publication</span>
            <h3>Nouvelles ressources</h3>
            <p>Supports simples pour accompagner les grandes etapes de la vie des femmes.</p>
        </article>
    @endforelse
    <div class="section-action">
        <a class="btn secondary" href="{{ route('news') }}">Voir toutes les actualites</a>
    </div>
</section>

<section class="agenda-list home-events">
    <div class="section-title">
        <p class="eyebrow">Agenda</p>
        <h2>Les prochains evenements</h2>
    </div>
    @forelse ($upcomingEvents as $event)
        <article>
            <time>{{ $event->event_date ? $event->event_date->translatedFormat('d M') : 'A venir' }}</time>
            <div><h2>{{ $event->title }}</h2><p>{{ $event->description }}</p></div>
        </article>
    @empty
        <article>
            <time>Sept.</time>
            <div><h2>Les Jeudis M</h2><p>Rencontre thematique autour de la menopause et de la qualite de vie.</p></div>
        </article>
        <article>
            <time>Oct.</time>
            <div><h2>Prevention HPV</h2><p>Temps d'information, vaccination et ressources pour les familles.</p></div>
        </article>
        <article>
            <time>Nov.</time>
            <div><h2>Assises Amazoniennes</h2><p>Echanges entre acteurs de sante, chercheurs, institutions et associations.</p></div>
        </article>
    @endforelse
    <div class="section-action">
        <a class="btn secondary" href="{{ route('agenda') }}">Voir tout l'agenda</a>
    </div>
</section>

<section class="stats-band">
    <div><strong>8</strong><span>actions phares</span></div>
    <div><strong>11</strong><span>themes sante</span></div>
    <div><strong>6</strong><span>ressources pro</span></div>
    <div><strong>1</strong><span>territoire guyanais</span></div>
</section>

<section class="split-section">
    <div>
        <p class="eyebrow">Acces rapides</p>
        <h2>Agir avec l'association</h2>
        <p>Adhesion, don, partenariat, benevolat ou simple prise de contact : chaque engagement renforce les actions d'information et de prevention.</p>
    </div>
    <div class="link-list">
        <a href="{{ route('actions') }}">Decouvrir nos actions</a>
        <a href="{{ route('public') }}">Consulter les fiches sante</a>
        <a href="{{ route('join') }}">Nous soutenir</a>
    </div>
</section>
@endsection

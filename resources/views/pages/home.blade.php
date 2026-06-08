@extends('layouts.site')

@section('title', 'Accueil | La Quinzaine Obstétricale')
@section('description', 'Accueil de La Quinzaine Obstétricale, site d’information, de rencontre et de prévention autour de la santé obstétricale.')

@section('content')
<section class="hero">
    <img src="{{ asset('images/quinzaine-hero.png') }}" alt="Cabinet de consultation obstétricale lumineux">
    <div class="hero-content">
        <p class="eyebrow">Santé obstétricale, information et présence</p>
        <h1>La Quinzaine Obstétricale</h1>
        <p class="lead">Un espace clair pour accompagner les familles, soutenir les professionnels et faire circuler une information fiable, humaine et accessible.</p>
        <div class="hero-actions">
            <a class="btn primary" href="{{ route('public') }}">Espace public</a>
            <a class="btn secondary" href="{{ route('pro') }}">Espace pro</a>
        </div>
    </div>
</section>

<section class="intro-band">
    <div>
        <span class="pulse-icon"></span>
        <h2>Une présence bienveillante à chaque étape</h2>
    </div>
    <p>Prévention, grossesse, naissance, post-partum : nous réunissons des repères pratiques et des ressources pour faciliter le dialogue avec les soignants.</p>
</section>

<section class="grid-section">
    <article class="feature-card">
        <span>01</span>
        <h3>Informer sans inquiéter</h3>
        <p>Des contenus lisibles, hiérarchisés et orientés vers les décisions utiles au quotidien.</p>
    </article>
    <article class="feature-card">
        <span>02</span>
        <h3>Relier les parcours</h3>
        <p>Des passerelles entre patientes, proches, sages-femmes, obstétriciens et acteurs de prévention.</p>
    </article>
    <article class="feature-card">
        <span>03</span>
        <h3>Partager les pratiques</h3>
        <p>Un agenda, des dossiers et des ressources pour nourrir la formation continue.</p>
    </article>
</section>

<section class="split-section">
    <div>
        <p class="eyebrow">À la une</p>
        <h2>Un site pensé pour deux publics</h2>
        <p>Le public trouve des repères simples et rassurants. Les professionnels accèdent à des ressources structurées, à l’agenda et à des espaces de coordination.</p>
    </div>
    <div class="link-list">
        <a href="{{ route('about') }}">Découvrir notre démarche</a>
        <a href="{{ route('agenda') }}">Voir les prochains rendez-vous</a>
        <a href="#don">Soutenir les actions de prévention</a>
    </div>
</section>
@endsection

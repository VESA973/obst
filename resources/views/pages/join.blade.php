@extends('layouts.site')

@section('title', 'Adhérer | La Quinzaine Obstétricale')

@section('content')
<section class="page-hero join-hero">
    <p class="eyebrow">Adhésion</p>
    <h1>Rejoindre La Quinzaine Obstétricale</h1>
    <p>Adhérer, c’est soutenir une information obstétricale claire, bienveillante et accessible, tout en participant à une dynamique de prévention et de transmission.</p>
    <div class="hero-actions">
        <a class="btn primary" href="mailto:contact@quinzaine-obstetricale.test?subject=Demande%20d'adhesion">Demander une adhésion</a>
        <a class="btn secondary" href="{{ route('about') }}">Découvrir la démarche</a>
    </div>
</section>

<section class="resource-grid join-grid">
    <article><span>Public</span><h2>Soutenir les actions</h2><p>Participer à la diffusion d’une information fiable autour de la grossesse, de la naissance et du post-partum.</p></article>
    <article><span>Professionnels</span><h2>Partager les pratiques</h2><p>Contribuer aux rencontres, aux ressources et aux temps de coordination entre acteurs du parcours périnatal.</p></article>
    <article><span>Association</span><h2>Faire vivre le projet</h2><p>Aider au développement du site, des événements et des supports de prévention.</p></article>
    <article><span>Contact</span><h2>Être rappelé</h2><p>Envoyez une demande d’adhésion, nous reviendrons vers vous avec les modalités.</p></article>
</section>
@endsection

@extends('layouts.site')

@section('title', 'Agenda | La Quinzaine Obstetricale')

@section('content')
<section class="page-hero compact">
    <p class="eyebrow">Agenda</p>
    <h1>Les rendez-vous de la Quinzaine</h1>
    <p>Toutes les manifestations avec inscription en ligne : conferences, ateliers, rencontres professionnelles et temps d'echange ouverts au public.</p>
</section>

<section class="agenda-list">
    <article>
        <time>Sept.</time>
        <div><h2>Les Jeudis M</h2><p>Rencontre autour de la menopause, de la prevention et de la qualite de vie. <a href="{{ route('contact') }}">S'inscrire</a></p></div>
    </article>
    <article>
        <time>Oct.</time>
        <div><h2>Escape Game Sante</h2><p>Animation de prevention pour apprendre autrement, en equipe et sur le terrain. <a href="{{ route('contact') }}">S'inscrire</a></p></div>
    </article>
    <article>
        <time>Nov.</time>
        <div><h2>Assises Amazoniennes</h2><p>Temps fort de partage entre professionnels, institutions, chercheurs et associations. <a href="{{ route('contact') }}">S'inscrire</a></p></div>
    </article>
</section>
@endsection

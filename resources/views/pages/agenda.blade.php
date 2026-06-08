@extends('layouts.site')

@section('title', 'Agenda | La Quinzaine Obstétricale')

@section('content')
<section class="page-hero compact">
    <p class="eyebrow">Agenda</p>
    <h1>Les rendez-vous de la Quinzaine</h1>
    <p>Conférences, ateliers, rencontres professionnelles et temps d’échange ouverts au public.</p>
</section>

<section class="agenda-list">
    <article>
        <time>12 juin</time>
        <div><h2>Atelier public : comprendre le suivi de grossesse</h2><p>Rencontre animée par une sage-femme, suivie d’un temps de questions.</p></div>
    </article>
    <article>
        <time>18 juin</time>
        <div><h2>Soirée pro : coordination ville-maternité</h2><p>Retours d’expérience et échanges sur les parcours complexes.</p></div>
    </article>
    <article>
        <time>25 juin</time>
        <div><h2>Table ronde : présence, douleur et consentement</h2><p>Dialogue entre professionnels, patientes partenaires et associations.</p></div>
    </article>
</section>
@endsection

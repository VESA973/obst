@extends('layouts.site')

@section('title', 'Recherche et Innovation | La Quinzaine Obstétricale')

@section('content')
<x-page-hero page-key="research" />

<section class="resource-grid">
    <article><span>Recherche</span><h2>Projets en cours</h2><p>Travaux suivis par l'association et projets en développement avec les partenaires.</p></article>
    <article><span>Valorisation</span><h2>Publications</h2><p>Articles, resumes, communications et ressources issues des travaux menes.</p></article>
    <article><span>Terrain</span><h2>Etudes</h2><p>Enquetes, observations, retours d'experience et analyses des besoins locaux.</p></article>
    <article><span>Opportunites</span><h2>Appels a projets</h2><p>Veille et coordination pour construire des reponses collectives.</p></article>
    <article><span>Réseau</span><h2>Collaborations</h2><p>Liens avec les institutions, équipes de soins, chercheurs et associations.</p></article>
</section>
@endsection

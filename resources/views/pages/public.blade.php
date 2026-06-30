@extends('layouts.site')

@section('title', 'Santé de la femme | La Quinzaine Obstétricale')

@section('content')
<x-page-hero page-key="public" class="public-hero" />

<section class="resource-grid">
    @forelse ($categories as $category)
        <article>
            <span>{{ $category->articles_count }} article(s)</span>
            <h2>{{ $category->title ?: $category->name }}</h2>
            <p>{{ $category->description ?: 'Retrouvez les articles et ressources associés à ce thème.' }}</p>
            <a href="{{ route('public.category', $category) }}">Voir les articles</a>
        </article>
    @empty
        <article><span>Cycle de vie</span><h2>Puberté</h2><p>Comprendre les changements du corps, les règles, l'hygiène et les premiers repères de santé.</p></article>
        <article><span>Projet parental</span><h2>Fertilité</h2><p>Questions fréquentes, délais, examens possibles et orientation vers les professionnels.</p></article>
        <article><span>Périnatalité</span><h2>Grossesse</h2><p>Suivi, examens, signes d'alerte et sujets à aborder avec l'équipe soignante.</p></article>
        <article><span>Après naissance</span><h2>Post-partum</h2><p>Récupération, allaitement, fatigue, douleur, soutien psychique et relais d'aide.</p></article>
        <article><span>Douleurs</span><h2>Endométriose</h2><p>Symptômes, parcours diagnostique, traitements et accompagnement au quotidien.</p></article>
        <article><span>Gynécologie</span><h2>Fibromes</h2><p>Comprendre les signes, les examens et les options de prise en charge.</p></article>
    @endforelse
</section>
@endsection

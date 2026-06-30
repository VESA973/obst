@extends('layouts.site')

@section('title', 'Santé de la femme | La Quinzaine Obstétricale')

@section('content')
<x-page-hero page-key="public" class="public-hero" />

<section class="health-category-grid">
    @forelse ($categories as $category)
        <article class="health-category-card">
            <a href="{{ route('public.category', $category) }}">
                <span class="health-category-media">
                    @if ($category->image_path)
                        <img src="{{ Storage::url($category->image_path) }}" alt="">
                    @else
                        <span>{{ Str::substr($category->name, 0, 1) }}</span>
                    @endif
                </span>
                <span class="health-category-content">
                    <small>{{ $category->articles_count }} article(s)</small>
                    <strong>{{ $category->title ?: $category->name }}</strong>
                    <span>{{ $category->description ?: 'Retrouvez les articles et ressources associés à ce thème.' }}</span>
                </span>
            </a>
        </article>
    @empty
        <article class="health-category-card"><a href="#"><span class="health-category-media"><span>P</span></span><span class="health-category-content"><small>Cycle de vie</small><strong>Puberté</strong><span>Comprendre les changements du corps et les premiers repères de santé.</span></span></a></article>
        <article class="health-category-card"><a href="#"><span class="health-category-media"><span>F</span></span><span class="health-category-content"><small>Projet parental</small><strong>Fertilité</strong><span>Questions fréquentes, délais, examens possibles et orientation.</span></span></a></article>
        <article class="health-category-card"><a href="#"><span class="health-category-media"><span>G</span></span><span class="health-category-content"><small>Périnatalité</small><strong>Grossesse</strong><span>Suivi, examens, signes d'alerte et sujets à aborder.</span></span></a></article>
    @endforelse
</section>
@endsection

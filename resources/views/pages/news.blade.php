@extends('layouts.site')

@section('title', 'Actualités | La Quinzaine Obstétricale')

@section('content')
<x-page-hero page-key="news" />

<section class="resource-grid news-grid">
    @forelse ($articles as $article)
        @php
            $coverImage = $article->image_path ?: $article->assets->where('type', 'photo')->first()?->path;
        @endphp
        <article id="article-{{ $article->id }}">
            <a class="news-card-link" href="{{ route('articles.show', $article) }}">
                @if ($coverImage)
                    <img src="{{ Storage::url($coverImage) }}" alt="">
                @endif
                <span>{{ $article->display_category ?: ($article->external_url ? 'Article externe' : 'Article') }}</span>
                <h2>{{ $article->title }}</h2>
                <p>{{ $article->excerpt ?: Str::limit(strip_tags($article->body), 180) }}</p>
                <strong>Lire l'actualité</strong>
            </a>
        </article>
    @empty
        <article><span>Article</span><h2>Santé de la femme en Guyane</h2><p>Comprendre les enjeux d'information, de prévention et d'accès aux soins.</p></article>
        <article><span>Tribune</span><h2>Faire avancer la périnatalité</h2><p>Positionnements, constats et propositions portées par l'association.</p></article>
        <article><span>Interview</span><h2>Paroles de terrain</h2><p>Rencontres avec les professionnels, partenaires et personnes engagées.</p></article>
        <article><span>Science</span><h2>Publications scientifiques</h2><p>Veille, travaux, résumés et ressources documentaires utiles.</p></article>
    @endforelse
</section>
@endsection

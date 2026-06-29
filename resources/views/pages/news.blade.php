@extends('layouts.site')

@section('title', 'Actualites | La Quinzaine Obstetricale')

@section('content')
<section class="page-hero compact">
    <p class="eyebrow">Actualites</p>
    <h1>Articles, tribunes, interviews et publications</h1>
    <p>Un espace pour suivre les prises de parole, les communiques, les publications scientifiques et les nouvelles ressources de l'association.</p>
</section>

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
                <strong>Lire l'actualite</strong>
            </a>
        </article>
    @empty
        <article><span>Article</span><h2>Sante des femmes en Guyane</h2><p>Comprendre les enjeux d'information, de prevention et d'acces aux soins.</p></article>
        <article><span>Tribune</span><h2>Faire avancer la perinatalite</h2><p>Positionnements, constats et propositions portees par l'association.</p></article>
        <article><span>Interview</span><h2>Paroles de terrain</h2><p>Rencontres avec les professionnels, partenaires et personnes engagees.</p></article>
        <article><span>Science</span><h2>Publications scientifiques</h2><p>Veille, travaux, resumes et ressources documentaires utiles.</p></article>
    @endforelse
</section>
@endsection

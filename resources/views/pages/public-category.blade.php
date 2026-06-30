@extends('layouts.site')

@section('title', ($category->title ?: $category->name).' | Santé de la femme')

@section('content')
<section class="page-hero compact">
    <p class="eyebrow">Santé de la femme</p>
    <h1>{{ $category->title ?: $category->name }}</h1>
    @if ($category->description)
        <p>{{ $category->description }}</p>
    @endif
</section>

<section class="resource-grid news-grid">
    @forelse ($articles as $article)
        @php($coverImage = $article->image_path ?: $article->assets->where('type', 'photo')->first()?->path)
        <article>
            <a class="news-card-link" href="{{ route('public.article', [$category, $article]) }}">
                @if ($coverImage)
                    <img src="{{ Storage::url($coverImage) }}" alt="">
                @endif
                <span>{{ $category->name }}</span>
                <h2>{{ $article->title }}</h2>
                <p>{{ $article->excerpt ?: Str::limit(strip_tags($article->body), 180) }}</p>
                <strong>Lire l'article</strong>
            </a>
        </article>
    @empty
        <article>
            <span>{{ $category->name }}</span>
            <h2>Aucun article publié</h2>
            <p>Les contenus de cette catégorie seront ajoutés prochainement.</p>
        </article>
    @endforelse
</section>

<section class="section-action">
    <a class="btn secondary" href="{{ route('public') }}">Retour à Santé de la femme</a>
</section>
@endsection

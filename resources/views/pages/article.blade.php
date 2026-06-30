@extends('layouts.site')

@section('title', $article->title.' | La Quinzaine Obstétricale')

@section('content')
@php
    $mainImage = $article->image_path ?: $article->assets->where('type', 'photo')->first()?->path;
@endphp

<section class="page-hero compact">
    <p class="eyebrow">{{ $article->display_category ?: 'Actualité' }}</p>
    <h1>{{ $article->title }}</h1>
    @if ($article->excerpt)
        <p>{{ $article->excerpt }}</p>
    @endif
</section>

<article class="article-detail">
    @if ($mainImage)
        <a class="article-main-image" href="{{ Storage::url($mainImage) }}" target="_blank" rel="noreferrer">
            <img src="{{ Storage::url($mainImage) }}" alt="Image principale {{ $article->title }}">
        </a>
    @endif

    @if ($article->published_at)
        <time>{{ $article->published_at->translatedFormat('d F Y') }}</time>
    @endif

    @if ($article->body)
        <div class="article-body">{{ $article->body }}</div>
    @elseif ($article->excerpt)
        <div class="article-body">{{ $article->excerpt }}</div>
    @endif

    @if ($article->assets->where('type', 'photo')->isNotEmpty())
        <div class="article-gallery" aria-label="Photos de {{ $article->title }}">
            @foreach ($article->assets->where('type', 'photo') as $photo)
                <a href="{{ Storage::url($photo->path) }}" target="_blank" rel="noreferrer">
                    <img src="{{ Storage::url($photo->path) }}" alt="">
                </a>
            @endforeach
        </div>
    @endif

    <div class="article-actions">
        <a class="btn secondary" href="{{ $backRoute ?? route('news') }}">{{ $backLabel ?? 'Retour aux actualités' }}</a>
        @if ($article->external_url)
            <a class="btn" href="{{ $article->external_url }}" target="_blank" rel="noreferrer">Lire sur {{ $article->source_name ?: 'le site source' }}</a>
        @endif
    </div>
</article>
@endsection

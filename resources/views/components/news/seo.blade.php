{{--
    Balises SEO d'une page Actualités : canonique, Open Graph, Twitter Cards,
    et données structurées JSON-LD NewsArticle pour une page article.
--}}
@props([
    'title',
    'description' => null,
    'canonical' => null,
    'image' => null,
    'article' => null,
    'prev' => null,
    'next' => null,
])

@php
    $canonical ??= url()->current();
    $description = \Illuminate\Support\Str::limit(trim((string) $description), 160);
    $imageUrl = $image ? \Illuminate\Support\Facades\Storage::disk('public')->url($image) : asset('images/quinzaine-hero.png');
    $siteName = 'La Quinzaine Obstétricale';
@endphp

@push('news-head')
    <link rel="canonical" href="{{ $canonical }}">
    @if ($prev) <link rel="prev" href="{{ $prev }}"> @endif
    @if ($next) <link rel="next" href="{{ $next }}"> @endif
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="{{ $article ? 'article' : 'website' }}">
    <meta property="og:title" content="{{ $title }}">
    @if ($description) <meta property="og:description" content="{{ $description }}"> @endif
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    @if ($description) <meta name="twitter:description" content="{{ $description }}"> @endif
    <meta name="twitter:image" content="{{ $imageUrl }}">

    @if ($article)
        <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
        <meta property="article:modified_time" content="{{ $article->updated_at->toIso8601String() }}">
        <meta property="article:section" content="{{ $article->category?->name }}">
        <script type="application/ld+json">
            {!! json_encode(array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => \Illuminate\Support\Str::limit($article->title, 110),
                'description' => $description ?: null,
                'image' => [$imageUrl],
                'datePublished' => $article->published_at->toIso8601String(),
                'dateModified' => $article->updated_at->toIso8601String(),
                'articleSection' => $article->category?->name,
                'mainEntityOfPage' => $canonical,
                'author' => $article->author
                    ? ['@type' => 'Person', 'name' => $article->author->name]
                    : ['@type' => 'Organization', 'name' => $siteName],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                    'logo' => ['@type' => 'ImageObject', 'url' => asset('images/quinzaine-logo.png')],
                ],
            ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    @endif
@endpush

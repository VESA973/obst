{{-- Date relative (« il y a 2 heures ») et temps de lecture. --}}
@props(['article', 'readingTime' => true])

<p {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-2 text-xs text-[var(--news-muted)]']) }}>
    <time datetime="{{ $article->published_at->toIso8601String() }}" title="{{ $article->published_at->translatedFormat('j F Y à H:i') }}">
        {{ $article->published_at->diffForHumans() }}
    </time>
    @if ($readingTime)
        <span aria-hidden="true">·</span>
        <span>{{ $article->readingTime() }} min de lecture</span>
    @endif
</p>

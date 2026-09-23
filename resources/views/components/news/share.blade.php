{{-- Boutons de partage (liens simples, aucun script tiers) et copie du lien. --}}
@props(['url', 'title'])

@php
    $links = [
        'Facebook' => ['https://www.facebook.com/sharer/sharer.php?u='.urlencode($url), 'M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8Z'],
        'X' => ['https://x.com/intent/post?url='.urlencode($url).'&text='.urlencode($title), 'M4 4l6.8 9.1L4.3 20h1.6l5.6-6 4.5 6H20l-7.1-9.6L19 4h-1.6l-5.2 5.6L8 4H4Z'],
        'LinkedIn' => ['https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($url), 'M4 9h4v11H4V9Zm2-6a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm4 6h3.8v1.6c.6-1 1.9-1.9 3.7-1.9 4 0 4.5 2.5 4.5 5.8V20h-4v-4.8c0-1.2 0-2.7-1.7-2.7s-2 1.3-2 2.6V20h-4V9Z'],
        'WhatsApp' => ['https://wa.me/?text='.urlencode($title.' '.$url), 'M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Zm4.7 12.6c-.2.6-1.2 1.1-1.7 1.2-.4 0-1 .1-3.1-.8-2.6-1.1-4.2-3.7-4.3-3.9-.1-.2-1-1.4-1-2.6s.6-1.9.9-2.1c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.6 2.1 1.1 1 2 1.3 2.3 1.4.3.1.5.1.6-.1l.8-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.1.1.6-.1 1.3Z'],
    ];
@endphp

<div {{ $attributes->class('flex flex-wrap items-center gap-2') }}>
    <span class="mr-1 text-sm font-semibold">Partager</span>
    @foreach ($links as $network => [$href, $path])
        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
           class="grid size-10 place-items-center rounded-full border border-[var(--news-line)] transition-colors hover:border-accent hover:bg-accent hover:text-white focus-visible:outline-2 focus-visible:outline-accent">
            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $path }}"/></svg>
            <span class="sr-only">Partager sur {{ $network }} (nouvelle fenêtre)</span>
        </a>
    @endforeach
    <button type="button" data-copy-link="{{ $url }}"
            class="inline-flex h-10 items-center gap-2 rounded-full border border-[var(--news-line)] px-4 text-sm transition-colors hover:border-accent hover:text-accent focus-visible:outline-2 focus-visible:outline-accent dark:hover:text-accent-light">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>
        <span data-copy-label>Copier le lien</span>
    </button>
</div>

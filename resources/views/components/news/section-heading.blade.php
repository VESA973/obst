{{-- Titre de section éditorial avec filet et lien « Voir tout » facultatif. --}}
@props(['title', 'href' => null, 'color' => null, 'id' => null])

<div {{ $attributes->class('mb-6 flex items-end justify-between gap-4 border-b-2 pb-2') }} style="border-color: {{ $color ?? 'var(--color-accent)' }}">
    <h2 @if ($id) id="{{ $id }}" @endif class="font-serif text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h2>
    @if ($href)
        <a href="{{ $href }}" class="shrink-0 text-sm font-semibold text-accent hover:underline dark:text-accent-light">
            Voir tout <span aria-hidden="true">→</span><span class="sr-only"> : {{ $title }}</span>
        </a>
    @endif
</div>

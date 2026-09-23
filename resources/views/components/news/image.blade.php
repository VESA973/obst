{{--
    Image d'article en WebP responsive (srcset), ou visuel de remplacement aux couleurs de la catégorie.
    Usage : <x-news.image :article="$article" sizes="(min-width: 1024px) 33vw, 100vw" />
--}}
@props([
    'article',
    'sizes' => '100vw',
    'eager' => false,
    // Couleur du visuel de remplacement (par défaut : couleur de la catégorie dans les Actualités).
    'color' => null,
])

@php
    $image = \App\Support\ResponsiveImage::for($article->image_path);
    $color ??= \App\Support\NewsBlocks::color($article->category);
@endphp

@if ($image)
    <img src="{{ $image['src'] }}"
         @if ($image['srcset']) srcset="{{ $image['srcset'] }}" sizes="{{ $sizes }}" @endif
         alt="{{ $article->title }}"
         @if ($eager) fetchpriority="high" @else loading="lazy" @endif
         decoding="async"
         {{ $attributes->merge(['class' => 'size-full object-cover']) }}>
@else
    {{-- Pas de photo : dégradé de la catégorie et initiale en filigrane. --}}
    <div role="img" aria-label="{{ $article->title }}"
         style="--cat: {{ $color }}"
         {{ $attributes->merge(['class' => 'relative grid size-full place-items-center overflow-hidden bg-[radial-gradient(circle_at_30%_20%,color-mix(in_srgb,var(--cat)_55%,white),var(--cat)_60%,color-mix(in_srgb,var(--cat)_70%,black))]']) }}>
        <span class="font-serif text-[clamp(3rem,12vw,7rem)] font-bold leading-none text-white/25 select-none" aria-hidden="true">
            {{ mb_strtoupper(mb_substr($article->category?->name ?? $article->title, 0, 1)) }}
        </span>
    </div>
@endif

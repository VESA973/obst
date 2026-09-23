{{--
    Carte article. Variantes :
    - featured : grande image, titre en surimpression sur un dégradé sombre (« À la une ») ;
    - standard : image 16:9, titre, extrait, métadonnées (grilles) ;
    - compact  : miniature à gauche, titre (barres latérales, listes, résultats de recherche).
    Toute la carte est cliquable (lien étendu), le badge catégorie reste un lien séparé.
--}}
@props([
    'article',
    'variant' => 'standard',
    'eager' => false,
    'headingLevel' => 3,
    'highlight' => null,
    'wide' => false,
])

@php
    $tag = 'h'.$headingLevel;
    $title = e($article->title);
    $summary = e($article->summary(220));

    // Recherche : surlignage du terme sur le texte déjà échappé, sans toucher aux entités HTML (&amp;…).
    if (filled($highlight)) {
        $pattern = '/(&#?\w+;)|('.preg_quote(e($highlight), '/').')/iu';
        $mark = fn (array $match) => $match[1] !== '' ? $match[1] : '<mark>'.$match[2].'</mark>';
        $title = preg_replace_callback($pattern, $mark, $title);
        $summary = preg_replace_callback($pattern, $mark, $summary);
    }
@endphp

@if ($variant === 'featured')
    <article {{ $attributes->class('news-card group relative isolate overflow-hidden rounded-xl bg-black') }}>
        {{-- « wide » : pleine largeur de page, format plus panoramique pour limiter la hauteur. --}}
        <div @class(['aspect-[4/3] sm:aspect-[16/9]', 'lg:aspect-[16/10]' => ! $wide, 'lg:aspect-[21/9]' => $wide])>
            <x-news.image :article="$article" :eager="$eager" sizes="(min-width: 1024px) 62vw, 100vw"
                          class="transition-transform duration-700 group-hover:scale-[1.03]" />
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-8">
            <x-news.category-badge :category="$article->category" size="md" />
            <{{ $tag }} class="mt-3 font-serif text-2xl font-bold leading-tight break-words hyphens-auto sm:text-4xl lg:text-[2.6rem]">
                <a href="{{ $article->publicUrl() }}" class="news-card-link">{!! $title !!}</a>
            </{{ $tag }}>
            <p class="mt-3 line-clamp-2 max-w-2xl text-sm text-white/85 sm:text-base">{!! $summary !!}</p>
            <x-news.meta :article="$article" class="mt-3 !text-white/75" />
        </div>
    </article>
@elseif ($variant === 'compact')
    <article {{ $attributes->class('news-card group relative flex gap-4') }}>
        <div class="aspect-[4/3] w-28 shrink-0 overflow-hidden rounded-md sm:w-32">
            <x-news.image :article="$article" sizes="128px" class="transition-transform duration-500 group-hover:scale-105" />
        </div>
        <div class="min-w-0 flex-1">
            <x-news.category-badge :category="$article->category" />
            <{{ $tag }} class="mt-1.5 line-clamp-3 break-words hyphens-auto font-serif text-base font-semibold leading-snug group-hover:text-accent dark:group-hover:text-accent-light">
                <a href="{{ $article->publicUrl() }}" class="news-card-link">{!! $title !!}</a>
            </{{ $tag }}>
            @if (filled($highlight))
                <p class="mt-1 line-clamp-2 text-sm text-[var(--news-muted)]">{!! $summary !!}</p>
            @endif
            <x-news.meta :article="$article" :reading-time="false" class="mt-1.5" />
        </div>
    </article>
@else
    <article {{ $attributes->class('news-card group relative flex flex-col') }}>
        <div class="aspect-video overflow-hidden rounded-lg">
            <x-news.image :article="$article" :eager="$eager" sizes="(min-width: 1280px) 25vw, (min-width: 768px) 50vw, 100vw"
                          class="transition-transform duration-500 group-hover:scale-105" />
        </div>
        <div class="mt-4 flex flex-1 flex-col">
            <div><x-news.category-badge :category="$article->category" /></div>
            <{{ $tag }} class="mt-2 line-clamp-2 break-words hyphens-auto font-serif text-xl font-bold leading-snug transition-colors group-hover:text-accent dark:group-hover:text-accent-light">
                <a href="{{ $article->publicUrl() }}" class="news-card-link">{!! $title !!}</a>
            </{{ $tag }}>
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-[var(--news-muted)]">{!! $summary !!}</p>
            <x-news.meta :article="$article" class="mt-auto pt-3" />
        </div>
    </article>
@endif

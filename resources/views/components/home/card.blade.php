{{--
    Carte de l'accueil (articles, fiches santé, événements).
    - `article` : image WebP responsive (ou visuel de remplacement) via <x-news.image> ;
    - `date`    : pastille de date à la place de l'image (événements sans visuel) ;
    Toute la carte est cliquable si `href` est fourni (lien étendu sur le titre).
--}}
@props([
    'href' => null,
    'title',
    'text' => null,
    'badge' => null,
    'meta' => null,
    'article' => null,
    'imageUrl' => null,
    'date' => null,
])

<article {{ $attributes->class(['home-card', 'home-card--link' => $href]) }}>
    <div class="home-card-media">
        @if ($article)
            <x-news.image :article="$article" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 85vw" />
        @elseif ($imageUrl)
            <img src="{{ $imageUrl }}" alt="" loading="lazy" decoding="async">
        @else
            {{-- Pas d'image : pastille de date sur fond teinté, avec une fougère en filigrane. --}}
            <div class="home-card-date">
                <x-home.leaf-pattern variant="fern" class="home-card-date-leaf" />
                @if ($date)
                    <span class="home-card-date-day">{{ $date->translatedFormat('d') }}</span>
                    <span class="home-card-date-month">{{ $date->translatedFormat('F Y') }}</span>
                @else
                    <span class="home-card-date-month">À venir</span>
                @endif
            </div>
        @endif
    </div>
    <div class="home-card-body">
        @if ($badge)
            <span class="home-card-badge">{{ $badge }}</span>
        @endif
        <h3>
            @if ($href)
                <a href="{{ $href }}" class="home-card-link">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
        </h3>
        @if ($text)
            <p>{{ $text }}</p>
        @endif
        @if ($meta)
            <p class="home-card-meta">{{ $meta }}</p>
        @endif
    </div>
</article>

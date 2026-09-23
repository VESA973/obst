{{-- Titre de section : sur-titre en couleur d'accent, titre, texte d'introduction et lien facultatif. --}}
@props(['eyebrow' => null, 'title', 'id' => null, 'href' => null, 'linkLabel' => 'Voir tout', 'center' => false])

<div {{ $attributes->class(['home-section-title', 'home-section-title--center' => $center]) }}>
    <div>
        @if ($eyebrow)
            <p class="home-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h2 @if ($id) id="{{ $id }}" @endif>{{ $title }}</h2>
        @if (! $slot->isEmpty())
            <p class="home-section-intro">{{ $slot }}</p>
        @endif
    </div>
    @if ($href)
        <a class="home-link-arrow" href="{{ $href }}">
            {{ $linkLabel }}<span class="sr-only"> : {{ $title }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
        </a>
    @endif
</div>

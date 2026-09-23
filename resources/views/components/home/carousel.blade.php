{{--
    Carrousel Swiper (initialisé par resources/js/home.js).
    Chaque élément du slot doit être un <div class="swiper-slide">.
    Sans JavaScript, les cartes défilent simplement à l'horizontale.
    `autoplay` : défilement automatique lent (carrousel principal uniquement).
--}}
@props(['label', 'autoplay' => false])

<div {{ $attributes->class('home-carousel') }} data-home-carousel @if ($autoplay) data-autoplay @endif>
    <div class="swiper" role="region" aria-roledescription="carrousel" aria-label="{{ $label }}">
        <div class="swiper-wrapper">
            {{ $slot }}
        </div>
    </div>
    <div class="home-carousel-controls">
        <div class="home-carousel-progress" data-carousel-pagination></div>
        <div class="home-carousel-buttons">
            <button type="button" class="home-carousel-btn" data-carousel-prev>
                <span class="sr-only">Précédent</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" class="home-carousel-btn" data-carousel-next>
                <span class="sr-only">Suivant</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </div>
</div>

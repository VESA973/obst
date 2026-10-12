{{--
    Hero de l'accueil : grande photo de Guyane (WebP responsive, préchargée), dégradé aux couleurs
    du site, motifs végétaux en bordure, formule d'accueil (config/home.php), boutons existants et vague « fleuve » vers la section suivante.
--}}
@php
    $sizes = [800, 1200, 1808];
    $srcset = collect($sizes)->map(fn (int $w) => asset("images/guyane-hero-{$w}.webp")." {$w}w")->implode(', ');
@endphp

@push('head')
    {{-- Préchargement de la grande photo (image principale de la page). --}}
    <link rel="preload" as="image" type="image/webp" imagesrcset="{{ $srcset }}" imagesizes="100vw" fetchpriority="high">
@endpush

<section class="home-hero" aria-labelledby="home-hero-title">
    <picture>
        <source type="image/webp" srcset="{{ $srcset }}" sizes="100vw">
        <img class="home-hero-img" src="{{ asset('images/guyane-hero.png') }}" alt="Paysage de Guyane avec forêt amazonienne et fleuve"
             width="1808" height="870" fetchpriority="high" decoding="async">
    </picture>
    <div class="home-hero-overlay" aria-hidden="true"></div>
    <x-home.leaf-pattern variant="palm" class="home-leaf home-leaf--hero-right" />
    <x-home.leaf-pattern variant="fern" class="home-leaf home-leaf--hero-left" />

    <div class="home-hero-content">
        {{-- Formule d'accueil : uniquement les traductions validées de config/home.php. --}}
        <p class="home-greetings home-appear" style="--delay: 0ms">
            @foreach (config('home.greetings') as $greeting)
                <span lang="{{ $greeting['lang'] }}" title="{{ $greeting['label'] }}">{{ $greeting['text'] }}</span>
            @endforeach
        </p>
        <p class="eyebrow home-appear" style="--delay: 80ms">Santé de la femme et périnatalité en Guyane</p>
        <h1 id="home-hero-title" class="home-hero-title">
            @foreach (['Informer', 'Former', 'Innover', 'Agir'] as $word)
                <span style="--i: {{ $loop->index }}">{{ $word }}</span>
            @endforeach
        </h1>
        <p class="lead home-appear" style="--delay: 240ms">Ensemble pour une meilleure santé de la femme et de la périnatalité en Guyane.</p>
        <div class="hero-actions home-appear" style="--delay: 320ms">
            <a class="btn primary" href="{{ route('join') }}">Adhérer</a>
            <a class="btn secondary" href="{{ route('join') }}#don">Faire un don</a>
            <a class="btn secondary" href="{{ route('contact') }}">Nous contacter</a>
        </div>
    </div>

    <x-home.wave-divider fill="var(--mint)" class="home-wave--hero" />
</section>

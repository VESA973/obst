{{--
    Hero de l'accueil : grande photo de Guyane (WebP responsive, préchargée), dégradé aux couleurs
    du site, motifs végétaux en bordure, formule d'accueil (config/home.php), boutons existants,
    rappel des numéros d'urgence et vague « fleuve » vers la section suivante.
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
        <h1 id="home-hero-title" class="home-appear" style="--delay: 160ms">Informer. Former. Innover. Agir.</h1>
        <p class="lead home-appear" style="--delay: 240ms">Ensemble pour une meilleure santé de la femme et de la périnatalité en Guyane.</p>
        <div class="hero-actions home-appear" style="--delay: 320ms">
            <a class="btn primary" href="{{ route('join') }}">Adhérer</a>
            <a class="btn secondary" href="{{ route('join') }}#don">Faire un don</a>
            <a class="btn secondary" href="{{ route('contact') }}">Nous contacter</a>
        </div>
        <p class="home-hero-emergency home-appear" style="--delay: 400ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>
            <span>Urgence médicale :</span>
            @foreach (config('home.emergency') as $emergency)
                <a href="tel:{{ $emergency['number'] }}">{{ $emergency['number'] }} <span class="sr-only">({{ $emergency['label'] }})</span></a>
                @if (! $loop->last)<span aria-hidden="true">ou</span>@endif
            @endforeach
        </p>
    </div>

    <x-home.wave-divider fill="var(--mint)" class="home-wave--hero" />
</section>

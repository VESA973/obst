{{--
    Gabarit commun aux sections éditoriales (Actualités, Santé de la femme ; voir App\Support\EditorialSection) :
    en-tête et pied de page du site, plus la barre de section, le bandeau « Dernière minute »
    (Actualités) et le pied de page éditorial. Les vues enfants remplissent la section « news ».
--}}
@extends('layouts.site')

@section('body_class', 'news-page')

@push('head')
    @if ($editorial->feedUrl())
        <link rel="alternate" type="application/rss+xml" title="{{ $editorial->title }} — La Quinzaine Obstétricale" href="{{ $editorial->feedUrl() }}">
    @endif
    @stack('news-head')
@endpush

@section('content')
    <div class="news-root min-h-screen transition-colors" data-news-root>
        {{-- Thème appliqué avant l'affichage pour éviter un flash clair en mode sombre. --}}
        <script>
            (() => {
                const root = document.currentScript.parentElement;
                let theme = null;
                try { theme = localStorage.getItem('news-theme'); } catch (error) {}
                if (theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches)) {
                    root.classList.add('dark');
                }
                // Animations d'apparition : actives seulement avec JavaScript et sans « réduire les animations ».
                if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    root.classList.add('has-reveal');
                }
            })();
        </script>

        @hasSection('reading_progress')
            <div class="pointer-events-none fixed inset-x-0 top-0 z-50 h-1" aria-hidden="true">
                <div class="news-progress h-full bg-gradient-to-r from-accent to-rose" data-reading-progress></div>
            </div>
        @endif

        @include('news.partials.bar')

        @if ($editorial->ticker)
            @include('news.partials.ticker')
        @endif

        <div class="mx-auto w-full max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
            @yield('news')
        </div>

        @include('news.partials.footer')
    </div>
@endsection

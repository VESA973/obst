{{-- Barre de section fixe : logo, rubriques, recherche, mode sombre, menu mobile. --}}
@php($currentSlug = $editorial->currentCategorySlug())

<header class="sticky top-0 z-40 border-b border-[var(--news-line)] bg-white/90 backdrop-blur-md dark:bg-[#0f0c16]/90">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ $editorial->homeUrl() }}" class="flex shrink-0 items-center gap-2.5 rounded focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-accent">
            <span class="grid size-8 place-items-center rounded-lg bg-gradient-to-br from-accent to-rose font-serif text-lg font-bold text-white" aria-hidden="true">Q</span>
            <span class="font-serif text-xl font-bold tracking-tight">{{ $editorial->title }}</span>
        </a>

        <nav class="news-scroll-x ml-4 hidden min-w-0 flex-1 items-center gap-1 overflow-x-auto lg:flex" aria-label="Rubriques : {{ $editorial->title }}">
            @foreach ($newsCategories as $category)
                <a href="{{ \App\Support\EditorialSection::categoryUrl($category) }}"
                   @if ($currentSlug === $category->slug) aria-current="page" @endif
                   @class([
                       'shrink-0 rounded-full px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-2 focus-visible:outline-accent',
                       'bg-accent text-white' => $currentSlug === $category->slug,
                       'text-[var(--news-muted)] hover:bg-[var(--news-surface)] hover:text-current' => $currentSlug !== $category->slug,
                   ])>{{ $category->name }}</a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-1">
            <form action="{{ $editorial->searchUrl() }}" method="GET" role="search" class="relative hidden md:block">
                <label for="news-search-desktop" class="sr-only">Rechercher un article</label>
                <input id="news-search-desktop" name="q" type="search" value="{{ request('q') }}"
                       placeholder="Rechercher…"
                       class="w-44 rounded-full border border-[var(--news-line)] bg-[var(--news-surface)] py-1.5 pl-9 pr-3 text-sm transition-[width] focus:w-60 focus:border-accent focus:outline-none">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[var(--news-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            </form>

            <button type="button" data-theme-toggle aria-pressed="false"
                    class="grid size-10 place-items-center rounded-full text-[var(--news-muted)] transition-colors hover:bg-[var(--news-surface)] hover:text-current focus-visible:outline-2 focus-visible:outline-accent">
                <span class="sr-only">Activer le mode sombre</span>
                <svg class="size-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                <svg class="hidden size-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            </button>

            <button type="button" data-news-menu-open aria-controls="news-menu" aria-haspopup="dialog"
                    class="grid size-10 place-items-center rounded-full transition-colors hover:bg-[var(--news-surface)] focus-visible:outline-2 focus-visible:outline-accent lg:hidden">
                <span class="sr-only">Ouvrir le menu : {{ $editorial->title }}</span>
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile et tablette : catégories en défilement horizontal. --}}
    <nav class="news-scroll-x flex gap-1 overflow-x-auto border-t border-[var(--news-line)] px-4 py-2 lg:hidden" aria-label="Rubriques : {{ $editorial->title }} (mobile)">
        @foreach ($newsCategories as $category)
            <a href="{{ \App\Support\EditorialSection::categoryUrl($category) }}"
               @if ($currentSlug === $category->slug) aria-current="page" @endif
               @class([
                   'shrink-0 rounded-full px-3 py-1 text-sm font-medium',
                   'bg-accent text-white' => $currentSlug === $category->slug,
                   'bg-[var(--news-surface)] text-[var(--news-muted)]' => $currentSlug !== $category->slug,
               ])>{{ $category->name }}</a>
        @endforeach
    </nav>
</header>

{{-- Menu mobile (burger). --}}
<dialog id="news-menu" aria-label="Menu : {{ $editorial->title }}"
        class="m-0 ml-auto h-dvh max-h-none w-[min(22rem,100vw)] max-w-none bg-white p-0 text-[#1c1626] backdrop:bg-black/50 dark:bg-[#0f0c16] dark:text-[#ece8f4]">
    <div class="flex h-full flex-col gap-6 overflow-y-auto p-6">
        <div class="flex items-center justify-between">
            <span class="font-serif text-2xl font-bold">{{ $editorial->title }}</span>
            <button type="button" data-news-menu-close class="grid size-10 place-items-center rounded-full hover:bg-[var(--news-surface)] focus-visible:outline-2 focus-visible:outline-accent">
                <span class="sr-only">Fermer le menu</span>
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <form action="{{ $editorial->searchUrl() }}" method="GET" role="search">
            <label for="news-search-mobile" class="sr-only">Rechercher un article</label>
            <input id="news-search-mobile" name="q" type="search" placeholder="Rechercher un article…"
                   class="w-full rounded-lg border border-[var(--news-line)] bg-[var(--news-surface)] px-4 py-2.5 focus:border-accent focus:outline-none">
        </form>

        <nav aria-label="Rubriques">
            <ul class="divide-y divide-[var(--news-line)]">
                <li><a href="{{ $editorial->homeUrl() }}" class="block py-3 font-medium">À la une</a></li>
                @foreach ($newsCategories as $category)
                    <li>
                        <a href="{{ \App\Support\EditorialSection::categoryUrl($category) }}" class="flex items-center justify-between py-3">
                            <span>{{ $category->name }}</span>
                            <span class="text-sm text-[var(--news-muted)]">{{ $category->articles_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <a href="{{ route('home') }}" class="mt-auto text-sm text-[var(--news-muted)] underline-offset-4 hover:underline">← Retour au site de l'association</a>
    </div>
</dialog>

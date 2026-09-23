{{-- Pied de page éditorial : rubriques, lettre d'information, liens utiles. --}}
<footer class="border-t border-[var(--news-line)] bg-[var(--news-surface)]">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div class="lg:col-span-1">
            <p class="font-serif text-2xl font-bold">{{ $editorial->title }}</p>
            <p class="mt-3 text-sm leading-relaxed text-[var(--news-muted)]">{{ $editorial->description }}</p>
            @if ($editorial->feedUrl())
            <a href="{{ $editorial->feedUrl() }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-accent hover:underline dark:text-accent-light">
                <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 17a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM3 10v3a8 8 0 0 1 8 8h3A11 11 0 0 0 3 10Zm0-6v3a14 14 0 0 1 14 14h3A17 17 0 0 0 3 4Z"/></svg>
                Flux RSS
            </a>
            @endif
        </div>

        <nav aria-label="Rubriques (pied de page)">
            <p class="text-xs font-bold uppercase tracking-widest text-[var(--news-muted)]">Rubriques</p>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($newsCategories as $category)
                    <li><a href="{{ \App\Support\EditorialSection::categoryUrl($category) }}" class="hover:text-accent hover:underline dark:hover:text-accent-light">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </nav>

        <nav aria-label="Liens utiles">
            <p class="text-xs font-bold uppercase tracking-widest text-[var(--news-muted)]">L'association</p>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="{{ route('about') }}" class="hover:text-accent hover:underline dark:hover:text-accent-light">Qui sommes-nous ?</a></li>
                <li><a href="{{ route('agenda') }}" class="hover:text-accent hover:underline dark:hover:text-accent-light">Agenda</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-accent hover:underline dark:hover:text-accent-light">Contact</a></li>
                <li><a href="{{ route('legal.mentions') }}" class="hover:text-accent hover:underline dark:hover:text-accent-light">Mentions légales</a></li>
                <li><a href="{{ route('legal.privacy') }}" class="hover:text-accent hover:underline dark:hover:text-accent-light">Confidentialité</a></li>
            </ul>
        </nav>

        <div id="newsletter" class="scroll-mt-24">
            <p class="text-xs font-bold uppercase tracking-widest text-[var(--news-muted)]">Lettre d'information</p>
            <p class="mt-4 text-sm text-[var(--news-muted)]">Recevez nos derniers articles par e-mail.</p>

            @if (session('newsletter_status'))
                <p class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" role="status">
                    {{ session('newsletter_status') }}
                </p>
            @else
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="news-newsletter mt-4 space-y-3">
                    @csrf
                    <div class="flex gap-2">
                        <label for="newsletter-email" class="sr-only">Adresse e-mail</label>
                        <input id="newsletter-email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}"
                               placeholder="vous@exemple.fr"
                               class="min-w-0 flex-1 rounded-lg border border-[var(--news-line)] bg-white px-3 py-2 text-sm focus:border-accent focus:outline-none dark:bg-[#0f0c16]">
                        <button type="submit" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-accent-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                            S'inscrire
                        </button>
                    </div>
                    <label class="flex items-start gap-2 text-xs text-[var(--news-muted)]">
                        <input type="checkbox" name="consent" value="1" required class="mt-0.5 accent-accent">
                        <span>J'accepte de recevoir la lettre d'information. Désinscription possible à tout moment (<a href="{{ route('legal.privacy') }}" class="underline">confidentialité</a>).</span>
                    </label>
                    <x-anti-bot key="newsletter" error-bag="newsletter" />
                    @if ($errors->newsletter->hasAny(['email', 'consent']))
                        <p class="text-sm text-rose" role="alert">{{ $errors->newsletter->first('email') ?: $errors->newsletter->first('consent') }}</p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</footer>

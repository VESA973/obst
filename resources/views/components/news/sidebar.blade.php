{{-- Barre latérale : articles récents numérotés et liste des rubriques. --}}
@props(['recent', 'categories'])

<aside {{ $attributes->class('space-y-10') }} aria-label="Compléments">
    @if ($recent->isNotEmpty())
        <section aria-labelledby="sidebar-recent">
            <h2 id="sidebar-recent" class="border-b-2 border-accent pb-2 font-serif text-xl font-bold">Les plus récents</h2>
            <ol class="mt-4 divide-y divide-[var(--news-line)]">
                @foreach ($recent as $article)
                    <li class="group relative flex gap-4 py-4">
                        <span class="font-serif text-3xl font-bold leading-none text-accent/30 dark:text-accent-light/40" aria-hidden="true">{{ $loop->iteration }}</span>
                        <div class="min-w-0">
                            <a href="{{ $article->publicUrl() }}" class="line-clamp-3 font-semibold leading-snug group-hover:text-accent dark:group-hover:text-accent-light">
                                {{ $article->title }}
                            </a>
                            <x-news.meta :article="$article" :reading-time="false" class="mt-1" />
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    @if ($categories->isNotEmpty())
        <section aria-labelledby="sidebar-categories">
            <h2 id="sidebar-categories" class="border-b-2 border-accent pb-2 font-serif text-xl font-bold">Rubriques</h2>
            <ul class="mt-4 space-y-1">
                @foreach ($categories as $category)
                    <li>
                        <a href="{{ \App\Support\EditorialSection::categoryUrl($category) }}"
                           style="--cat: {{ \App\Support\NewsBlocks::color($category) }}"
                           class="flex items-center justify-between rounded-md px-3 py-2 transition-colors hover:bg-[var(--news-surface)]">
                            <span class="flex items-center gap-2.5">
                                <span class="size-2.5 rounded-full bg-[var(--cat)]" aria-hidden="true"></span>
                                {{ $category->name }}
                            </span>
                            <span class="rounded-full bg-[var(--news-surface)] px-2 py-0.5 text-xs text-[var(--news-muted)]">{{ $category->articles_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</aside>

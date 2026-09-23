{{-- Bandeau « Dernière minute » (config news.ticker). Le contenu est doublé pour un défilement continu. --}}
@php($breaking = \App\Support\NewsBlocks::latestTitles())

@if ($breaking->isNotEmpty())
    <div class="news-ticker border-b border-[var(--news-line)] bg-[var(--news-surface)]">
        <div class="mx-auto flex max-w-7xl items-center gap-4 overflow-hidden px-4 py-2 sm:px-6 lg:px-8">
            <span class="shrink-0 rounded bg-rose px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-white">Dernière minute</span>
            <div class="min-w-0 flex-1 overflow-hidden">
                <div class="news-ticker-track">
                    @foreach ([false, true] as $duplicate)
                        <ul class="flex shrink-0 items-center gap-8 pr-8" @if ($duplicate) aria-hidden="true" @endif>
                            @foreach ($breaking as $article)
                                <li class="shrink-0 text-sm">
                                    <a href="{{ $article->publicUrl() }}" @if ($duplicate) tabindex="-1" @endif class="hover:text-accent hover:underline dark:hover:text-accent-light">
                                        <time class="mr-1.5 font-semibold text-rose" datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->format('H:i') }}</time>
                                        {{ $article->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

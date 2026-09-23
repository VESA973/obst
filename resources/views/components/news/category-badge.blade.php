{{-- Badge coloré menant à la catégorie. --}}
@props(['category', 'size' => 'sm'])

@if ($category)
    <a href="{{ \App\Support\EditorialSection::categoryUrl($category) }}"
       style="--cat: {{ \App\Support\NewsBlocks::color($category) }}"
       {{ $attributes->class([
           'news-card-above inline-block rounded-sm bg-[var(--cat)] font-bold uppercase tracking-wider text-white transition-opacity hover:opacity-85 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--cat)]',
           'px-2 py-0.5 text-[0.68rem]' => $size === 'sm',
           'px-2.5 py-1 text-xs' => $size === 'md',
       ]) }}>{{ $category->name }}</a>
@endif

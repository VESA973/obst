{{-- Sommaire rempli par news.js depuis les titres H2 / H3 du contenu ; masqué s'il y a moins de deux titres. --}}
<nav data-article-toc hidden aria-labelledby="article-toc-title"
    {{ $attributes->class('news-toc rounded-xl border border-[var(--news-line)] bg-[var(--news-surface)] p-5 xl:border-0 xl:bg-transparent xl:p-0') }}>
    <p id="article-toc-title" class="mb-3 text-xs font-bold uppercase tracking-widest text-[var(--news-muted)]">Sommaire</p>
    <ol data-article-toc-list class="border-l border-[var(--news-line)]"></ol>
</nav>

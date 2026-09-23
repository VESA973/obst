{{-- Cartes d'une page de résultats ; aussi renvoyé seul pour le bouton « Charger plus ». --}}
@foreach ($articles as $article)
    @continue($skipFirst && $loop->first && $articles->onFirstPage())
    <x-news.card :article="$article" data-news-item />
@endforeach

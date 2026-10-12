{{--
    Accès rapides vers les pages existantes du site, chevauchant le bas de la grande photo.
--}}
@php
    // Photos d'illustration : public/images/quick/<clé>.webp (crédits dans les mentions légales).
    $items = [
        ['href' => route('public'), 'image' => 'public', 'title' => 'Fiches santé', 'text' => 'Repères pour chaque étape de la vie'],
        ['href' => route('news'), 'image' => 'news', 'title' => 'Actualités', 'text' => 'Tribunes, interviews, publications'],
        ['href' => route('agenda'), 'image' => 'agenda', 'title' => 'Agenda', 'text' => 'Événements et inscriptions'],
        ['href' => route('pro'), 'image' => 'pro', 'title' => 'Espace pro', 'text' => 'Ressources pour les soignants'],
        ['href' => route('contact'), 'image' => 'contact', 'title' => 'Contact', 'text' => 'Une question ? Écrivez-nous'],
    ];
@endphp

<nav class="home-quick" aria-label="Accès rapides">
    <ul>
        @foreach ($items as $item)
            <li>
                <a class="home-quick-card" href="{{ $item['href'] }}">
                    <img class="home-quick-img" src="{{ asset('images/quick/'.$item['image'].'.webp') }}" alt="" width="640" height="400" loading="lazy" decoding="async">
                    <span class="home-quick-text">
                        <strong>{{ $item['title'] }}</strong>
                        <span>{{ $item['text'] }}</span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

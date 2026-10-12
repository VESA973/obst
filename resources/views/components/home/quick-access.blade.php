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
                    <span class="home-quick-media"><img src="{{ asset('images/quick/'.$item['image'].'.webp') }}" alt="" width="640" height="400" loading="lazy" decoding="async"></span>
                    <span class="home-quick-text">
                        <strong>
                            {{ $item['title'] }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </strong>
                        <span>{{ $item['text'] }}</span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

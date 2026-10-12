{{--
    Accès rapides vers les pages existantes du site, chevauchant le bas de la grande photo.
--}}
@php
    // Icônes au trait (style Lucide, 24×24).
    $items = [
        ['href' => route('public'), 'title' => 'Fiches santé', 'text' => 'Repères pour chaque étape de la vie', 'icon' => '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/>'],
        ['href' => route('news'), 'title' => 'Actualités', 'text' => 'Tribunes, interviews, publications', 'icon' => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8Z"/>'],
        ['href' => route('agenda'), 'title' => 'Agenda', 'text' => 'Événements et inscriptions', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>'],
        ['href' => route('pro'), 'title' => 'Espace pro', 'text' => 'Ressources pour les soignants', 'icon' => '<path d="M11 2v2M5 2v2M5 3H4a2 2 0 0 0-2 2v4a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-1"/><path d="M8 15a6 6 0 0 0 12 0v-3"/><circle cx="20" cy="10" r="2"/>'],
        ['href' => route('contact'), 'title' => 'Contact', 'text' => 'Une question ? Écrivez-nous', 'icon' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>'],
    ];
@endphp

<nav class="home-quick" aria-label="Accès rapides">
    <ul>
        @foreach ($items as $item)
            <li>
                <a class="home-quick-card" href="{{ $item['href'] }}">
                    <span class="home-quick-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
                    </span>
                    <span class="home-quick-text">
                        <strong>{{ $item['title'] }}</strong>
                        <span>{{ $item['text'] }}</span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

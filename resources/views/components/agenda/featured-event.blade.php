{{-- Prochain événement mis en avant : image, date, compte à rebours (agenda.js), lieu, bouton. --}}
@props(['item'])

@php
    $event = $item->event;
    $type = $item->type();
    $start = $item->startsAt();
@endphp

<article class="agenda-featured home-reveal" style="--type-color: {{ $type['color'] }}" aria-labelledby="agenda-featured-title">
    <div class="agenda-featured-media">
        @if ($url = $item->imageUrl())
            <img src="{{ $url }}" alt="" fetchpriority="high" decoding="async" style="object-position: {{ $event->flyer_position ?? 'center' }}">
        @else
            <div class="assoc-img assoc-img--placeholder"><x-home.leaf-pattern variant="palm" class="assoc-img-leaf" /></div>
        @endif
        <x-agenda.date-block :date="$start" class="agenda-featured-date" />
    </div>

    <div class="agenda-featured-body">
        <p class="home-eyebrow">Prochain événement</p>
        <x-agenda.type-badge :type="$type" />
        <h2 id="agenda-featured-title">{{ $event->title }}</h2>

        @if ($start)
            {{-- Compte à rebours : valeurs calculées côté serveur, mises à jour chaque minute par agenda.js. --}}
            <div class="agenda-countdown" data-countdown="{{ $start->toIso8601String() }}" role="timer" aria-live="off">
                <div><strong data-unit="days">{{ max(0, (int) now()->diffInDays($start, false)) }}</strong><span>jours</span></div>
                <div><strong data-unit="hours">{{ max(0, (int) now()->diffInHours($start, false) % 24) }}</strong><span>heures</span></div>
                <div><strong data-unit="minutes">{{ max(0, (int) now()->diffInMinutes($start, false) % 60) }}</strong><span>minutes</span></div>
            </div>
        @endif

        <ul class="agenda-card-meta">
            @if ($start)
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>{{ $start->translatedFormat('l j F Y') }}@if ($item->timeRange()), {{ $item->timeRange() }}@endif</li>
            @endif
            @if ($event->location)
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>{{ $event->location }}</li>
            @endif
        </ul>

        <a class="btn primary" href="{{ route('agenda.show', $event) }}">Voir le détail<span class="sr-only"> : {{ $event->title }}</span></a>
    </div>
</article>

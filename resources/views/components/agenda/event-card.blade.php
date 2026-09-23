{{--
    Carte horizontale d'un événement (vue liste). Les attributs data-* servent aux filtres (agenda.js).
    L'ancre id="event-{id}" est conservée : les QR codes imprimés pointent vers /agenda#event-{id}.
--}}
@props(['item', 'past' => false])

@php
    $event = $item->event;
    $type = $item->type();
    $start = $item->startsAt();
    $search = \Illuminate\Support\Str::of($event->title.' '.$event->location.' '.$event->description)->ascii()->lower()->squish();
    $remaining = $item->remainingPlaces();
@endphp

<article id="event-{{ $event->id }}"
    {{ $attributes->class(['agenda-card', 'agenda-card--past' => $past, 'home-card--link']) }}
    style="--type-color: {{ $type['color'] }}"
    data-agenda-item
    data-type="{{ $type['key'] }}"
    data-commune="{{ $item->commune() }}"
    data-month="{{ $start?->format('Y-m') }}"
    data-days="{{ $item->slots()->map(fn ($slot) => $slot['date']->format('Y-m-d'))->implode(',') }}"
    data-search="{{ $search }}">
    <x-agenda.date-block :date="$start" :muted="$past" />

    <div class="agenda-card-body">
        <div class="agenda-card-tags">
            <x-agenda.type-badge :type="$type" />
            @if (! $past && $remaining === 0)
                <span class="agenda-badge agenda-badge--full">Complet</span>
            @elseif (! $past && $remaining !== null && $remaining <= 10)
                <span class="agenda-badge agenda-badge--soon">Plus que {{ $remaining }} place{{ $remaining > 1 ? 's' : '' }}</span>
            @endif
        </div>
        <h3><a class="home-card-link" href="{{ route('agenda.show', $event) }}">{{ $event->title }}</a></h3>
        <ul class="agenda-card-meta">
            @if ($start)
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    <span class="sr-only">Date : </span>{{ $start->translatedFormat('l j F Y') }}@if ($item->timeRange()), {{ $item->timeRange() }}@endif
                    @if ($item->slots()->count() > 1)
                        <span class="agenda-card-more">(+{{ $item->slots()->count() - 1 }} date{{ $item->slots()->count() > 2 ? 's' : '' }})</span>
                    @endif
                </li>
            @endif
            @if ($event->location)
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    <span class="sr-only">Lieu : </span>{{ $event->location }}
                </li>
            @endif
        </ul>
        @if (! $past && $item->excerpt())
            <p class="agenda-card-excerpt">{{ $item->excerpt(170) }}</p>
        @endif
    </div>

    @if ($url = $item->imageUrl())
        <div class="agenda-card-media">
            <img src="{{ $url }}" alt="" loading="lazy" decoding="async">
        </div>
    @endif
</article>

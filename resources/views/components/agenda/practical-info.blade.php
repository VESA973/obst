{{-- Encadré « Informations pratiques » de la fiche (collant sur ordinateur), selon les champs existants. --}}
@props(['item'])

@php
    $event = $item->event;
    $remaining = $item->remainingPlaces();
    $url = route('agenda.show', $event);
    $share = [
        'WhatsApp' => ['https://wa.me/?text='.urlencode($event->title.' '.$url), 'M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Z'],
        'Facebook' => ['https://www.facebook.com/sharer/sharer.php?u='.urlencode($url), 'M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8Z'],
    ];
@endphp

<aside {{ $attributes->class('agenda-info') }} aria-labelledby="agenda-info-title">
    <h2 id="agenda-info-title">Informations pratiques</h2>

    <dl>
        <div>
            <dt>{{ $item->slots()->count() > 1 ? 'Dates' : 'Date' }}</dt>
            <dd>
                @forelse ($item->slots() as $slot)
                    <span class="agenda-info-slot">
                        @if ($slot['label'])<strong>{{ $slot['label'] }} :</strong>@endif
                        {{ $slot['date']->translatedFormat('l j F Y') }}
                        @if ($slot['start']) <br><span class="agenda-info-time">{{ $item->timeRange($slot) }}</span>@endif
                    </span>
                @empty
                    Date à venir
                @endforelse
            </dd>
        </div>
        @if ($event->location)
            <div>
                <dt>Lieu</dt>
                <dd>{{ $event->location }}</dd>
            </div>
        @endif
        <div>
            <dt>Participation</dt>
            <dd>
                {{ $event->is_free_entry ? 'Entrée libre' : ($event->is_paid ? 'Payante' : 'Gratuite') }}
                @if ($remaining !== null)
                    <br><span class="agenda-info-time">{{ $remaining > 0 ? $remaining.' place'.($remaining > 1 ? 's' : '').' restante'.($remaining > 1 ? 's' : '') : 'Complet' }}</span>
                @endif
            </dd>
        </div>
        <div>
            <dt>Contact</dt>
            <dd><a href="{{ route('contact') }}">Nous écrire</a></dd>
        </div>
    </dl>

    @unless ($item->isPast())
        @unless ($event->is_free_entry)
            <a class="btn primary agenda-info-cta" href="#inscription">{{ $event->is_paid ? 'S\'inscrire (billetterie)' : 'S\'inscrire' }}</a>
        @endunless

        @if ($item->startsAt())
            <div class="agenda-info-calendar">
                <p>Ajouter à mon agenda</p>
                <a href="{{ route('agenda.ics', $event) }}" download>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></svg>
                    Fichier .ics <span class="sr-only">(Apple, Outlook, Android)</span>
                </a>
                <a href="{{ $item->googleCalendarUrl() }}" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    Google Agenda <span class="sr-only">(nouvelle fenêtre)</span>
                </a>
            </div>
        @endif
    @endunless

    <div class="agenda-info-share">
        <p>Partager</p>
        <div>
            @foreach ($share as $network => [$href, $path])
                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" @class(['agenda-share', 'agenda-share--whatsapp' => $network === 'WhatsApp'])>
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $path }}"/></svg>
                    {{ $network }}<span class="sr-only"> (nouvelle fenêtre)</span>
                </a>
            @endforeach
            <button type="button" class="agenda-share" data-copy-link="{{ $url }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>
                <span data-copy-label>Copier le lien</span>
            </button>
        </div>
    </div>
</aside>

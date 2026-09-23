{{-- Bloc date : jour en grand, mois abrégé (et année si ce n'est pas l'année en cours), aux couleurs du site. --}}
@props(['date', 'muted' => false])

<div {{ $attributes->class(['agenda-date', 'agenda-date--muted' => $muted]) }} aria-hidden="true">
    @if ($date)
        <span class="agenda-date-weekday">{{ $date->translatedFormat('D') }}</span>
        <span class="agenda-date-day">{{ $date->format('j') }}</span>
        <span class="agenda-date-month">{{ $date->translatedFormat('M') }}@if (! $date->isCurrentYear()) {{ $date->format('Y') }}@endif</span>
    @else
        <span class="agenda-date-month">À venir</span>
    @endif
</div>

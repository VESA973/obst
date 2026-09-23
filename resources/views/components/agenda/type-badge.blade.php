{{-- Badge du type d'événement (déduit du titre, config/agenda.php). --}}
@props(['type'])

<span {{ $attributes->class('agenda-badge') }} style="--type-color: {{ $type['color'] }}">{{ $type['label'] }}</span>

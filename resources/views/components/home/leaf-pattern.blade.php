{{--
    Motif végétal amazonien (feuille de palmier, fougère ou liane) en SVG, décoratif.
    La couleur vient de `color` (currentColor) et la discrétion de l'opacité définie en CSS.
    Usage : <x-home.leaf-pattern variant="palm" class="home-leaf home-leaf--top-right" />
--}}
@props(['variant' => 'palm'])

<svg {{ $attributes->merge(['class' => 'home-leaf-svg']) }} viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-linecap="round" aria-hidden="true" focusable="false">
    @if ($variant === 'fern')
        {{-- Fougère : tige courbe et folioles alternées qui rétrécissent vers la pointe. --}}
        <path d="M30 190 C70 140 110 90 170 20" stroke-width="3"/>
        @foreach ([[48, 168, 26], [62, 150, 28], [77, 132, 29], [92, 114, 28], [108, 96, 26], [124, 78, 23], [140, 60, 19], [155, 42, 14]] as [$x, $y, $l])
            <path d="M{{ $x }} {{ $y }} q{{ -$l * 0.9 }} {{ -$l * 0.15 }} {{ -$l }} {{ -$l * 0.7 }}" stroke-width="2"/>
            <path d="M{{ $x }} {{ $y }} q{{ $l * 0.2 }} {{ $l * 0.85 }} {{ $l * 0.75 }} {{ $l * 0.95 }}" stroke-width="2"/>
        @endforeach
    @elseif ($variant === 'liana')
        {{-- Liane : tige ondulante et feuilles en cœur. --}}
        <path d="M10 10 C60 40 20 80 70 110 S80 170 140 190" stroke-width="2.5"/>
        @foreach ([[38, 38, 20], [46, 86, -25], [74, 116, 30], [96, 158, -20], [128, 184, 15]] as [$x, $y, $r])
            <path d="M{{ $x }} {{ $y }} c-8 -10 -22 -4 -18 8 c3 10 18 14 18 14 s15 -4 18 -14 c4 -12 -10 -18 -18 -8Z" stroke-width="1.8" transform="rotate({{ $r }} {{ $x }} {{ $y }})"/>
        @endforeach
    @else
        {{-- Palme : nervure centrale et longues folioles de part et d'autre. --}}
        <path d="M20 180 Q90 110 185 25" stroke-width="3"/>
        @foreach (range(0, 9) as $i)
            @php
                $t = 0.08 + $i * 0.09;
                $x = 20 + 165 * $t;
                $y = 180 - 155 * $t + 18 * sin($t * 3.14);
                $l = 62 - $i * 4.5;
            @endphp
            <path d="M{{ round($x, 1) }} {{ round($y, 1) }} q{{ round(-$l * 0.35, 1) }} {{ round(-$l * 0.55, 1) }} {{ round(-$l * 0.2, 1) }} {{ round(-$l, 1) }}" stroke-width="2"/>
            <path d="M{{ round($x, 1) }} {{ round($y, 1) }} q{{ round($l * 0.6, 1) }} {{ round($l * 0.1, 1) }} {{ round($l * 0.95, 1) }} {{ round($l * 0.45, 1) }}" stroke-width="2"/>
        @endforeach
    @endif
</svg>

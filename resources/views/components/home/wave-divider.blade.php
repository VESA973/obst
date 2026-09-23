{{--
    Séparateur en vague douce, évoquant le cours d'un fleuve (Maroni, Oyapock, Approuague).
    `fill` : couleur de la section suivante (variable CSS du site) ; `flip` : vague inversée.
--}}
@props(['fill' => 'var(--white)', 'flip' => false])

<div {{ $attributes->merge(['class' => 'home-wave'.($flip ? ' home-wave--flip' : '')]) }} aria-hidden="true">
    <svg viewBox="0 0 1440 90" preserveAspectRatio="none" focusable="false">
        {{-- Deux courants superposés : un reflet plus clair, puis la rive pleine. --}}
        <path d="M0 52 C180 20 330 78 520 56 C700 35 820 6 1010 30 C1180 52 1310 72 1440 44 V90 H0Z" style="fill: {{ $fill }}; opacity: .45"/>
        <path d="M0 64 C200 40 360 88 560 70 C760 52 880 26 1060 46 C1220 64 1330 80 1440 60 V90 H0Z" style="fill: {{ $fill }}"/>
    </svg>
</div>

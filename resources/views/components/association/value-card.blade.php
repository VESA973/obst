{{-- Carte « mission / valeur » : icône au trait, numéro, titre et texte. --}}
@props(['number' => null, 'title', 'icon'])

<article {{ $attributes->class('assoc-value') }}>
    <div class="assoc-value-head">
        <span class="assoc-value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
        </span>
        @if ($number)
            <span class="assoc-value-number" aria-hidden="true">{{ $number }}</span>
        @endif
    </div>
    <h3>{{ $title }}</h3>
    <p>{{ $slot }}</p>
</article>

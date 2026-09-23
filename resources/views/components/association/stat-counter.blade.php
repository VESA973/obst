{{--
    Chiffre clé avec compteur animé au défilement (resources/js/association.js).
    La valeur finale est écrite dans la page : sans JavaScript ou en « réduire les animations », elle s'affiche directement.
--}}
@props(['value', 'suffix' => '', 'label'])

<div {{ $attributes->class('assoc-stat') }}>
    <strong><span data-count-to="{{ (int) $value }}">{{ (int) $value }}</span>{{ $suffix }}</strong>
    <span>{{ $label }}</span>
</div>

{{--
    Image d'un emplacement de la page « L'association » (WebP responsive, lazy loading),
    ou visuel de remplacement : dégradé aux couleurs du site et feuille de palmier.
--}}
@props(['image' => null, 'sizes' => '100vw', 'eager' => false, 'leaf' => 'palm'])

@php($responsive = $image ? \App\Support\ResponsiveImage::for($image->path) : null)

@if ($responsive)
    <img src="{{ $responsive['src'] }}"
         @if ($responsive['srcset']) srcset="{{ $responsive['srcset'] }}" sizes="{{ $sizes }}" @endif
         alt="{{ $image->alt }}"
         @if ($eager) fetchpriority="high" @else loading="lazy" @endif
         decoding="async"
         {{ $attributes->merge(['class' => 'assoc-img']) }}>
@else
    <div {{ $attributes->merge(['class' => 'assoc-img assoc-img--placeholder']) }} aria-hidden="true">
        <x-home.leaf-pattern :variant="$leaf" class="assoc-img-leaf" />
    </div>
@endif

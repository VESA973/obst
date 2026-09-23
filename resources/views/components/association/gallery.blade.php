{{--
    Galerie photos (Filament > Contenus > Page L'association) : grille responsive,
    ouverture en grand dans une visionneuse (GLightbox) avec légendes et navigation au clavier.
    Sans JavaScript, chaque vignette ouvre simplement l'image.
--}}
@props(['images'])

@if ($images->isNotEmpty())
    <ul {{ $attributes->class('assoc-gallery') }}>
        @foreach ($images as $image)
            @php($responsive = \App\Support\ResponsiveImage::for($image->path))
            <li @class(['assoc-gallery-item', 'assoc-gallery-item--wide' => $loop->index % 5 === 0])>
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                   class="assoc-gallery-link glightbox"
                   data-gallery="association"
                   @if ($image->caption) data-description="{{ $image->caption }}" @endif
                   data-title="{{ $image->alt }}">
                    @if ($responsive)
                        <img src="{{ $responsive['src'] }}" @if ($responsive['srcset']) srcset="{{ $responsive['srcset'] }}" sizes="(min-width: 1024px) 33vw, 50vw" @endif
                             alt="{{ $image->alt }}" loading="lazy" decoding="async">
                    @endif
                    <span class="sr-only">Agrandir la photo</span>
                </a>
                @if ($image->caption)
                    <p class="assoc-gallery-caption">{{ $image->caption }}</p>
                @endif
            </li>
        @endforeach
    </ul>
@endif

{{--
    Fiche d'un événement (/agenda/{id}) : bannière, fil d'Ariane, type, titre, description, créneaux,
    photos et documents, inscription (formulaire existant) ; encadré pratique collant ; carte de la commune ;
    autres événements en carrousel ; JSON-LD Event.
--}}
@extends('layouts.site')

@php
    $event = $item->event;
    $type = $item->type();
    $start = $item->startsAt();
    $photos = $event->assets->where('type', 'photo');
    $documents = $event->assets->where('type', 'document');
    $coordinates = $item->coordinates();
@endphp

@section('title', $event->title.' | Agenda | La Quinzaine Obstétricale')
@section('description', $item->excerpt(160))
@section('body_class', 'home-page agenda-page')

@section('content')
    <x-news.seo :title="$event->title" :description="$item->excerpt(160)" :canonical="route('agenda.show', $event)"
                :image="$event->flyer_is_image ? $event->image_path : null" />

    <section class="assoc-hero agenda-hero agenda-hero--event" style="--type-color: {{ $type['color'] }}" aria-labelledby="event-title">
        @if ($url = $item->imageUrl())
            <img class="assoc-img assoc-hero-img" src="{{ $url }}" alt="" fetchpriority="high" style="object-position: {{ $event->banner_position ?? 'center' }}">
        @else
            <div class="assoc-img assoc-img--placeholder assoc-hero-img"></div>
        @endif
        <div class="assoc-hero-overlay" aria-hidden="true"></div>
        <div class="home-container assoc-hero-content">
            <nav aria-label="Fil d'Ariane" class="assoc-breadcrumb">
                <ol>
                    <li><a href="{{ route('home') }}">Accueil</a></li>
                    <li><a href="{{ route('agenda') }}">Agenda</a></li>
                    <li aria-current="page">{{ \Illuminate\Support\Str::limit($event->title, 50) }}</li>
                </ol>
            </nav>
            <div class="agenda-hero-tags">
                <x-agenda.type-badge :type="$type" />
                @if ($item->isPast())
                    <span class="agenda-badge agenda-badge--past">Événement passé</span>
                @endif
            </div>
            <h1 id="event-title" class="agenda-event-title">{{ $event->title }}</h1>
            @if ($start)
                <p class="lead">{{ ucfirst($start->translatedFormat('l j F Y')) }}@if ($item->timeRange()) · {{ $item->timeRange() }}@endif @if ($event->location) · {{ $event->location }}@endif</p>
            @endif
        </div>
        <x-home.wave-divider fill="var(--mint)" class="home-wave--hero" />
    </section>

    <div class="home-tinted agenda-main">
        <div class="home-container agenda-detail">
            <div class="agenda-detail-content">
                <section aria-labelledby="event-about">
                    <h2 id="event-about">À propos de l'événement</h2>
                    @foreach ($item->paragraphs() as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </section>

                @if ($item->slots()->count() > 1)
                    <section aria-labelledby="event-program">
                        <h2 id="event-program">Programme</h2>
                        <ol class="agenda-program">
                            @foreach ($item->slots() as $slot)
                                <li>
                                    <x-agenda.date-block :date="$slot['date']" />
                                    <div>
                                        <strong>{{ $slot['label'] ?: ucfirst($slot['date']->translatedFormat('l j F')) }}</strong>
                                        @if ($slot['start'])<span>{{ $item->timeRange($slot) }}</span>@endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                @if ($event->image_path && ! $event->flyer_is_image)
                    <p><a class="home-link-arrow" href="{{ Storage::url($event->image_path) }}" target="_blank" rel="noopener">Ouvrir le flyer ({{ $event->flyer_extension }})</a></p>
                @endif

                @if ($photos->isNotEmpty())
                    <section aria-labelledby="event-photos">
                        <h2 id="event-photos">Photos</h2>
                        <ul class="assoc-gallery">
                            @foreach ($photos as $photo)
                                <li class="assoc-gallery-item">
                                    <a class="assoc-gallery-link glightbox" data-gallery="event" href="{{ Storage::url($photo->path) }}">
                                        <img src="{{ Storage::url($photo->path) }}" alt="Photo {{ $loop->iteration }} — {{ $event->title }}" loading="lazy">
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($documents->isNotEmpty())
                    <section aria-labelledby="event-documents">
                        <h2 id="event-documents">Documents</h2>
                        <ul class="agenda-documents">
                            @foreach ($documents as $document)
                                <li><a href="{{ Storage::url($document->path) }}" target="_blank" rel="noopener">{{ $document->title ?: $document->original_name ?: 'Document associé' }}</a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Inscription (fonctionnement existant : formulaire gratuit ou lien de billetterie). --}}
                @unless ($item->isPast())
                    <section id="inscription" class="agenda-register" aria-labelledby="event-register">
                        <h2 id="event-register">Inscription</h2>
                        @if ($event->is_free_entry)
                            <p>Entrée libre, sans inscription : venez directement !</p>
                        @elseif ($event->is_paid)
                            @if ($event->registration_url)
                                <p>L'inscription se fait sur la billetterie en ligne.</p>
                                <a class="btn primary" href="{{ $event->registration_url }}" target="_blank" rel="noopener noreferrer">S'inscrire<span class="sr-only"> (nouvelle fenêtre)</span></a>
                            @endif
                        @elseif ($item->remainingPlaces() === 0)
                            <p>Cet événement est complet. <a href="{{ route('contact') }}">Contactez-nous</a> pour être informé d'une prochaine date.</p>
                        @else
                            <form class="agenda-register-form" method="POST" action="{{ route('events.register', $event) }}">
                                @csrf
                                <label>Nom complet<input name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                                <label>E-mail<input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required></label>
                                <label>Téléphone (facultatif)<input name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel"></label>
                                @if ($errors->any())
                                    <p class="agenda-register-error" role="alert">{{ $errors->first() }}</p>
                                @endif
                                <button type="submit" class="btn primary">Je m'inscris</button>
                                @if ($event->registration_capacity)
                                    <small>{{ $event->registrations_count }} / {{ $event->registration_capacity }} inscrit(s)</small>
                                @endif
                            </form>
                        @endif
                        <div class="agenda-qr">
                            <img src="{{ route('events.qr', $event) }}" alt="QR code d'inscription pour {{ $event->title }}" width="120" height="120" loading="lazy">
                            <p>Scannez ce QR code pour retrouver l'événement sur votre téléphone.</p>
                        </div>
                    </section>
                @endunless

                {{-- Carte : position approximative de la commune (aucune adresse précise n'est enregistrée). --}}
                @if ($coordinates)
                    <section aria-labelledby="event-map">
                        <h2 id="event-map">Localisation</h2>
                        <div class="agenda-map" data-agenda-map data-lat="{{ $coordinates[0] }}" data-lng="{{ $coordinates[1] }}" data-label="{{ $item->commune() }}">
                            <p class="agenda-map-fallback">Carte de {{ $item->commune() }} (chargement…)</p>
                        </div>
                        <p class="agenda-map-note">Position approximative de la commune de {{ $item->commune() }}. Carte © contributeurs OpenStreetMap.</p>
                    </section>
                @endif
            </div>

            <x-agenda.practical-info :item="$item" class="agenda-detail-aside" />
        </div>

        @if ($others->isNotEmpty())
            <section class="home-section" aria-labelledby="agenda-others">
                <div class="home-container">
                    <x-home.section-title eyebrow="Agenda" title="Autres événements à venir" id="agenda-others" :href="route('agenda')" link-label="Tout l'agenda" />
                    <x-home.carousel label="Autres événements à venir">
                        @foreach ($others as $other)
                            <div class="swiper-slide">
                                <x-home.card :href="route('agenda.show', $other->event)" :title="$other->event->title" :text="$other->excerpt(120)"
                                             :date="$other->startsAt()" :image-url="$other->imageUrl()" :image-position="$other->event->flyer_position" :badge="$other->type()['label']" :meta="$other->event->location" />
                            </div>
                        @endforeach
                    </x-home.carousel>
                </div>
            </section>
        @endif
    </div>
@endsection

@push('head')
    @stack('news-head')
    @if ($start)
        <script type="application/ld+json">
            {!! json_encode(array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $event->title,
                'description' => $item->excerpt(300),
                'startDate' => $start->toIso8601String(),
                'endDate' => $item->endsAt()?->toIso8601String(),
                'eventStatus' => 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'location' => $event->location ? array_filter([
                    '@type' => 'Place',
                    'name' => $event->location,
                    'address' => array_filter(['@type' => 'PostalAddress', 'addressLocality' => $item->commune(), 'addressRegion' => 'Guyane', 'addressCountry' => 'FR']),
                ]) : null,
                'image' => $item->imageUrl() ? [$item->imageUrl()] : null,
                'isAccessibleForFree' => $event->is_free_entry || ! $event->is_paid,
                'organizer' => ['@type' => 'Organization', 'name' => 'La Quinzaine Obstétricale', 'url' => route('home')],
                'url' => route('agenda.show', $event),
            ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    @endif
    <script>
        if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('home-has-reveal');
        }
    </script>
    @vite('resources/js/agenda.js')
@endpush

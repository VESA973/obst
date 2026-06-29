@extends('layouts.site')

@section('title', 'Agenda | La Quinzaine Obstetricale')

@section('content')
<x-page-hero page-key="agenda" />

<section class="agenda-list">
    @forelse ($events as $event)
        @php
            $photos = $event->assets->where('type', 'photo');
            $documents = $event->assets->where('type', 'document');
        @endphp
        <details id="event-{{ $event->id }}" class="event-card">
            <summary class="event-summary">
                <time>{{ $event->event_date ? $event->event_date->translatedFormat('d M') : 'A venir' }}</time>
                <span class="event-thumb">
                    @if ($event->image_path)
                        @if ($event->flyer_is_image)
                            <img src="{{ Storage::url($event->image_path) }}" alt="">
                        @else
                            <span>{{ $event->flyer_extension }}</span>
                        @endif
                    @else
                        <span>Agenda</span>
                    @endif
                </span>
                <span class="event-summary-text">
                    <strong>{{ $event->title }}</strong>
                    @if ($event->location)
                        <span>{{ $event->location }}</span>
                    @endif
                </span>
                <span class="event-summary-action">Voir le detail</span>
            </summary>

            <div class="event-detail">
                <div class="event-visual">
                    @if ($event->image_path)
                        @if ($event->flyer_is_image)
                            <a href="{{ Storage::url($event->image_path) }}" target="_blank" rel="noreferrer" aria-label="Voir le flyer de {{ $event->title }}">
                                <img src="{{ Storage::url($event->image_path) }}" alt="Flyer {{ $event->title }}">
                            </a>
                        @else
                            <a class="event-file-link" href="{{ Storage::url($event->image_path) }}" target="_blank" rel="noreferrer">
                                <span>{{ $event->flyer_extension }}</span>
                                <strong>Ouvrir le flyer</strong>
                            </a>
                        @endif
                    @else
                        <div class="event-placeholder">Agenda</div>
                    @endif
                </div>
                <div class="event-content">
                    <h2>{{ $event->title }}</h2>
                    <p>{{ $event->description }}</p>
                    @if ($event->location)
                        <p class="event-meta">Lieu : {{ $event->location }}</p>
                    @endif
                    @if ($event->schedule_items)
                        <div class="event-schedules" aria-label="Dates et horaires">
                            @foreach ($event->schedule_items as $slot)
                                <span>
                                    {{ ! empty($slot['label']) ? $slot['label'].' - ' : '' }}
                                    {{ \Illuminate\Support\Carbon::parse($slot['date'])->translatedFormat('d F Y') }}
                                    @if (! empty($slot['start_time']))
                                        de {{ $slot['start_time'] }}
                                    @endif
                                    @if (! empty($slot['end_time']))
                                        a {{ $slot['end_time'] }}
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @elseif ($event->event_date)
                        <p class="event-meta">Date : {{ $event->event_date->translatedFormat('d F Y') }}</p>
                    @endif
                    @if ($photos->isNotEmpty())
                        <div class="event-gallery" aria-label="Photos de {{ $event->title }}">
                            @foreach ($photos as $photo)
                                <a href="{{ Storage::url($photo->path) }}" target="_blank" rel="noreferrer">
                                    <img src="{{ Storage::url($photo->path) }}" alt="">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if ($documents->isNotEmpty())
                        <div class="event-documents">
                            @foreach ($documents as $document)
                                <a href="{{ Storage::url($document->path) }}" target="_blank" rel="noreferrer">
                                    {{ $document->title ?: $document->original_name ?: 'Document associe' }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="event-qr">
                        <img src="{{ route('events.qr', $event) }}" alt="QR code inscription {{ $event->title }}">
                        <div>
                            <strong>QR code inscription</strong>
                            <span>{{ $event->is_paid ? 'Scannez pour ouvrir HelloAsso.' : 'Scannez pour ouvrir cet evenement.' }}</span>
                            <a href="{{ route('events.qr', $event) }}" target="_blank" rel="noreferrer">Ouvrir le QR code</a>
                        </div>
                    </div>
                    @if ($event->is_paid)
                        @if ($event->registration_url)
                            <a class="event-register-link" href="{{ $event->registration_url }}" target="_blank" rel="noreferrer">S'inscrire sur HelloAsso</a>
                        @endif
                    @else
                        <form class="event-registration-form" method="POST" action="{{ route('events.register', $event) }}">
                            @csrf
                            <input name="name" placeholder="Nom complet" required>
                            <input name="email" type="email" placeholder="Email" required>
                            <input name="phone" placeholder="Telephone">
                            <textarea name="notes" placeholder="Message optionnel"></textarea>
                            <button type="submit">S'inscrire gratuitement</button>
                            @if ($event->registration_capacity)
                                <small>{{ $event->registrations_count }} / {{ $event->registration_capacity }} inscrit(s)</small>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        </details>
    @empty
        <article>
            <time>Sept.</time>
            <div><h2>Les Jeudis M</h2><p>Rencontre autour de la menopause, de la prevention et de la qualite de vie. <a href="{{ route('contact') }}">S'inscrire</a></p></div>
        </article>
        <article>
            <time>Oct.</time>
            <div><h2>Escape Game Sante</h2><p>Animation de prevention pour apprendre autrement, en equipe et sur le terrain. <a href="{{ route('contact') }}">S'inscrire</a></p></div>
        </article>
        <article>
            <time>Nov.</time>
            <div><h2>Assises Amazoniennes</h2><p>Temps fort de partage entre professionnels, institutions, chercheurs et associations. <a href="{{ route('contact') }}">S'inscrire</a></p></div>
        </article>
    @endforelse
</section>
@endsection

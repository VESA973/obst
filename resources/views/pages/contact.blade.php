{{--
    Page Contact : hero (réglages « Pages du site »), formulaire enregistré en base (ContactController,
    Filament > Communauté > Messages), rappel des numéros d'urgence, coordonnées (config/contact.php,
    masquées si vides), inscription à la lettre d'information et liens utiles.
    Script : resources/js/contact.js (cette page uniquement).
--}}
@extends('layouts.site')

@php
    $page = \App\Models\PageSetting::forKey('contact');
    $heroImage = ! empty($page['hero_image_path'])
        ? (str_starts_with($page['hero_image_path'], 'images/') ? asset($page['hero_image_path']) : Storage::url($page['hero_image_path']))
        : null;
    $contact = config('contact');
    $hasDetails = filled($contact['email']) || filled($contact['phone']) || filled($contact['address']);
    $contactErrors = $errors->contact;
@endphp

@section('title', 'Contact | La Quinzaine Obstétricale')
@section('description', \Illuminate\Support\Str::limit($page['description'] ?? '', 160))
@section('body_class', 'home-page contact-page')

@section('content')
    <x-news.seo :title="($page['title'] ?? 'Contact').' — La Quinzaine Obstétricale'" :description="$page['description'] ?? null" :canonical="route('contact')" />

    <section class="assoc-hero contact-hero" aria-labelledby="contact-title">
        @if ($heroImage)
            <img class="assoc-img assoc-hero-img" src="{{ $heroImage }}" alt="" fetchpriority="high">
        @else
            <div class="assoc-img assoc-img--placeholder assoc-hero-img"></div>
        @endif
        <div class="assoc-hero-overlay" aria-hidden="true"></div>
        <x-home.leaf-pattern variant="fern" class="home-leaf home-leaf--hero-left" />
        <div class="home-container assoc-hero-content">
            <nav aria-label="Fil d'Ariane" class="assoc-breadcrumb home-appear">
                <ol><li><a href="{{ route('home') }}">Accueil</a></li><li aria-current="page">{{ $page['menu_label'] ?? 'Contact' }}</li></ol>
            </nav>
            <p class="eyebrow home-appear" style="--delay: 80ms">{{ $page['eyebrow'] ?? 'Contact' }}</p>
            <h1 id="contact-title" class="home-appear assoc-title-{{ $page['title_size'] ?? 'normal' }}" style="--delay: 160ms">{{ $page['title'] ?? 'Contact' }}</h1>
            @if (! empty($page['description']))
                <p class="lead home-appear" style="--delay: 240ms">{{ $page['description'] }}</p>
            @endif
        </div>
        <x-home.wave-divider fill="var(--mint)" class="home-wave--hero" />
    </section>

    <div class="home-tinted contact-main">
        <div class="home-container contact-grid">
            {{-- Formulaire --}}
            <section id="formulaire" class="contact-card contact-form-card home-reveal" aria-labelledby="contact-form-title">
                <p class="home-eyebrow">Formulaire</p>
                <h2 id="contact-form-title">Écrivez-nous</h2>

                @if (session('contact_status'))
                    <div class="contact-success" role="status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/></svg>
                        <p>{{ session('contact_status') }}</p>
                    </div>
                @endif

                <form class="contact-form" method="POST" action="{{ route('contact.send') }}" novalidate>
                    @csrf

                    @if ($contactErrors->any())
                        <div class="contact-errors" role="alert" tabindex="-1" data-contact-errors>
                            <p>Merci de corriger les points suivants :</p>
                            <ul>
                                @foreach ($contactErrors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <fieldset class="contact-topics">
                        <legend>Votre demande concerne <span aria-hidden="true">*</span></legend>
                        <div>
                            @foreach (\App\Models\ContactMessage::TOPICS as $value => $label)
                                <label class="contact-topic">
                                    <input type="radio" name="topic" value="{{ $value }}" required @checked(old('topic', 'question') === $value)>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="contact-fields">
                        <label>
                            <span>Nom et prénom <span aria-hidden="true">*</span></span>
                            <input name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="255" @if ($contactErrors->has('name')) aria-invalid="true" @endif>
                        </label>
                        <label>
                            <span>E-mail <span aria-hidden="true">*</span></span>
                            <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255" @if ($contactErrors->has('email')) aria-invalid="true" @endif>
                        </label>
                        <label>
                            <span>Téléphone <span class="contact-optional">(facultatif)</span></span>
                            <input name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="40">
                        </label>
                        <label>
                            <span>Sujet <span aria-hidden="true">*</span></span>
                            <input name="subject" value="{{ old('subject') }}" required maxlength="200" @if ($contactErrors->has('subject')) aria-invalid="true" @endif>
                        </label>
                        <label class="contact-field-wide">
                            <span>Message <span aria-hidden="true">*</span></span>
                            <textarea name="message" rows="7" required minlength="10" maxlength="5000" data-contact-message aria-describedby="contact-message-count" @if ($contactErrors->has('message')) aria-invalid="true" @endif>{{ old('message') }}</textarea>
                            <span class="contact-count" id="contact-message-count" data-contact-count>5000 caractères maximum</span>
                        </label>
                    </div>

                    <label class="contact-consent">
                        <input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
                        <span>J'accepte que mes données soient utilisées uniquement pour répondre à ma demande (<a href="{{ route('legal.privacy') }}">confidentialité</a>). <span aria-hidden="true">*</span></span>
                    </label>

                    <x-anti-bot key="contact" error-bag="contact" />

                    <div class="contact-submit">
                        <button type="submit" class="btn primary">
                            Envoyer le message
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                        <p><span aria-hidden="true">*</span> Champs obligatoires</p>
                    </div>
                </form>
            </section>

            <div class="contact-side">
                {{-- Le formulaire ne remplace pas une consultation : rappel des urgences. --}}
                <aside class="contact-card contact-urgent home-reveal" aria-labelledby="contact-urgent-title">
                    <span class="contact-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.5a2 2 0 0 0-1.9 1.4l-2.4 7.6a.25.25 0 0 1-.5 0L9.2 3a.25.25 0 0 0-.5 0L6.4 10.6A2 2 0 0 1 4.5 12H2"/></svg>
                    </span>
                    <h2 id="contact-urgent-title">Une urgence médicale ?</h2>
                    <p>Ce formulaire n'est pas adapté aux urgences ni aux demandes de conseil médical personnel.</p>
                    <div class="contact-urgent-numbers">
                        @foreach (config('home.emergency') as $emergency)
                            <a href="tel:{{ $emergency['number'] }}"><strong>{{ $emergency['number'] }}</strong><span>{{ $emergency['label'] }}</span></a>
                        @endforeach
                    </div>
                </aside>

                @if ($hasDetails)
                    <aside class="contact-card home-reveal" aria-labelledby="contact-details-title">
                        <h2 id="contact-details-title">Nos coordonnées</h2>
                        <ul class="contact-details">
                            @if (filled($contact['email']))
                                <li><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
                            @endif
                            @if (filled($contact['phone']))
                                <li><a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></li>
                            @endif
                            @if (filled($contact['address']))
                                <li>{{ $contact['address'] }}</li>
                            @endif
                        </ul>
                    </aside>
                @endif

                @if (! empty($contact['socials']))
                    <aside class="contact-card home-reveal" aria-labelledby="contact-social-title">
                        <p class="home-eyebrow">Réseaux sociaux</p>
                        <h2 id="contact-social-title">Suivre l'association</h2>
                        <p>Retrouvez les actualités, événements et campagnes de sensibilisation sur nos réseaux.</p>
                        <div class="contact-socials">
                            @foreach ($contact['socials'] as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $network }}<span class="sr-only"> (nouvelle fenêtre)</span></a>
                            @endforeach
                        </div>
                    </aside>
                @endif

                {{-- Lettre d'information : vraie inscription (NewsletterController). --}}
                <aside id="newsletter" class="contact-card contact-newsletter home-reveal" aria-labelledby="contact-newsletter-title">
                    <x-home.leaf-pattern variant="palm" class="contact-newsletter-leaf" />
                    <p class="home-eyebrow">Newsletter</p>
                    <h2 id="contact-newsletter-title">Recevoir les nouvelles</h2>
                    <p>Inscrivez-vous pour suivre les manifestations, ressources et appels à participation.</p>
                    @if (session('newsletter_status'))
                        <p class="contact-success-inline" role="status">{{ session('newsletter_status') }}</p>
                    @else
                        <form method="POST" action="{{ route('newsletter.subscribe') }}" class="contact-newsletter-form">
                            @csrf
                            <label class="sr-only" for="contact-newsletter-email">Adresse e-mail</label>
                            <input id="contact-newsletter-email" name="email" type="email" required autocomplete="email" placeholder="vous@exemple.fr" value="{{ old('email') }}">
                            <label class="contact-consent">
                                <input type="checkbox" name="consent" value="1" required>
                                <span>J'accepte de recevoir la lettre d'information.</span>
                            </label>
                            <x-anti-bot key="newsletter" error-bag="newsletter" />
                            @if ($errors->newsletter->hasAny(['email', 'consent']))
                                <p class="contact-field-error" role="alert">{{ $errors->newsletter->first('email') ?: $errors->newsletter->first('consent') }}</p>
                            @endif
                            <button type="submit" class="btn secondary">S'inscrire</button>
                        </form>
                    @endif
                </aside>

                <nav class="contact-card contact-links home-reveal" aria-labelledby="contact-links-title">
                    <h2 id="contact-links-title">Vous cherchez peut-être</h2>
                    <ul>
                        <li><a href="{{ route('public') }}">Les fiches santé <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a></li>
                        <li><a href="{{ route('agenda') }}">L'agenda des événements <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a></li>
                        <li><a href="{{ route('join') }}">Adhérer ou faire un don <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a></li>
                        <li><a href="{{ route('pro') }}">L'espace professionnel <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('head')
    @stack('news-head')
    <script>
        if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('home-has-reveal');
        }
    </script>
    @vite('resources/js/contact.js')
@endpush

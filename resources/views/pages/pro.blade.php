@extends('layouts.site')

@section('title', 'Espace professionnels | La Quinzaine Obstétricale')

@section('content')
<x-page-hero page-key="pro" class="pro-hero" />
@php($canAccessPro = auth()->check() && (auth()->user()->is_member || auth()->user()->is_admin) && auth()->user()->hasVerifiedEmail())

@guest
<section class="auth-gate auth-gate-top" id="connexion-pro">
    <div class="auth-copy">
        <p class="eyebrow">Connexion professionnelle</p>
        <h2>Accédez aux contenus réservés</h2>
        <p>Connectez-vous pour consulter les recommandations, protocoles, formations, replays, bibliothèque et ressources pédagogiques.</p>
    </div>

    <div class="auth-forms">
        <form class="auth-card login-card-featured" method="POST" action="{{ route('professional.login') }}">
            @csrf
            <h3>Connexion</h3>
            <label for="login-email">Email</label>
            <input id="login-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>

            <label for="login-password">Mot de passe</label>
            <input id="login-password" name="password" type="password" autocomplete="current-password" required>

            <label class="check-row">
                <input name="remember" type="checkbox" value="1">
                <span>Rester connecté</span>
            </label>

            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <button type="submit">Se connecter</button>
            <a href="{{ route('professional.password.request') }}">Mot de passe oublié ?</a>
            @if (\App\Models\SiteSetting::getValue('google_login_enabled', '0') === '1')
                <a class="google-login-link" href="{{ route('professional.google.redirect') }}">Se connecter avec Google</a>
            @endif
        </form>
    </div>
</section>
@endguest

@auth
    @unless ($canAccessPro)
        <section class="auth-gate auth-gate-top">
            <div class="auth-copy">
                <p class="eyebrow">Compte en attente</p>
                <h2>Confirmez votre email</h2>
                <p>Votre compte est créé, mais l’accès aux ressources professionnelles sera ouvert après confirmation de votre adresse email.</p>
            </div>
        </section>
    @endunless
@endauth

<section class="pro-intro">
    <div>
        <p class="eyebrow">Présentation</p>
        <h2>Un espace conçu pour les pratiques de terrain</h2>
        <p>Les contenus accompagnent la décision, la coordination et la transmission entre acteurs de santé, avec une attention particulière aux réalités guyanaises.</p>
    </div>
    <div class="pro-summary">
        <span>Contenu exclusif</span>
        <strong>{{ $canAccessPro ? 'Disponible' : 'Connexion requise' }}</strong>
    </div>
</section>

@if ($canAccessPro)
<section class="pro-layout private-content">
    <div class="pro-panel">
        <span class="private-label">Réservé pro</span>
        <h2>Recommandations</h2>
        <p>Synthèses pratiques, bonnes pratiques et documents de référence pour les équipes.</p>
        <a href="#">Consulter les dossiers</a>
    </div>
    <div class="pro-panel accent">
        <span class="private-label">Réservé pro</span>
        <h2>Protocoles</h2>
        <p>Outils d'orientation et supports de coordination entre ville, maternité, réseaux et associations.</p>
        <a href="#">Voir les supports</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Réservé pro</span>
        <h2>Formations</h2>
        <p>Journées thématiques, webinaires et retours d'expérience.</p>
        <a href="#">Accéder aux formations</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Réservé pro</span>
        <h2>Replays</h2>
        <p>Vidéos et supports des rencontres passées pour prolonger la formation.</p>
        <a href="#">Voir les replays</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Réservé pro</span>
        <h2>Bibliothèque</h2>
        <p>Articles, publications, notes scientifiques et ressources documentaires classées.</p>
        <a href="#">Ouvrir la bibliothèque</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Réservé pro</span>
        <h2>Ressources pédagogiques</h2>
        <p>Supports d'atelier, fiches patientes, outils de prévention et formats imprimables.</p>
        <a href="#">Voir les ressources</a>
    </div>
</section>
@endif
@endsection

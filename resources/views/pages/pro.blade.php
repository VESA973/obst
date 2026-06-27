@extends('layouts.site')

@section('title', 'Espace professionnels | La Quinzaine Obstetricale')

@section('content')
<section class="page-hero pro-hero">
    <p class="eyebrow">Espace professionnels</p>
    <h1>Ressources, coordination et formation continue</h1>
    <p>Un espace pour les sages-femmes, obstetriciens, internes, medecins generalistes, pediatres, psychologues, chercheurs et equipes impliquees dans le parcours perinatal.</p>
</section>

@guest
<section class="auth-gate auth-gate-top" id="connexion-pro">
    <div class="auth-copy">
        <p class="eyebrow">Connexion professionnelle</p>
        <h2>Accedez aux contenus reserves</h2>
        <p>Connectez-vous pour consulter les recommandations, protocoles, formations, replays, bibliotheque et ressources pedagogiques.</p>
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
                <span>Rester connecte</span>
            </label>

            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <button type="submit">Se connecter</button>
        </form>
    </div>
</section>
@endguest

<section class="pro-intro">
    <div>
        <p class="eyebrow">Presentation</p>
        <h2>Un espace concu pour les pratiques de terrain</h2>
        <p>Les contenus accompagnent la decision, la coordination et la transmission entre acteurs de sante, avec une attention particuliere aux realites guyanaises.</p>
    </div>
    <div class="pro-summary">
        <span>Contenu exclusif</span>
        <strong>@auth Disponible @else Connexion requise @endauth</strong>
    </div>
</section>

@auth
<section class="pro-layout private-content">
    <div class="pro-panel">
        <span class="private-label">Reserve pro</span>
        <h2>Recommandations</h2>
        <p>Syntheses pratiques, bonnes pratiques et documents de reference pour les equipes.</p>
        <a href="#">Consulter les dossiers</a>
    </div>
    <div class="pro-panel accent">
        <span class="private-label">Reserve pro</span>
        <h2>Protocoles</h2>
        <p>Outils d'orientation et supports de coordination entre ville, maternite, reseaux et associations.</p>
        <a href="#">Voir les supports</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Reserve pro</span>
        <h2>Formations</h2>
        <p>Journees thematiques, webinaires et retours d'experience.</p>
        <a href="#">Acceder aux formations</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Reserve pro</span>
        <h2>Replays</h2>
        <p>Videos et supports des rencontres passees pour prolonger la formation.</p>
        <a href="#">Voir les replays</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Reserve pro</span>
        <h2>Bibliotheque</h2>
        <p>Articles, publications, notes scientifiques et ressources documentaires classees.</p>
        <a href="#">Ouvrir la bibliotheque</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Reserve pro</span>
        <h2>Ressources pedagogiques</h2>
        <p>Supports d'atelier, fiches patientes, outils de prevention et formats imprimables.</p>
        <a href="#">Voir les ressources</a>
    </div>
</section>
@endauth
@endsection

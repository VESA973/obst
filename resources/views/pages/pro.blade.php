@extends('layouts.site')

@section('title', 'Espace pro | La Quinzaine Obstetricale')

@section('content')
<section class="page-hero pro-hero">
    <p class="eyebrow">Espace pro</p>
    <h1>Ressources, coordination et formation continue</h1>
    <p>Un espace pour les sages-femmes, obstetriciens, internes, medecins generalistes, pediatres et equipes impliquees dans le parcours perinatal.</p>
</section>

@guest
<section class="auth-gate auth-gate-top" id="connexion-pro">
    <div class="auth-copy">
        <p class="eyebrow">Connexion professionnelle</p>
        <h2>Accedez aux contenus reserves</h2>
        <p>Connectez-vous pour consulter les dossiers professionnels, supports de formation, notes de coordination et ressources partagees.</p>
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
        <p>La partie visible presente la demarche, les objectifs et les thematiques. Les dossiers, supports et ressources de travail sont reserves aux professionnels connectes.</p>
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
        <h2>Dossiers professionnels</h2>
        <p>Recommandations, syntheses pratiques, supports de reunion et outils d'orientation pour les equipes.</p>
        <a href="#">Consulter les dossiers</a>
    </div>
    <div class="pro-panel accent">
        <span class="private-label">Reserve pro</span>
        <h2>Coordination</h2>
        <p>Favoriser les echanges entre ville, maternite, reseaux de perinatalite et acteurs associatifs.</p>
        <a href="#">Voir les supports</a>
    </div>
    <div class="pro-panel">
        <span class="private-label">Reserve pro</span>
        <h2>Formation</h2>
        <p>Journees thematiques, webinaires, retours d'experience et ressources a partager en equipe.</p>
        <a href="#">Acceder aux formations</a>
    </div>
</section>
@endauth
@endsection

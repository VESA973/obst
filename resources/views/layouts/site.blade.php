<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'La Quinzaine Obstétricale')</title>
    <meta name="description" content="@yield('description', 'La Quinzaine Obstétricale agit pour la santé de la femme et de la périnatalité en Guyane.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="header-top">
            <a class="brand" href="{{ route('home') }}" aria-label="Accueil La Quinzaine Obstétricale">
                <img class="brand-logo" src="{{ asset('images/quinzaine-logo.jpeg') }}" alt="Quinzaine Obstétricale CHAR">
            </a>

            <div class="header-actions" aria-label="Actions rapides">
                @auth
                    <span class="account-chip">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('professional.logout') }}">
                        @csrf
                        <button type="submit">Déconnexion</button>
                    </form>
                @else
                    <button class="create-account-trigger" type="button" data-open-register>
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.2 0-7 2.1-7 4.2V20h14v-1.8c0-2.1-2.8-4.2-7-4.2Z"/>
                        </svg>
                        <span>Espace pro</span>
                    </button>
                @endauth
                <a class="nav-join" href="{{ route('join') }}">Nous soutenir</a>
            </div>
        </div>

        <div class="header-bottom">
            <span class="header-note">Santé de la femme et périnatalité en Guyane</span>
            <nav class="main-nav" aria-label="Navigation principale">
                <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Accueil</a>
                @foreach ($siteMenuPages as $menuPage)
                    @if ($menuPage['page_key'] === 'about')
                        @php($actionsPage = \App\Models\PageSetting::forKey('actions'))
                        <div @class(['nav-dropdown', 'active' => request()->routeIs('about') || request()->routeIs('actions')])>
                            <a href="{{ route($menuPage['route']) }}" @class(['active' => request()->routeIs($menuPage['route'])])>{{ $menuPage['menu_label'] }}</a>
                            <div class="nav-submenu" aria-label="Sous-menu association">
                                <a href="{{ route('actions') }}" @class(['active' => request()->routeIs('actions')])>{{ $actionsPage['menu_label'] ?? 'Nos actions' }}</a>
                            </div>
                        </div>
                    @elseif ($menuPage['page_key'] !== 'actions' && $menuPage['page_key'] !== 'research')
                        <a href="{{ route($menuPage['route']) }}" @class(['active' => request()->routeIs($menuPage['route'])])>{{ $menuPage['menu_label'] }}</a>
                    @endif
                @endforeach
            </nav>
        </div>
    </header>

    @guest
        <dialog class="register-modal" id="register-modal" aria-labelledby="register-modal-title">
            <button class="modal-close" type="button" data-close-register aria-label="Fermer la fenêtre">&times;</button>
            <div class="modal-heading">
                <p class="eyebrow">Compte professionnel</p>
                <h2 id="register-modal-title">Espace pro</h2>
                <p>Connectez-vous à votre compte professionnel ou créez un accès avec votre email.</p>
            </div>

            <div class="pro-auth-modal-grid">
                <form class="auth-card modal-login-form" method="POST" action="{{ route('professional.login') }}">
                    @csrf
                    <h3>Connexion</h3>
                    <label for="modal-login-email">Email</label>
                    <input id="modal-login-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>

                    <label for="modal-login-password">Mot de passe</label>
                    <input id="modal-login-password" name="password" type="password" autocomplete="current-password" required>

                    <label class="check-row">
                        <input name="remember" type="checkbox" value="1">
                        <span>Rester connecté</span>
                    </label>

                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <x-anti-bot key="professional_login" />

                    <button type="submit">Se connecter</button>
                    <a href="{{ route('professional.password.request') }}">Mot de passe oublié ?</a>
                    @if (\App\Models\SiteSetting::getValue('google_login_enabled', '0') === '1')
                        <a class="google-login-link" href="{{ route('professional.google.redirect') }}">Se connecter avec Google</a>
                    @endif
                </form>

                <form class="auth-card modal-register-form accent-card" method="POST" action="{{ route('professional.register') }}">
                    @csrf
                    <h3>Inscription</h3>
                    <label for="modal-register-name">Nom complet</label>
                    <input id="modal-register-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required>
                    @error('name', 'register')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <label for="modal-register-email">Email professionnel</label>
                    <input id="modal-register-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email', 'register')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <label for="modal-register-password">Mot de passe</label>
                    <input id="modal-register-password" name="password" type="password" autocomplete="new-password" required>
                    @error('password', 'register')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <label for="modal-register-password-confirmation">Confirmer le mot de passe</label>
                    <input id="modal-register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

                    <label class="check-row">
                        <input name="is_health_professional" type="checkbox" value="1" required>
                        <span>Je certifie être un professionnel de santé.</span>
                    </label>
                    @error('is_health_professional', 'register')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <x-anti-bot key="professional_register" error-bag="register" />

                    <button type="submit">Créer mon accès</button>
                </form>
            </div>
        </dialog>
    @endguest

    @if (session('account_created'))
        <dialog class="register-modal account-created-modal" open aria-labelledby="account-created-title">
            <div class="modal-heading">
                <p class="eyebrow">Compte créé</p>
                <h2 id="account-created-title">Confirmez votre email</h2>
                <p>Votre compte professionnel a bien été créé. Un email de confirmation vient de vous être envoyé. Cliquez sur le lien reçu pour activer votre accès à l’espace professionnel.</p>
            </div>
            <form method="dialog">
                <button type="submit">J’ai compris</button>
            </form>
        </dialog>
    @endif

    <main>
        @if (session('status'))
            <div class="flash-message">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer" id="footer-don">
        <div>
            <strong>La Quinzaine Obstétricale</strong>
            <p>Association guyanaise dédiée à la santé de la femme, à la périnatalité, à la prévention et à la transmission.</p>
        </div>
        <nav class="footer-legal" aria-label="Liens légaux">
            <a href="{{ route('legal.mentions') }}">Mentions légales</a>
            <a href="{{ route('legal.privacy') }}">Confidentialité</a>
            <a href="{{ route('legal.cookies') }}">Cookies</a>
            <a href="{{ route('legal.terms') }}">CGU</a>
        </nav>
        <a class="footer-don" href="{{ route('join') }}">Nous soutenir</a>
    </footer>

    <div class="cookie-banner" data-cookie-banner hidden>
        <div>
            <strong>Préférences cookies</strong>
            <p>Nous utilisons les cookies nécessaires au fonctionnement du site. Vous pouvez accepter ou refuser les cookies non essentiels.</p>
            <a href="{{ route('legal.cookies') }}">En savoir plus</a>
        </div>
        <div class="cookie-actions">
            <button type="button" data-cookie-choice="refused">Refuser</button>
            <button type="button" data-cookie-choice="accepted">Accepter</button>
        </div>
    </div>
</body>
</html>

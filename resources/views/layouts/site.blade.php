<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'La Quinzaine Obstetricale')</title>
    <meta name="description" content="@yield('description', 'La Quinzaine Obstetricale agit pour la sante des femmes et de la perinatalite en Guyane.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="header-top">
            <a class="brand" href="{{ route('home') }}" aria-label="Accueil La Quinzaine Obstetricale">
                <img class="brand-logo" src="{{ asset('images/quinzaine-logo.jpeg') }}" alt="Quinzaine Obstetricale CHAR">
            </a>

            <div class="header-actions" aria-label="Actions rapides">
                @auth
                    <span class="account-chip">{{ auth()->user()->name }}</span>
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @elseif (! \App\Models\User::where('is_admin', true)->exists())
                        <a href="{{ route('admin.bootstrap') }}">Initialiser admin</a>
                    @endif
                    <form method="POST" action="{{ route('professional.logout') }}">
                        @csrf
                        <button type="submit">Deconnexion</button>
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
            <span class="header-note">Sante des femmes et perinatalite en Guyane</span>
            <nav class="main-nav" aria-label="Navigation principale">
                <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Accueil</a>
                <a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>L'association</a>
                <a href="{{ route('actions') }}" @class(['active' => request()->routeIs('actions')])>Nos actions</a>
                <a href="{{ route('public') }}" @class(['active' => request()->routeIs('public')])>Sante des femmes</a>
                <a href="{{ route('news') }}" @class(['active' => request()->routeIs('news')])>Actualites</a>
                <a href="{{ route('agenda') }}" @class(['active' => request()->routeIs('agenda')])>Agenda</a>
                <a href="{{ route('research') }}" @class(['active' => request()->routeIs('research')])>Recherche</a>
                <a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact')])>Contact</a>
            </nav>
            <div class="site-search" data-site-search>
                <label for="site-search-input" aria-label="Rechercher sur le site">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M10.8 4a6.8 6.8 0 1 1-4.81 11.61A6.8 6.8 0 0 1 10.8 4Zm0 2a4.8 4.8 0 1 0 3.39 8.19A4.8 4.8 0 0 0 10.8 6Zm5.66 9.04 3.25 3.25-1.42 1.42-3.25-3.25 1.42-1.42Z"/>
                    </svg>
                    <input id="site-search-input" type="search" placeholder="Rechercher" autocomplete="off" data-search-input data-search-url="{{ route('search') }}">
                </label>
                <div class="search-results" data-search-results hidden></div>
            </div>
        </div>
    </header>

    @guest
        <dialog class="register-modal" id="register-modal" aria-labelledby="register-modal-title">
            <button class="modal-close" type="button" data-close-register aria-label="Fermer la fenetre">&times;</button>
            <div class="modal-heading">
                <p class="eyebrow">Compte professionnel</p>
                <h2 id="register-modal-title">Espace pro</h2>
                <p>Connectez-vous a votre compte professionnel ou creez un acces avec votre email.</p>
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
                        <span>Rester connecte</span>
                    </label>

                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <button type="submit">Se connecter</button>
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

                    <button type="submit">Creer mon acces</button>
                </form>
            </div>
        </dialog>
    @endguest

    <main>
        @if (session('status'))
            <div class="flash-message">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer" id="footer-don">
        <div>
            <strong>La Quinzaine Obstetricale</strong>
            <p>Association guyanaise dediee a la sante des femmes, a la perinatalite, a la prevention et a la transmission.</p>
        </div>
        <a class="footer-don" href="{{ route('join') }}">Nous soutenir</a>
    </footer>
</body>
</html>

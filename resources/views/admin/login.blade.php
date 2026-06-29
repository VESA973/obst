@extends('layouts.admin')

@section('title', 'Connexion admin | La Quinzaine Obstétricale')

@section('content')
<section class="admin-login-screen">
    <div class="admin-login-panel">
        <div>
            <p class="eyebrow">Administration</p>
            <h1>Connexion admin</h1>
            <p>Accès réservé à la gestion du site, des actualités, des événements, des fichiers et des utilisateurs.</p>
        </div>

        <form class="admin-login-form" method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label>
                Email
                <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            </label>
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <label>
                Mot de passe
                <input name="password" type="password" autocomplete="current-password" required>
            </label>
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <label class="admin-check">
                <input name="remember" type="checkbox" value="1">
                Garder la session ouverte
            </label>

            <button type="submit">Entrer dans l'admin</button>
        </form>
    </div>
</section>
@endsection

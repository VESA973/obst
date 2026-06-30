@extends('layouts.site')

@section('title', 'Réinitialiser le mot de passe | Espace pro')

@section('content')
<section class="auth-gate auth-gate-top">
    <div class="auth-copy">
        <p class="eyebrow">Espace professionnel</p>
        <h2>Réinitialiser le mot de passe</h2>
        <p>Choisissez un nouveau mot de passe pour votre compte professionnel.</p>
    </div>

    <form class="auth-card login-card-featured" method="POST" action="{{ route('professional.password.update') }}">
        @csrf
        <input name="token" type="hidden" value="{{ $token }}">
        <label for="reset-email">Email</label>
        <input id="reset-email" name="email" type="email" value="{{ old('email', $email) }}" required>
        <label for="reset-password">Nouveau mot de passe</label>
        <input id="reset-password" name="password" type="password" autocomplete="new-password" required>
        <label for="reset-password-confirmation">Confirmer le mot de passe</label>
        <input id="reset-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror
        @error('password')
            <p class="form-error">{{ $message }}</p>
        @enderror
        <button type="submit">Réinitialiser</button>
    </form>
</section>
@endsection

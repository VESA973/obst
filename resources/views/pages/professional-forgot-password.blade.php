@extends('layouts.site')

@section('title', 'Mot de passe oublié | Espace pro')

@section('content')
<section class="auth-gate auth-gate-top">
    <div class="auth-copy">
        <p class="eyebrow">Espace professionnel</p>
        <h2>Mot de passe oublié</h2>
        <p>Indiquez votre email professionnel. Un lien sécurisé vous sera envoyé pour choisir un nouveau mot de passe.</p>
    </div>

    <form class="auth-card login-card-featured" method="POST" action="{{ route('professional.password.email') }}">
        @csrf
        <label for="forgot-email">Email</label>
        <input id="forgot-email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror
        <button type="submit">Envoyer le lien</button>
    </form>
</section>
@endsection

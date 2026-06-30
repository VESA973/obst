<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SiteMailerConfigurator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfessionalAuthController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $attributes = $request->validateWithBag('register', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'is_health_professional' => ['accepted'],
            'website' => ['nullable', 'prohibited'],
            'form_started_at' => ['required', 'integer'],
        ]);

        if (now()->timestamp - (int) $attributes['form_started_at'] < 3) {
            $exception = ValidationException::withMessages([
                'email' => 'Merci de patienter quelques secondes avant de valider le formulaire.',
            ]);
            $exception->errorBag = 'register';

            throw $exception;
        }

        $user = User::where('email', $attributes['email'])->first();

        if ($user?->hasVerifiedEmail()) {
            $exception = ValidationException::withMessages([
                'email' => 'Un compte existe déjà avec cette adresse email.',
            ]);
            $exception->errorBag = 'register';

            throw $exception;
        }

        if ($user) {
            $user->forceFill([
                'name' => $attributes['name'],
                'password' => $attributes['password'],
                'is_member' => true,
                'is_health_professional' => true,
            ])->save();
        } else {
            $user = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'is_member' => true,
                'is_health_professional' => true,
            ]);
        }

        SiteMailerConfigurator::apply();
        $user->sendEmailVerificationNotification();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('pro')
            ->with('status', 'Votre compte est créé. Confirmez votre email avec le lien reçu pour accéder à l’espace professionnel.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Les identifiants saisis ne correspondent à aucun compte.'])
                ->onlyInput('email');
        }

        $user = $request->user();

        if (! $user->is_member && ! $user->is_admin) {
            Auth::logout();

            return back()
                ->withErrors(['email' => "Ce compte n'a pas accès à l'espace professionnel."])
                ->onlyInput('email');
        }

        if (! $user->hasVerifiedEmail()) {
            SiteMailerConfigurator::apply();
            $user->sendEmailVerificationNotification();
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Votre email doit être confirmé. Un nouveau lien vient de vous être envoyé.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('pro'));
    }

    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()
            ->route('pro')
            ->with('status', 'Votre email est confirmé. Vous pouvez maintenant vous connecter.');
    }

    public function forgotPasswordForm(): View
    {
        return view('pages.professional-forgot-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        SiteMailerConfigurator::apply();
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Un lien de réinitialisation vient de vous être envoyé.')
            : back()->withErrors(['email' => 'Aucun compte ne correspond à cette adresse.']);
    }

    public function resetPasswordForm(Request $request, string $token): View
    {
        return view('pages.professional-reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset($attributes, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('pro')->with('status', 'Votre mot de passe a été réinitialisé.')
            : back()->withErrors(['email' => 'Le lien de réinitialisation est invalide ou expiré.']);
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (SiteSetting::getValue('google_login_enabled', '0') !== '1') {
            return redirect()->route('pro')->with('status', 'La connexion Google n’est pas activée.');
        }

        if (! class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return redirect()->route('pro')->with('status', 'La connexion Google nécessite le package laravel/socialite sur le serveur.');
        }

        config([
            'services.google.client_id' => SiteSetting::getValue('google_client_id'),
            'services.google.client_secret' => SiteSetting::getValue('google_client_secret'),
            'services.google.redirect' => route('professional.google.callback'),
        ]);

        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        if (! class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return redirect()->route('pro')->with('status', 'La connexion Google nécessite le package laravel/socialite sur le serveur.');
        }

        config([
            'services.google.client_id' => SiteSetting::getValue('google_client_id'),
            'services.google.client_secret' => SiteSetting::getValue('google_client_secret'),
            'services.google.redirect' => route('professional.google.callback'),
        ]);

        $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();

        $user = User::firstOrNew(['email' => $googleUser->getEmail()]);
        $user->forceFill([
            'name' => $user->name ?: ($googleUser->getName() ?: $googleUser->getNickname() ?: $googleUser->getEmail()),
            'google_id' => $googleUser->getId(),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'is_member' => true,
            'is_health_professional' => true,
        ]);

        if (! $user->exists) {
            $user->password = Str::password(32);
        }

        $user->save();

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->route('pro');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('pro')
            ->with('status', "Vous êtes déconnecté de l'espace professionnel.");
    }
}

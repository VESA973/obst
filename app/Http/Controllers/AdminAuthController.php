<?php

namespace App\Http\Controllers;

use App\Support\AntiBotChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function loginForm(Request $request): View|RedirectResponse
    {
        if ($request->user()?->is_admin) {
            return redirect()->route('admin.home');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        AntiBotChallenge::verify($request, 'admin_login');

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Identifiants administrateur invalides.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! $request->user()->is_admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => "Ce compte n'a pas accès à l'administration."])
                ->onlyInput('email');
        }

        return redirect()->intended(route('admin.home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.dashboard');
    }
}

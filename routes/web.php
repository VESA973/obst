<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventQrCodeController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfessionalAuthController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::view('/qui-sommes-nous', 'pages.about')->name('about');
Route::redirect('/espace-public', '/sante-des-femmes', 301);
Route::redirect('/adherer', '/nous-soutenir', 301);
Route::view('/nos-actions', 'pages.actions')->name('actions');
Route::get('/sante-des-femmes', [PageController::class, 'public'])->name('public');
Route::get('/sante-des-femmes/{category:slug}', [PageController::class, 'publicCategory'])->name('public.category');
Route::get('/sante-des-femmes/{category:slug}/{article:slug}', [PageController::class, 'publicArticle'])->name('public.article');
Route::view('/espace-pro', 'pages.pro')->name('pro');
Route::get('/espace-pro/ressources', [PageController::class, 'professionalResources'])->name('pro.resources');
// Rubrique Actualités. Les routes fixes passent avant /actualites/{slug}.
Route::get('/actualites', [NewsController::class, 'index'])->name('news');
Route::get('/actualites/recherche', [NewsController::class, 'search'])->name('news.search');
Route::get('/actualites/rss.xml', [NewsController::class, 'feed'])->name('news.feed');
Route::get('/actualites/{slug}', [NewsController::class, 'category'])->name('news.category');
Route::get('/actualites/{category}/{article}', [NewsController::class, 'show'])->name('news.show');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/agenda', [PageController::class, 'agenda'])->name('agenda');
Route::get('/agenda/{event}', [PageController::class, 'agendaShow'])->name('agenda.show');
Route::get('/agenda/{event}/calendrier.ics', [PageController::class, 'agendaIcs'])->name('agenda.ics');
Route::view('/recherche-innovation', 'pages.research')->name('research');
Route::view('/nous-soutenir', 'pages.join')->name('join');
Route::view('/contact', 'pages.contact')->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.send');
Route::view('/mentions-legales', 'pages.legal.mentions')->name('legal.mentions');
Route::view('/politique-de-confidentialite', 'pages.legal.privacy')->name('legal.privacy');
Route::view('/politique-de-cookies', 'pages.legal.cookies')->name('legal.cookies');
Route::view('/conditions-generales-utilisation', 'pages.legal.terms')->name('legal.terms');
Route::redirect('/connexion', '/espace-pro#connexion-pro')->name('login');
Route::get('/recherche', SearchController::class)->name('search');
Route::get('/evenements/{event}/qr-code', EventQrCodeController::class)->name('events.qr');
Route::post('/evenements/{event}/inscription', [EventRegistrationController::class, 'store'])->name('events.register');

Route::post('/professionnels/inscription', [ProfessionalAuthController::class, 'register'])->middleware('throttle:5,1')->name('professional.register');
Route::post('/professionnels/connexion', [ProfessionalAuthController::class, 'login'])->middleware('throttle:5,1')->name('professional.login');
Route::post('/professionnels/deconnexion', [ProfessionalAuthController::class, 'logout'])->name('professional.logout');
Route::get('/professionnels/email/verification/{id}/{hash}', [ProfessionalAuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::get('/professionnels/mot-de-passe-oublie', [ProfessionalAuthController::class, 'forgotPasswordForm'])->name('professional.password.request');
Route::post('/professionnels/mot-de-passe-oublie', [ProfessionalAuthController::class, 'sendPasswordResetLink'])->middleware('throttle:5,1')->name('professional.password.email');
Route::get('/professionnels/mot-de-passe/{token}', [ProfessionalAuthController::class, 'resetPasswordForm'])->name('password.reset');
Route::post('/professionnels/mot-de-passe', [ProfessionalAuthController::class, 'resetPassword'])->name('professional.password.update');
Route::get('/professionnels/google', [ProfessionalAuthController::class, 'redirectToGoogle'])->name('professional.google.redirect');
Route::get('/professionnels/google/retour', [ProfessionalAuthController::class, 'handleGoogleCallback'])->name('professional.google.callback');

// L'administration est gérée par Filament (App\Providers\Filament\AdminPanelProvider).
// Anciennes adresses conservées pour les favoris.
Route::redirect('/admin/connexion', '/admin/login');
Route::redirect('/admin/tableau-de-bord', '/admin');

// Premier administrateur : un compte connecté devient admin tant qu'aucun admin n'existe.
Route::get('/admin/initialiser', function () {
    abort_if(User::where('is_admin', true)->exists(), 403);

    auth()->user()->forceFill(['is_admin' => true])->save();

    return redirect('/admin');
})->middleware('auth')->name('admin.bootstrap');

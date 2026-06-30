<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\EventQrCodeController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfessionalAuthController;
use App\Http\Controllers\SearchController;
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
Route::get('/actualites', [PageController::class, 'news'])->name('news');
Route::get('/actualites/{article:slug}', [PageController::class, 'article'])->name('articles.show');
Route::get('/agenda', [PageController::class, 'agenda'])->name('agenda');
Route::view('/recherche-innovation', 'pages.research')->name('research');
Route::view('/nous-soutenir', 'pages.join')->name('join');
Route::view('/contact', 'pages.contact')->name('contact');
Route::redirect('/connexion', '/espace-pro#connexion-pro')->name('login');
Route::get('/recherche', SearchController::class)->name('search');
Route::get('/evenements/{event}/qr-code', EventQrCodeController::class)->name('events.qr');
Route::post('/evenements/{event}/inscription', [EventRegistrationController::class, 'store'])->name('events.register');

Route::post('/professionnels/inscription', [ProfessionalAuthController::class, 'register'])->middleware('throttle:5,1')->name('professional.register');
Route::post('/professionnels/connexion', [ProfessionalAuthController::class, 'login'])->middleware('throttle:5,1')->name('professional.login');
Route::post('/professionnels/deconnexion', [ProfessionalAuthController::class, 'logout'])->name('professional.logout');
Route::get('/professionnels/email/verification/{id}/{hash}', [ProfessionalAuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::get('/professionnels/mot-de-passe-oublie', [ProfessionalAuthController::class, 'forgotPasswordForm'])->name('professional.password.request');
Route::post('/professionnels/mot-de-passe-oublie', [ProfessionalAuthController::class, 'sendPasswordResetLink'])->middleware('throttle:5,1')->name('professional.password.email');
Route::get('/professionnels/mot-de-passe/{token}', [ProfessionalAuthController::class, 'resetPasswordForm'])->name('password.reset');
Route::post('/professionnels/mot-de-passe', [ProfessionalAuthController::class, 'resetPassword'])->name('professional.password.update');
Route::get('/professionnels/google', [ProfessionalAuthController::class, 'redirectToGoogle'])->name('professional.google.redirect');
Route::get('/professionnels/google/retour', [ProfessionalAuthController::class, 'handleGoogleCallback'])->name('professional.google.callback');

Route::get('/admin', [AdminAuthController::class, 'loginForm'])->name('admin.dashboard');
Route::get('/admin/connexion', [AdminAuthController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/connexion', [AdminAuthController::class, 'login'])->name('admin.login.store');
Route::post('/admin/deconnexion', [AdminAuthController::class, 'logout'])->middleware('auth')->name('admin.logout');

Route::get('/admin/initialiser', function () {
    abort_if(User::where('is_admin', true)->exists(), 403);

    auth()->user()->forceFill(['is_admin' => true])->save();

    return redirect()->route('admin.home')->with('status', 'Votre compte est maintenant administrateur.');
})->middleware('auth')->name('admin.bootstrap');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tableau-de-bord', [AdminController::class, 'index'])->name('home');
    Route::get('/configuration', [AdminController::class, 'configuration'])->name('configuration');
    Route::put('/configuration', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/pages', [AdminController::class, 'pages'])->name('pages.index');
    Route::put('/pages', [AdminController::class, 'updatePages'])->name('pages.update');
    Route::get('/actualites', [AdminController::class, 'articles'])->name('articles.index');
    Route::get('/inscriptions', [AdminController::class, 'registrations'])->name('registrations.index');
    Route::get('/professionnels', [AdminController::class, 'professionals'])->name('professionals.index');
    Route::put('/professionnels/{user}', [AdminController::class, 'updateProfessional'])->name('professionals.update');
    Route::get('/membres', [AdminController::class, 'members'])->name('members.index');
    Route::post('/membres', [AdminController::class, 'storeMember'])->name('members.store');
    Route::put('/membres/{member}', [AdminController::class, 'updateMember'])->name('members.update');
    Route::delete('/membres/{member}', [AdminController::class, 'destroyMember'])->name('members.destroy');
    Route::get('/fichiers', [AdminController::class, 'files'])->name('files.index');
    Route::post('/fichiers', [AdminController::class, 'storeFile'])->name('files.store');
    Route::delete('/fichiers/{file}', [AdminController::class, 'destroyFile'])->name('files.destroy');
    Route::post('/actualites/categories', [AdminController::class, 'storeArticleCategory'])->name('article-categories.store');
    Route::put('/actualites/categories/{category}', [AdminController::class, 'updateArticleCategory'])->name('article-categories.update');
    Route::delete('/actualites/categories/{category}', [AdminController::class, 'destroyArticleCategory'])->name('article-categories.destroy');
    Route::post('/actualites', [AdminController::class, 'storeArticle'])->name('articles.store');
    Route::put('/actualites/{article}', [AdminController::class, 'updateArticle'])->name('articles.update');
    Route::delete('/actualites/{article}', [AdminController::class, 'destroyArticle'])->name('articles.destroy');
    Route::delete('/actualites/photos/{asset}', [AdminController::class, 'destroyArticleAsset'])->name('articles.assets.destroy');
    Route::get('/evenements', [AdminController::class, 'events'])->name('events.index');
    Route::get('/evenements/inscriptions/export', [AdminController::class, 'exportEventRegistrations'])->name('events.registrations.export');
    Route::post('/evenements', [AdminController::class, 'storeEvent'])->name('events.store');
    Route::put('/evenements/{event}', [AdminController::class, 'updateEvent'])->name('events.update');
    Route::delete('/evenements/{event}', [AdminController::class, 'destroyEvent'])->name('events.destroy');
    Route::delete('/evenements/fichiers/{asset}', [AdminController::class, 'destroyEventAsset'])->name('events.assets.destroy');
    Route::get('/utilisateurs', [AdminController::class, 'users'])->name('users.index');
    Route::post('/utilisateurs', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/utilisateurs/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/utilisateurs/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
});

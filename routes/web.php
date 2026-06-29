<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfessionalAuthController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::view('/qui-sommes-nous', 'pages.about')->name('about');
Route::redirect('/espace-public', '/sante-des-femmes', 301);
Route::redirect('/adherer', '/nous-soutenir', 301);
Route::view('/nos-actions', 'pages.actions')->name('actions');
Route::view('/sante-des-femmes', 'pages.public')->name('public');
Route::view('/espace-pro', 'pages.pro')->name('pro');
Route::view('/actualites', 'pages.news')->name('news');
Route::view('/agenda', 'pages.agenda')->name('agenda');
Route::view('/recherche-innovation', 'pages.research')->name('research');
Route::view('/nous-soutenir', 'pages.join')->name('join');
Route::view('/contact', 'pages.contact')->name('contact');
Route::redirect('/connexion', '/espace-pro#connexion-pro')->name('login');

Route::post('/professionnels/inscription', [ProfessionalAuthController::class, 'register'])->name('professional.register');
Route::post('/professionnels/connexion', [ProfessionalAuthController::class, 'login'])->name('professional.login');
Route::post('/professionnels/deconnexion', [ProfessionalAuthController::class, 'logout'])->name('professional.logout');

Route::get('/admin/initialiser', function () {
    abort_if(User::where('is_admin', true)->exists(), 403);

    auth()->user()->forceFill(['is_admin' => true])->save();

    return redirect()->route('admin.dashboard')->with('status', 'Votre compte est maintenant administrateur.');
})->middleware('auth')->name('admin.bootstrap');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::put('/configuration', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/membres', [AdminController::class, 'storeMember'])->name('members.store');
    Route::put('/membres/{member}', [AdminController::class, 'updateMember'])->name('members.update');
    Route::delete('/membres/{member}', [AdminController::class, 'destroyMember'])->name('members.destroy');
    Route::post('/fichiers', [AdminController::class, 'storeFile'])->name('files.store');
    Route::delete('/fichiers/{file}', [AdminController::class, 'destroyFile'])->name('files.destroy');
    Route::post('/evenements', [AdminController::class, 'storeEvent'])->name('events.store');
    Route::put('/evenements/{event}', [AdminController::class, 'updateEvent'])->name('events.update');
    Route::delete('/evenements/{event}', [AdminController::class, 'destroyEvent'])->name('events.destroy');
    Route::post('/utilisateurs', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/utilisateurs/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/utilisateurs/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
});

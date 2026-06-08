<?php

use App\Http\Controllers\ProfessionalAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::view('/qui-sommes-nous', 'pages.about')->name('about');
Route::view('/espace-public', 'pages.public')->name('public');
Route::view('/espace-pro', 'pages.pro')->name('pro');
Route::view('/agenda', 'pages.agenda')->name('agenda');
Route::view('/adherer', 'pages.join')->name('join');

Route::post('/professionnels/inscription', [ProfessionalAuthController::class, 'register'])->name('professional.register');
Route::post('/professionnels/connexion', [ProfessionalAuthController::class, 'login'])->name('professional.login');
Route::post('/professionnels/deconnexion', [ProfessionalAuthController::class, 'logout'])->name('professional.logout');

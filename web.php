<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KlantController;
use App\Http\Controllers\InstructeurController;
use App\Http\Controllers\EigenaarController;
use App\Http\Controllers\ReserveringController;

/*
|--------------------------------------------------------------------------
| Publieke routes (geen login nodig)
|--------------------------------------------------------------------------
*/

// Algemene homepage met pakketinformatie
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Authenticatie routes
|--------------------------------------------------------------------------
*/

Route::get('/registreer', [AuthController::class, 'registreerForm'])->name('auth.registreer');
Route::post('/registreer', [AuthController::class, 'registreer'])->name('auth.registreer.post');
Route::get('/activeer/{token}', [AuthController::class, 'activeer'])->name('auth.activeer');
Route::get('/wachtwoord-instellen/{token}', [AuthController::class, 'wachtwoordForm'])->name('auth.wachtwoord.form');
Route::post('/wachtwoord-instellen/{token}', [AuthController::class, 'wachtwoordOpslaan'])->name('auth.wachtwoord.opslaan');

Route::get('/login', [AuthController::class, 'loginForm'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

/*
|--------------------------------------------------------------------------
| Klant routes (middleware: auth + rol:klant)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:klant'])->prefix('klant')->name('klant.')->group(function () {

    Route::get('/dashboard', [KlantController::class, 'dashboard'])->name('dashboard');

    // Persoonsgegevens
    Route::get('/profiel', [KlantController::class, 'profielForm'])->name('profiel');
    Route::post('/profiel', [KlantController::class, 'profielOpslaan'])->name('profiel.opslaan');

    // Wachtwoord
    Route::get('/wachtwoord', [KlantController::class, 'wachtwoordForm'])->name('wachtwoord');
    Route::post('/wachtwoord', [KlantController::class, 'wachtwoordWijzigen'])->name('wachtwoord.wijzigen');

    // Reserveringen
    Route::get('/reserveren', [ReserveringController::class, 'create'])->name('reserveren');
    Route::post('/reserveren', [ReserveringController::class, 'store'])->name('reserveren.store');
    Route::get('/reserveringen', [ReserveringController::class, 'overzicht'])->name('reserveringen');
    Route::get('/reserveringen/{id}/annuleer', [ReserveringController::class, 'annuleerForm'])->name('reserveringen.annuleer');
    Route::post('/reserveringen/{id}/annuleer', [ReserveringController::class, 'annuleer'])->name('reserveringen.annuleer.post');
    Route::post('/reserveringen/{id}/betaald', [ReserveringController::class, 'markeerBetaald'])->name('reserveringen.betaald');
});

/*
|--------------------------------------------------------------------------
| Instructeur routes (middleware: auth + rol:instructeur)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:instructeur'])->prefix('instructeur')->name('instructeur.')->group(function () {

    Route::get('/dashboard', [InstructeurController::class, 'dashboard'])->name('dashboard');

    // Profiel & wachtwoord
    Route::get('/profiel', [InstructeurController::class, 'profielForm'])->name('profiel');
    Route::post('/profiel', [InstructeurController::class, 'profielOpslaan'])->name('profiel.opslaan');
    Route::get('/wachtwoord', [InstructeurController::class, 'wachtwoordForm'])->name('wachtwoord');
    Route::post('/wachtwoord', [InstructeurController::class, 'wachtwoordWijzigen'])->name('wachtwoord.wijzigen');

    // Klantenbeheer
    Route::get('/klanten', [InstructeurController::class, 'klanten'])->name('klanten');
    Route::get('/klanten/{id}', [InstructeurController::class, 'klantDetail'])->name('klanten.detail');
    Route::put('/klanten/{id}', [InstructeurController::class, 'klantWijzigen'])->name('klanten.wijzigen');

    // Lesrooster
    Route::get('/rooster', [InstructeurController::class, 'rooster'])->name('rooster');
    Route::get('/rooster/{type}', [InstructeurController::class, 'rooster'])->name('rooster.type'); // dag|week|maand

    // Les annuleren
    Route::post('/les/{id}/annuleer/ziekte', [InstructeurController::class, 'annuleerZiekte'])->name('les.annuleer.ziekte');
    Route::post('/les/{id}/annuleer/weer', [InstructeurController::class, 'annuleerWeer'])->name('les.annuleer.weer');
});

/*
|--------------------------------------------------------------------------
| Eigenaar routes (middleware: auth + rol:eigenaar)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:eigenaar'])->prefix('eigenaar')->name('eigenaar.')->group(function () {

    Route::get('/dashboard', [EigenaarController::class, 'dashboard'])->name('dashboard');

    // Profiel & wachtwoord
    Route::get('/profiel', [EigenaarController::class, 'profielForm'])->name('profiel');
    Route::post('/profiel', [EigenaarController::class, 'profielOpslaan'])->name('profiel.opslaan');
    Route::get('/wachtwoord', [EigenaarController::class, 'wachtwoordForm'])->name('wachtwoord');
    Route::post('/wachtwoord', [EigenaarController::class, 'wachtwoordWijzigen'])->name('wachtwoord.wijzigen');

    // Klantenbeheer
    Route::get('/klanten', [EigenaarController::class, 'klanten'])->name('klanten');
    Route::post('/klanten', [EigenaarController::class, 'klantToevoegen'])->name('klanten.toevoegen');
    Route::put('/klanten/{id}', [EigenaarController::class, 'klantWijzigen'])->name('klanten.wijzigen');
    Route::delete('/klanten/{id}', [EigenaarController::class, 'klantVerwijderen'])->name('klanten.verwijderen');
    Route::post('/klanten/{id}/blokkeer', [EigenaarController::class, 'klantBlokkeren'])->name('klanten.blokkeren');
    Route::post('/klanten/{id}/rol', [EigenaarController::class, 'rolWijzigen'])->name('klanten.rol');

    // Instructeursbeheer
    Route::get('/instructeurs', [EigenaarController::class, 'instructeurs'])->name('instructeurs');
    Route::post('/instructeurs', [EigenaarController::class, 'instructeurToevoegen'])->name('instructeurs.toevoegen');
    Route::put('/instructeurs/{id}', [EigenaarController::class, 'instructeurWijzigen'])->name('instructeurs.wijzigen');
    Route::delete('/instructeurs/{id}', [EigenaarController::class, 'instructeurVerwijderen'])->name('instructeurs.verwijderen');

    // Betalingen
    Route::get('/betalingen', [EigenaarController::class, 'betalingen'])->name('betalingen');
    Route::post('/betalingen/{id}/bevestig', [EigenaarController::class, 'betalingBevestigen'])->name('betalingen.bevestig');

    // Roosters
    Route::get('/rooster', [EigenaarController::class, 'rooster'])->name('rooster');
    Route::get('/rooster/{instructeur_id}/{type?}', [EigenaarController::class, 'roosterInstructeur'])->name('rooster.instructeur');

    // Les annuleren
    Route::post('/les/{id}/annuleer/ziekte', [EigenaarController::class, 'annuleerZiekte'])->name('les.annuleer.ziekte');
    Route::post('/les/{id}/annuleer/weer', [EigenaarController::class, 'annuleerWeer'])->name('les.annuleer.weer');
});

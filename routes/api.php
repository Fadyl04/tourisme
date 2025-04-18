<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AuthController;
use FedaPay\FedaPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 *  Routes pour l'authentification des utilisateurs
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/**
 *  Routes publiques pour la consultation des événements et des sites
 */
Route::get('/events', [EventController::class, 'index']); // Liste des événements
Route::get('/events/{id}', [EventController::class, 'show']); // Détail d'un événement
Route::get('/events/search/{label}', [EventController::class, 'searchEvent']); // Recherche d'événements

Route::get('/sites', [SiteController::class, 'index']); // Liste des sites
Route::get('/sites/{id}', [SiteController::class, 'show']); // Détail d'un site
Route::get('/sites/search/{label}', [SiteController::class, 'searchSite']); // Recherche de sites

/**
 *  Routes pour le paiement 
 */
Route::post('/reservations/{id_reservation}/pay', [PaiementController::class, 'initierPaiement']);
Route::match(['get', 'post'], 'reservations/{id_reservation}/pay/confirm-pay', [PaiementController::class, 'callbackPaiement'])->name('paiement.callback');
/* Route::post('/paid', function(){
    try {
        FedaPay::setApiKey(env('FEDAPAY_PUBLIC_KEY'));
        FedaPay::setEnvironment(env('FEDAPAY_MODE'));
        $account = \FedaPay\Account::all();
        Log::info('Connexion FedaPay réussie :', $account->toArray());
    } catch (\Exception $e) {
        //throw $th;
        Log::error('Echec de connexion');
    }
});
 */

/**
 *  Routes protégées (authentification requise)
 */
Route::middleware('auth:sanctum')->group(function () {
    // Déconnexion et récupération des infos utilisateur
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    /**
     *  Routes pour le CRUD des événements et des sites (admin/director uniquement)
     */
    Route::post('/events', [EventController::class, 'store']); // Création
    Route::put('/events/{id}', [EventController::class, 'update']); // Modification
    Route::delete('/events/{id}', [EventController::class, 'destroy']); // Suppression

    Route::post('/sites', [SiteController::class, 'store']); // Création
    Route::put('/sites/{id}', [SiteController::class, 'update']); // Modification
    Route::delete('/sites/{id}', [SiteController::class, 'destroy']); // Suppression

    /**
     *  Routes pour les réservations
     */
    Route::post('/reservations', [ReservationController::class, 'store']); // Création réservation
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']); // Annulation réservation
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
});

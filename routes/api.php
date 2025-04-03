<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

/* Route::get('/', function(){
    return 'fadyl';
}); */

/**
 *  Routes pour l'authentification des utilisateurs
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
/**
 *  Routes publiques pour la consultation des événements
 */
Route::get('/events', [EventController::class, 'index']); // Liste
Route::get('/events/{id}', [EventController::class, 'show']); // Détail
Route::get('/events/search/{label}', [EventController::class, 'searchEvent']); // Recherche par libellé
/**
 *  Routes publiques pour la consultation des événements
 */
Route::get('/sites', [SiteController::class, 'index']); // Liste
Route::get('/sites/{id}', [SiteController::class, 'show']); // Détail
Route::get('/sites/search/{label}', [SiteController::class, 'searchSite']); // Recherche par libellé
/**
 *  Routes protégées (authentification requise)
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });          
    /**
     *  Route pour le crud d'évènement.
     */
    Route::get('/events', [EventController::class, 'index']); // Liste
    Route::get('/events/{id}', [EventController::class, 'show']); // Détail
    Route::post('/events', [EventController::class, 'store']); // Création
    Route::put('/events/{id}', [EventController::class, 'update']); // Modification
    Route::delete('/events/{id}', [EventController::class, 'destroy']); // Suppression
    /**
     *  Route pour le crud d'un site touristque.
     */
    Route::get('/sites', [SiteController::class, 'index']); // Liste
    Route::get('/sites/{id}', [SiteController::class, 'show']); // Détail
    Route::post('/sites', [SiteController::class, 'store']); // Création
    Route::put('/sites/{id}', [SiteController::class, 'update']); // Modification
    Route::delete('/sites/{id}', [SiteController::class, 'destroy']); // Suppression
});

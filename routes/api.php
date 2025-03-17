<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

/* Route::get('/', function(){
    return 'fadyl';
}); */

/**
 *  Route pour l'inscription la connexion et la déconnxion
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
/**
 *  Route pour l'inscription la connexion et la déconnxion
 */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Authentification (public)
Route::post('/login', [AuthController::class, 'login']);

// Route publique — liste des profils actifs
Route::get('/profiles', [ProfileController::class, 'index']);

// Routes protégées par authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Gestion des profils
    Route::post('/profiles', [ProfileController::class, 'store']);
    Route::get('/admin/profiles', [ProfileController::class, 'indexAdmin']);
    Route::put('/profiles/{profile}', [ProfileController::class, 'update']);
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy']);

});

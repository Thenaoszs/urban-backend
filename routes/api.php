<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SignalementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes – CitoyenApp
|--------------------------------------------------------------------------
|
| Le middleware ForceJsonResponse est appliqué globalement dans bootstrap/app.php
| Toutes les routes sont préfixées /api automatiquement.
|
*/

// ── Routes publiques (sans authentification) ──────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ── Routes authentifiées (Sanctum) ────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Profil (auto-modification)
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // ── Citoyen : ses propres signalements ───────────────────────────────────
    Route::get('/signalements',      [SignalementController::class, 'index']);
    Route::post('/signalements',     [SignalementController::class, 'store']);
    Route::get('/signalements/{id}', [SignalementController::class, 'show'])
        ->where('id', '[0-9]+');

    // ── Gestionnaire & Admin ─────────────────────────────────────────────────
    Route::middleware(['role:gestionnaire,admin'])->group(function () {
        Route::get('/signalements/all',               [SignalementController::class, 'all']);
        Route::put('/signalements/{id}/statut',       [SignalementController::class, 'updateStatut'])
            ->where('id', '[0-9]+');
        Route::get('/stats',                          [SignalementController::class, 'stats']);
    });

    // ── Admin uniquement ─────────────────────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        // Gestion complète des utilisateurs
        Route::get('/users',                         [UserController::class, 'index']);
        Route::post('/users',                        [UserController::class, 'store']);
        Route::get('/users/{id}',                    [UserController::class, 'show'])
            ->where('id', '[0-9]+');
        Route::put('/users/{id}',                    [UserController::class, 'update'])
            ->where('id', '[0-9]+');
        Route::delete('/users/{id}',                 [UserController::class, 'destroy'])
            ->where('id', '[0-9]+');
        Route::patch('/users/{id}/toggle-block',     [UserController::class, 'toggleBlock'])
            ->where('id', '[0-9]+');

        // Suppression de signalement
        Route::delete('/signalements/{id}',          [SignalementController::class, 'destroy'])
            ->where('id', '[0-9]+');
    });
});

// ── Fallback 404 JSON ─────────────────────────────────────────────────────────
Route::fallback(function () {
    return response()->json(['message' => 'Route non trouvée.'], 404);
});

Route::get('/health', function () {
    try {
        DB::select('SELECT 1');

        return response()->json([
            'status' => 'OK',
            'database' => 'Connected',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'database' => 'Disconnected',
            'error' => $e->getMessage(),
        ], 500);
    }
});

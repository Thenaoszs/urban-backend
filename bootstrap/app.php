<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Middleware global pour toutes les routes API → force JSON
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Alias de middlewares personnalisés
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Configuration CORS (Laravel gère via config/cors.php)
        // Les headers CORS sont ajoutés automatiquement par HandleCors
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Non authentifié → JSON 401
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié. Veuillez vous connecter.',
                ], 401);
            }
        });

        // Validation → JSON 422 avec les erreurs détaillées
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Données invalides.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Modèle non trouvé → JSON 404
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            Request $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Ressource introuvable.',
                ], 404);
            }
        });

        // Accès refusé → JSON 403
        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            Request $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Accès refusé.',
                ], 403);
            }
        });
    })
    ->create();

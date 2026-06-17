<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * Inscription d'un citoyen.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'nom'      => $request->nom,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'citoyen',
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie.',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    /**
     * POST /api/login
     * Connexion d'un utilisateur (citoyen, gestionnaire ou admin).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects.',
            ], 401);
        }

        if ($user->is_blocked) {
            return response()->json([
                'message' => 'Votre compte a été bloqué. Contactez un administrateur.',
            ], 403);
        }

        // Révoquer les anciens tokens (optionnel – 1 session active)
        $user->tokens()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token'   => $token,
            'user'    => $user,
        ], 200);
    }

    /**
     * POST /api/logout
     * Déconnexion (révoque le token courant).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * GET /api/me
     * Retourne l'utilisateur connecté.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}

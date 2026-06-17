<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Liste tous les utilisateurs (admin).
     */
    public function index(): JsonResponse
    {
        $users = User::withCount('signalements')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($users);
    }

    /**
     * POST /api/users
     * Créer un utilisateur / gestionnaire (admin).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'nom'      => $request->nom,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'citoyen',
        ]);

        return response()->json([
            'message' => 'Utilisateur créé.',
            'user'    => $user,
        ], 201);
    }

    /**
     * GET /api/users/{id}
     * Détail d'un utilisateur (admin).
     */
    public function show(int $id): JsonResponse
    {
        $user = User::withCount('signalements')->findOrFail($id);

        return response()->json($user);
    }

    /**
     * PUT /api/users/{id}
     * Modifier un utilisateur (admin).
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->only(['nom', 'email', 'role']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'user'    => $user->fresh(),
        ]);
    }

    /**
     * DELETE /api/users/{id}
     * Supprimer un utilisateur (admin).
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Empêcher un admin de se supprimer lui-même
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    /**
     * PATCH /api/users/{id}/toggle-block
     * Bloquer / débloquer un utilisateur (admin).
     */
    public function toggleBlock(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas bloquer votre propre compte.',
            ], 403);
        }

        $user->update(['is_blocked' => ! $user->is_blocked]);

        $status = $user->is_blocked ? 'bloqué' : 'débloqué';

        return response()->json([
            'message'    => "Utilisateur {$status}.",
            'is_blocked' => $user->is_blocked,
        ]);
    }

    /**
     * PUT /api/profile
     * L'utilisateur modifie son propre profil.
     */
    public function updateProfile(UpdateUserRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->only(['nom', 'email']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user'    => $user->fresh(),
        ]);
    }
}

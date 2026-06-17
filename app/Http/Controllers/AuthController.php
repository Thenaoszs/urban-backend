<?php 

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
    // Inscription pour les Citoyens [cite: 25]
    public function register(Request $request) {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6',
        ]); // [cite: 165]

        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Citoyen', // Rôle par défaut [cite: 135]
        ]);

        $token = $user->createToken('auth_token')->plainTextToken; // 

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    // Connexion commune (Citoyen, Gestionnaire, Admin) [cite: 30, 77]
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]); // [cite: 165]

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::make($request->password, ['fallback' => $user->password])) {
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Identifiants incorrectes.'], 401);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken; // 

        return response()->json(['user' => $user, 'token' => $token]);
    }

    // Déconnexion [cite: 34, 78]
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // Gestion du profil (Gestionnaires / Admin / Citoyen) [cite: 108]
    public function updateProfile(Request $request) {
        $user = $request->user();

        $request->validate([
            'nom' => 'sometimes|string|max:255', // [cite: 110]
            'email' => 'sometimes|string|email|unique:users,email,'.$user->id, // [cite: 111]
            'password' => 'sometimes|string|min:6', // [cite: 112]
        ]); // [cite: 165]

        if ($request->has('nom')) $user->nom = $request->nom; // [cite: 110]
        if ($request->has('email')) $user->email = $request->email; // [cite: 111]
        if ($request->has('password')) $user->password = Hash::make($request->password); // [cite: 112]

        $user->save();

        return response()->json(['message' => 'Profil mis à jour avec succès.', 'user' => $user]);
    }
}
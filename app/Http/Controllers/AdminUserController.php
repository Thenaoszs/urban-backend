<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller {
    // Lister tous les utilisateurs [cite: 116]
    public function index() {
        return response()->json(User::all());
    }

    // Ajouter un utilisateur/gestionnaire [cite: 117]
    public function store(Request $request) {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:Administrateur,Gestionnaire,Citoyen', // [cite: 120, 121, 122, 124]
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // [cite: 120]
        ]);

        return response()->json(['message' => 'Utilisateur créé.', 'user' => $user], 201);
    }

    // Modifier un utilisateur [cite: 118]
    public function update(Request $request, $id) {
        $user = User::findOrFail($id);

        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:Administrateur,Gestionnaire,Citoyen' // [cite: 120]
        ]);

        if ($request->has('nom')) $user->nom = $request->nom;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('password')) $user->password = Hash::make($request->password);
        if ($request->has('role')) $user->role = $request->role; // [cite: 120]

        $user->save();

        return response()->json(['message' => 'Utilisateur modifié.', 'user' => $user]);
    }

    // Supprimer un utilisateur [cite: 119]
    public function destroy($id) {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }
}
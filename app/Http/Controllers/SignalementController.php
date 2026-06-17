<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use App\Models\ImageSignalement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignalementController extends Controller {
    // Lister les signalements [cite: 57, 88]
    public function index(Request $request) {
        $user = $request->user();
        $query = Signalement::with(['images', 'auteur']); // [cite: 90, 94]

        // Si c'est un citoyen, il ne voit que ses propres signalements [cite: 59, 126]
        if ($user->role === 'Citoyen') {
            $query->where('utilisateur_id', $user->id);
        } else {
            // Filtres applicables aux Gestionnaires / Admins [cite: 97]
            if ($request->has('type')) $query->where('type', $request->type); // [cite: 99]
            if ($request->has('statut')) $query->where('statut', $request->statut); // [cite: 100]
            if ($request->has('date')) $query->whereDate('created_at', $request->date); // [cite: 101]
        }

        return response()->json($query->latest()->get());
    }

    // Création d'un signalement (Citoyen) [cite: 37, 195]
    public function store(Request $request) {
        $request->validate([
            'type' => 'required|in:Inondation,Dégât de la chaussée,Reseau électrique,Réseau d\'eau,Dechets et ordures,Forêt et verdure', // [cite: 40, 44, 45, 46, 47, 48]
            'description' => 'required|string', // [cite: 40]
            'latitude' => 'required|numeric', // [cite: 41, 49]
            'longitude' => 'required|numeric', // [cite: 41, 49]
            'images' => 'required|array|max:2', // Maximum 2 images [cite: 42, 55]
            'images.*' => 'image|mimes:jpeg,png,jpg|max:4096', // [cite: 152, 165]
        ]);

        // Créer le signalement [cite: 196]
        $signalement = Signalement::create([
            'utilisateur_id' => $request->user()->id,
            'type' => $request->type,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'statut' => 'En cours', // Statut initial [cite: 196]
        ]);

        // Gestion de l'upload des images [cite: 152]
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Note : La compression de l'image est censée se faire côté Mobile/Frontend avant l'envoi [cite: 56, 170]
                $path = $image->store('signalements', 'public');

                ImageSignalement::create([
                    'signalement_id' => $signalement->id,
                    'image_path' => Storage::url($path), // URL d'accès publique 
                ]);
            }
        }

        return response()->json($signalement->load('images'), 201);
    }

    // Consulter les détails d'un signalement [cite: 66, 88]
    public function show($id, Request $request) {
        $signalement = Signalement::with(['images', 'auteur'])->findOrFail($id); // [cite: 68, 90, 94]

        // Sécuriser l'accès pour les citoyens [cite: 153]
        if ($request->user()->role === 'Citoyen' && $signalement->utilisateur_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès interdit à ce signalement.'], 403);
        }

        return response()->json($signalement);
    }

    // Modifier le statut (Gestionnaire & Admin uniquement) [cite: 102, 198]
    public function updateStatus(Request $request, $id) {
        $request->validate([
            'statut' => 'required|in:En cours,Accepté,Rejeté,Traité' // [cite: 103, 104, 105, 106, 107]
        ]);

        $signalement = Signalement::findOrFail($id);
        $signalement->statut = $request->statut; // [cite: 103]
        $signalement->save();

        return response()->json([
            'message' => 'Statut du signalement mis à jour avec succès.', // [cite: 202]
            'signalement' => $signalement
        ]);
    }
}
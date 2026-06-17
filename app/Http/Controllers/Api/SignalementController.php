<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSignalementRequest;
use App\Http\Requests\UpdateStatutRequest;
use App\Models\ImageSignalement;
use App\Models\Signalement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignalementController extends Controller
{
    // ─── CITOYEN ──────────────────────────────────────────────────────────────

    /**
     * GET /api/signalements
     * Liste les signalements de l'utilisateur connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $signalements = Signalement::with(['images', 'utilisateur:id,nom'])
            ->where('utilisateur_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($signalements);
    }

    /**
     * POST /api/signalements
     * Créer un nouveau signalement (citoyen).
     */
    public function store(StoreSignalementRequest $request): JsonResponse
    {
        $signalement = Signalement::create([
            'utilisateur_id' => $request->user()->id,
            'type'           => $request->type,
            'description'    => $request->description,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'statut'         => 'en_cours',
        ]);

        // Traitement des images (max 2)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('signalements', 'public');

                ImageSignalement::create([
                    'signalement_id' => $signalement->id,
                    'image_path'     => $path,
                ]);
            }
        }

        return response()->json(
            $signalement->load(['images', 'utilisateur:id,nom']),
            201
        );
    }

    /**
     * GET /api/signalements/{id}
     * Détail d'un signalement.
     * Citoyen : seulement les siens. Gestionnaire/Admin : tous.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $signalement = Signalement::with(['images', 'utilisateur:id,nom'])
            ->findOrFail($id);

        // Un citoyen ne peut voir que ses propres signalements
        if (
            $request->user()->isCitoyen()
            && $signalement->utilisateur_id !== $request->user()->id
        ) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json($signalement);
    }

    // ─── GESTIONNAIRE / ADMIN ─────────────────────────────────────────────────

    /**
     * GET /api/signalements/all
     * Liste tous les signalements (gestionnaire/admin) avec filtres optionnels.
     */
    public function all(Request $request): JsonResponse
    {
        $query = Signalement::with(['images', 'utilisateur:id,nom'])
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return response()->json($query->get());
    }

    /**
     * PUT /api/signalements/{id}/statut
     * Changer le statut d'un signalement (gestionnaire/admin).
     */
    public function updateStatut(UpdateStatutRequest $request, int $id): JsonResponse
    {
        $signalement = Signalement::findOrFail($id);
        $signalement->update(['statut' => $request->statut]);

        return response()->json([
            'message'     => 'Statut mis à jour.',
            'signalement' => $signalement->load(['images', 'utilisateur:id,nom']),
        ]);
    }

    /**
     * DELETE /api/signalements/{id}
     * Supprimer un signalement et ses images (admin seulement).
     */
    public function destroy(int $id): JsonResponse
    {
        $signalement = Signalement::with('images')->findOrFail($id);

        // Supprimer les fichiers images du storage
        foreach ($signalement->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $signalement->delete();

        return response()->json(['message' => 'Signalement supprimé.']);
    }

    // ─── STATISTIQUES (dashboard) ─────────────────────────────────────────────

    /**
     * GET /api/stats
     * Statistiques globales pour le dashboard gestionnaire/admin.
     */
    public function stats(): JsonResponse
    {
        $total = Signalement::count();

        $parStatut = Signalement::selectRaw('statut, COUNT(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut');

        $parType = Signalement::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Évolution sur les 7 derniers jours
        $evolution = Signalement::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'total'     => $total,
            'parStatut' => $parStatut,
            'parType'   => $parType,
            'evolution' => $evolution,
        ]);
    }
}

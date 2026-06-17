<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {
    public function index() {
        // Nombre total de signalements [cite: 81]
        $totalSignalements = Signalement::count();

        // Répartition par type [cite: 82]
        $parType = Signalement::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        // Répartition par statut [cite: 83]
        $parStatut = Signalement::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();

        // Évolution temporelle (Graphiques d'évolution) [cite: 85]
        $evolution = Signalement::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return response()->json([
            'statistiques_generales' => [ // [cite: 84]
                'total' => $totalSignalements,
                'par_type' => $parType,
                'par_statut' => $parStatut,
                'evolution' => $evolution
            ]
        ]);
    }
}
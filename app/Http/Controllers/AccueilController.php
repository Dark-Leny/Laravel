<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use App\Models\Categorie;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccueilController extends Controller
{
    /**
     * Affichage de la page d'accueil avec données SQLite
     * SÉANCE 2 : Utiliser Eloquent pour les statistiques d'accueil
     */
    public function index()
    {
        // Statistiques réelles depuis la base de données
        $stats = [
            'totalLivres' => Livre::count(),
            'livresDisponibles' => Livre::disponible()->count(),
            'totalEmprunts' => 12, // Sera implémenté dans une séance future
            'totalUtilisateurs' => 25, // Sera implémenté dans une séance future
            'totalCategories' => Categorie::actives()->count()
        ];

        // Livres mis en avant (3 premiers livres de la base)
        $livresEnVedette = Livre::with('categorie')
            ->disponible()
            ->take(3)
            ->get();

        // Favoris des bibliothécaires
        $livresFavoris = Livre::with('categorie', 'favoritedByUsers')
            ->whereHas('favoritedByUsers', function ($query) {
                $query->where('role', '=', 'bibliothécaire');
            })
            ->withCount(['favoritedByUsers as favoris_count' => function ($query) {
                $query->where('role', '=', 'bibliothécaire');
            }])
            ->orderBy('favoris_count', 'desc')
            ->take(6)
            ->get();

        return view('welcome', [
            'stats' => $stats,
            'livresEnVedette' => $livresEnVedette,
            'livresFavoris' => $livresFavoris
        ]);
    }
}

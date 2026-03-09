<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Middleware pour authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Ajouter un livre aux favoris
     * Seul les bibliothécaires peuvent ajouter des favoris
     */
    public function store(Request $request, Livre $livre)
    {
        // Vérifier que l'utilisateur est bibliothécaire
        if (!auth()->user()->isBibliothecaire()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les bibliothécaires peuvent ajouter des favoris.'
            ], 403);
        }

        // Ajouter le livre aux favoris si ce n'est pas déjà fait
        auth()->user()->favoriteBooks()->syncWithoutDetaching($livre->id);

        return response()->json([
            'success' => true,
            'message' => 'Livre ajouté aux favoris.',
            'isFavoritized' => true
        ]);
    }

    /**
     * Supprimer un livre des favoris
     * Seul les bibliothécaires qui l'ont ajouté peuvent le retirer
     */
    public function destroy(Request $request, Livre $livre)
    {
        // Vérifier que l'utilisateur est bibliothécaire
        if (!auth()->user()->isBibliothecaire()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les bibliothécaires peuvent retirer des favoris.'
            ], 403);
        }

        // Retirer le livre des favoris
        auth()->user()->favoriteBooks()->detach($livre->id);

        return response()->json([
            'success' => true,
            'message' => 'Livre retiré des favoris.',
            'isFavoritized' => false
        ]);
    }

    /**
     * Obtenir la liste des favoris du bibliothécaire
     * Accessible à tous les utilisateurs authentifiés
     */
    public function getBibliothecaireFavorites(User $bibliothecaire)
    {
        // Vérifier que l'utilisateur est bien un bibliothécaire
        if (!$bibliothecaire->isBibliothecaire()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas un bibliothécaire.'
            ], 404);
        }

        $favorites = $bibliothecaire->favoriteBooks()
            ->with('categorie')
            ->get();

        return response()->json([
            'success' => true,
            'bibliothecaire' => $bibliothecaire->name,
            'favoriteBooks' => $favorites
        ]);
    }

    /**
     * Vérifier si un livre est en favori (pour AJAX)
     */
    public function check(Livre $livre)
    {
        if (!auth()->check()) {
            return response()->json(['isFavoritized' => false]);
        }

        $isFavoritized = auth()->user()
            ->favoriteBooks()
            ->where('livres.id', $livre->id)
            ->exists();

        return response()->json(['isFavoritized' => $isFavoritized]);
    }
}

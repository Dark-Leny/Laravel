<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    use HasFactory;

    protected $table = 'livres';

    protected $fillable = [
        'titre',
        'auteur',
        'annee',
        'nb_pages',
        'isbn',
        'resume',
        'couverture',
        'disponible',
        'categorie_id'
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'annee' => 'integer',
        'nb_pages' => 'integer'
    ];

    /**
     * Scope pour les livres disponibles
     */
    public function scopeDisponible($query)
    {
        return $query->where('disponible', true);
    }

    /**
     * Scope pour rechercher par titre ou auteur
     */
    public function scopeRecherche($query, $terme)
    {
        return $query->where('titre', 'like', '%' . $terme . '%')
            ->orWhere('auteur', 'like', '%' . $terme . '%');
    }

    /**
     * Un livre appartient à une catégorie
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeParCategorie($query, $categorieNom)
    {
        return $query->where('categorie', $categorieNom);
    }

    /**
     * Scope pour filtrer par catégorie via relation
     */
    public function scopeParCategorieSlug($query, $categorieSlug)
    {
        return $query->whereHas('categorie', function ($q) use ($categorieSlug) {
            $q->where('slug', $categorieSlug);
        });
    }

    /**
     * Accesseur pour l'URL du livre
     */
    public function getUrlAttribute()
    {
        return route('livre.show', $this->id);
    }

    /**
     * Les utilisateurs qui ont mis ce livre en favoris
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(
            User::class,
            'user_livre_favorite',
            'livre_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * Vérifier si le livre est favori pour un utilisateur
     */
    public function isFavoriteFor(?User $user = null)
    {
        if (is_null($user)) {
            return false;
        }
        return $this->favoritedByUsers()->where('users.id', $user->id)->exists();
    }
}

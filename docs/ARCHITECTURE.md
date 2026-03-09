# 📊 Documentation de la Architecture BiblioTech

## 📁 Fichiers de Documentation

Cette section contient la documentation graphique et technique de l'architecture BiblioTech.

### 1. **[DIAGRAMME_CAS_UTILISATION.md](DIAGRAMME_CAS_UTILISATION.md)**
   - **Format** : Diagramme Mermaid (graphique)
   - **Contenu** :
     - Vue d'ensemble des acteurs (User, Bibliothécaire, Admin, Système)
     - Cas d'utilisation principaux
     - Restrictions et permissions par rôle
     - Architecture de sécurité

   **À utiliser pour** : Comprendre les interactions utilisateurs et fonctionnalités

---

### 2. **[SCHEMA_RELATIONNEL.md](SCHEMA_RELATIONNEL.md)**
   - **Format** : Diagramme Mermaid ER + Tableaux détaillés
   - **Contenu** :
     - Diagramme Entity-Relationship (ER)
     - Description détaillée de chaque table
     - Colonnes, types, contraintes
     - Relations Eloquent
     - Exemples de requêtes
   
   **À utiliser pour** : Comprendre la structure de la base de données

---

## 🎯 Structure Simplifiée

```
┌─────────────────┐
│     USERS       │ (Authentification & Rôles)
└────────┬────────┘
         │
         ├──────────────────┐
         │                  │
         ▼                  ▼
    ┌─────────────┐   ┌──────────────────┐
    │   LIVRES    │   │ USER_LIVRE_      │
    │ (Catalogue) │   │ FAVORITE (Pivot) │
    └──────┬──────┘   └──────────────────┘
           │
           ▼
    ┌──────────────┐
    │ CATEGORIES   │
    └──────────────┘
```

---

## 🔐 Contrôle d'Accès (RBAC)

### Rôles Disponibles

| Rôle | Code | Accès | Remarques |
|------|------|-------|-----------|
| 👤 Utilisateur | `user` | Lecture seule | Affichage catalog + favoris |
| 📚 Bibliothécaire | `bibliothécaire` | Lecture/Écriture | Gère favoris + livres |
| ⚙️ Admin | `admin` | Total | Accès complet système |

### Permissions Clés (Séance 5)

✅ **Favoris - Seul Bibliothécaire**
```php
if (auth()->user()->isBibliothecaire()) {
    // Peut ajouter/retirer des favoris
}
```

✅ **Voir les Favoris - Tous les Rôles**
```php
// Tous les utilisateurs peuvent voir les favoris du bibliothécaire
$favoriteBooks = $user->favoriteBooks()->get();
```

---

## 📱 Points d'Accès

### Page d'Accueil (`/`)
- ✅ Statistiques globales
- ✅ Livres recommandés (3 premiers)
- ✅ **Favoris du bibliothécaire** (6 plus favorisés)
- Pour tous les rôles

### Fiche Livre (`/livres/{id}`)
- ✅ **Bibliothécaire** : Bouton ajouter/retirer favoris
- ✅ **Autres rôles** : Voir combien de bibliothécaires l'ont favorisé
- Pour utilisateurs authentifiés

### Catalogue (`/livres`)
- ✅ Liste complète avec recherche et filtre
- ✅ Création/modification/suppression (bibliothécaire)
- Pour tous

---

## 🔄 Flux de Données - Favoris

```
Bibliothécaire clique "Ajouter" 
         │
         ▼
   Validation rôle ✓
         │
         ▼
   API POST /livres/{id}/favorite
         │
         ▼
   Insert dans USER_LIVRE_FAVORITE
         │
         ▼
   localStorage.setItem() + Reload
         │
         ▼
   Bouton devient rouge + persist
```

---

## 🛠️ Technologies Utilisées

- **Backend** : Laravel 11
- **Frontend** : Bootstrap 5 + Vanilla JS
- **Base de Données** : SQLite
- **ORM** : Eloquent
- **Authentification** : Laravel Session
- **CSRF Protection** : Token Laravel

---

## 📚 Fichiers Clés

### Modèles
- [app/Models/User.php](../app/Models/User.php) - Relation `favoriteBooks()`
- [app/Models/Livre.php](../app/Models/Livre.php) - Relation `favoritedByUsers()` + `isFavoriteFor()`
- [app/Models/Categorie.php](../app/Models/Categorie.php) - Relation `livres()`

### Contrôleurs
- [app/Http/Controllers/FavoriteController.php](../app/Http/Controllers/FavoriteController.php) - API favoris
- [app/Http/Controllers/AccueilController.php](../app/Http/Controllers/AccueilController.php) - Page d'accueil

### Routes
- [routes/web.php](../routes/web.php) - Routes authentifiées pour favoris

### Vues
- [resources/views/components/favorite-button.blade.php](../resources/views/components/favorite-button.blade.php) - Composant favori
- [resources/views/welcome.blade.php](../resources/views/welcome.blade.php) - Page d'accueil

### Migrations
- [database/migrations/2026_03_09_create_user_livre_favorite_table.php](../database/migrations/2026_03_09_create_user_livre_favorite_table.php) - Table pivot

---

## 🚀 Comment Utiliser

### Développement
1. Consulter [SCHEMA_RELATIONNEL.md](SCHEMA_RELATIONNEL.md) pour la structure BD
2. Consulter [DIAGRAMME_CAS_UTILISATION.md](DIAGRAMME_CAS_UTILISATION.md) pour les fonctionnalités

### Déploiement
- ✅ Migrations en place
- ✅ Modèles configurés
- ✅ Routes protégées par middleware
- ✅ Validation côté serveur

### Tests
- Créer un bibliothécaire : `biblio@test.fr`
- Ajouter un livre aux favoris
- Vérifier l'affichage en tant qu'utilisateur ordinaire
- Consulter la page d'accueil → Voir la section favoris

---

## 📊 Statistiques

| Élément | Nombre |
|---------|--------|
| Tables | 4 (+ 2 tables Laravel par défaut) |
| Relations Eloquent | 6 |
| Routes favoris | 4 |
| Contrôleurs nouveaux | 1 (FavoriteController) |
| Migrations nouvelles | 1 |
| Composants Blade | 2 modifications |

---

## 🔍 Voir Aussi

- [README.md](../README.md) - Accueil du projet
- [SOLUTIONS-SUMMARY.md](../SOLUTIONS-SUMMARY.md) - Résumé des solutions
- [seance-05/](./seance-05/) - Documentation spécifique Séance 5

---

**Dernière mise à jour** : 9 Mars 2026  
**Version** : 1.0 (Séance 5 - Favoris et Rôles)

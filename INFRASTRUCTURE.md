# Architecture MVC Vanilla - Infrastructure Créée

## ✅ Tâches Complétées

### 1. Arborescence des Dossiers
```
✓ /app/controllers     - Contrôleurs métier
✓ /app/models          - Modèles (requêtes SQL)
✓ /app/views           - Vues HTML/CSS
✓ /config              - Configuration
✓ /core                - Moteur (Router)
✓ /public              - Point d'accès public
  ✓ /css               - Feuilles de style
  ✓ /js                - Fichiers JavaScript
  ✓ /uploads           - Fichiers uploadés
```

### 2. Fichiers de Sécurité
```
✓ /.htaccess           - Sécurité racine (bloque accès aux dossiers sensibles)
✓ /public/.htaccess    - Routage URL vers index.php
✓ .gitignore           - Fichiers à ignorer par Git
```

### 3. Fichiers de Configuration
```
✓ /config/database.php - Configuration MySQL/MariaDB + fonction connectDatabase()
✓ /config/app.php      - Configuration générale (DEBUG, URL, uploads, session)
```

### 4. Moteur de Routage
```
✓ /core/Router.php     - Classe Router robuste avec:
  - Enregistrement de routes GET/POST
  - Matching automatique avec paramètres dynamiques (:id)
  - Instanciation automatique des contrôleurs
  - Gestion des erreurs 404 et 500
  - Pas de dépendances externes
```

### 5. Front Controller
```
✓ /public/index.php    - Point d'entrée unique avec:
  - Chargement de la configuration
  - Démarrage de la session (avec timeout)
  - Gestion globale des erreurs et exceptions
  - Try/catch sur le dispatch
  - Logging des erreurs
```

### 6. Test et Documentation
```
✓ /app/controllers/HomeController.php    - Contrôleur de test
✓ /app/views/home_index.php              - Vue d'accueil
✓ /app/views/test_router.php             - Vue de test du routeur
✓ README.md                              - Documentation complète
✓ INFRASTRUCTURE.md                      - Ce fichier
```

---

## 🚀 Comment Tester l'Arborescence

### Accédez à:
- **http://localhost/gestion_memoires_uatm**
  - Affiche la page d'accueil avec informations système
  
- **http://localhost/gestion_memoires_uatm/test**
  - Page de test confirme le fonctionnement du routeur

---

## 📋 Checklist pour le Tech Lead

### Avant de Développer les Modules

- [ ] Vérifier que le routeur fonctionne (accéder à `/test`)
- [ ] Vérifier que la base de données est créée (importer `01_create_tables.sql`)
- [ ] Tester la connexion PDO dans `config/database.php`
- [ ] Configurer les variables d'environnement sensibles
- [ ] Activer `mod_rewrite` sur Apache (`a2enmod rewrite`)

### Avant la Mise en Production

- [ ] Définir `APP_DEBUG = false` dans `config/app.php`
- [ ] Configurer les headers de sécurité (HSTS, CSP, etc.)
- [ ] Mettre en place l'authentification sécurisée
- [ ] Valider et nettoyer TOUTES les entrées utilisateur
- [ ] Tester les injections SQL sur PDO (impossible si prepare/execute utilisés)
- [ ] Forcer HTTPS
- [ ] Sécuriser le dossier `/config`

---

## 🔒 Sécurité Implémentée

✅ **Pas d'accès direct aux fichiers PHP**: Seul `/public/index.php` est accessible  
✅ **Routeur sécurisé**: Pas de `eval()` ou d'exécution dynamique dangereuse  
✅ **PDO + Prepared Statements**: Injection SQL impossible (à respecter dans les modèles)  
✅ **Session avec timeout**: Destruction automatique après 30 minutes d'inactivité  
✅ **Gestion globale des erreurs**: Les détails ne s'affichent qu'en mode debug  
✅ **Logging complet**: Tous les événements importants sont loggés  
✅ **Configuration externalisée**: Pas de données sensibles en dur dans le code  

---

## 📝 Conventions Respectées

| Élément | Convention | Exemple |
|---------|-----------|---------|
| Contrôleurs | PascalCase | `MemoireController.php` |
| Modèles | PascalCase | `Memoire.php` |
| Méthodes | camelCase | `getMemoiresValides()` |
| Variables | camelCase | `$donneesUtilisateur` |
| Vues | snake_case | `memoire_detail.php` |
| Tables SQL | snake_case | `id_memoire` |
| Colonnes SQL | snake_case | `date_creation` |

---

## 🔄 Flux de Requête (Résumé)

1. **URL**: `/memoires/123`
2. **Apache**: Redirige vers `/public/index.php?url=memoires/123`
3. **index.php**: Charge config, session, instancie Router
4. **Router**: Match `/memoires/123` → `MemoireController@afficher` avec param `123`
5. **Contrôleur**: `MemoireController::afficher(123)` récupère les données
6. **Modèle**: Requête PDO à la base de données
7. **Vue**: Affiche le mémoire en HTML
8. **Réponse**: HTML au navigateur

---

## 📚 Structure d'un Modèle (à créer)

```php
<?php

class Memoire
{
    private $pdo;
    
    public function __construct()
    {
        require_once CONFIG_PATH . '/database.php';
        $this->pdo = connectDatabase();
    }
    
    /**
     * Récupère tous les mémoires
     */
    public function getAll()
    {
        $sql = "SELECT * FROM memoire WHERE id_statut != 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un mémoire par ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM memoire WHERE id_memoire = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
```

---

## 📝 Notes Importantes

⚠️ **Les contrôleurs n'ont PAS d'authentification encore**  
⚠️ **Les modèles doivent être créés selon le pattern montré**  
⚠️ **Les vues DOIVENT échapper les variables avec `htmlspecialchars()`**  
⚠️ **Les requêtes SQL DOIVENT utiliser `prepare()` et `execute()`**  

---

**Date**: 25 mai 2026  
**Version**: 1.0.0  
**Status**: ✅ Infrastructure Prête pour le Développement

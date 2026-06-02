# Plateforme de Gestion des Mémoires UATM

## Architecture

Cette application suit une architecture MVC (Modèle-Vue-Contrôleur) vanilla sans framework.

```
gestion_memoires_uatm/
├── app/
│   ├── controllers/      # Logique métier et aiguillage des requêtes
│   ├── models/           # Classes d'accès aux données (requêtes SQL)
│   └── views/            # Templates HTML/CSS (pas de logique métier)
├── config/               # Fichiers de configuration
│   ├── app.php           # Configuration générale
│   └── database.php      # Paramètres de connexion
├── core/                 # Moteur de l'application
│   └── Router.php        # Système de routage des URLs
├── public/               # Point d'accès public (seul dossier accessible par le web)
│   ├── index.php         # Front Controller (point d'entrée unique)
│   ├── css/              # Feuilles de style CSS
│   ├── js/               # Fichiers JavaScript
│   └── uploads/          # Fichiers uploadés par les utilisateurs
├── 01_create_tables.sql  # Script DDL de création de la base de données
├── .htaccess             # Sécurité et redirection URL (racine)
└── .gitignore            # Fichiers à ignorer par Git
```

## Sécurité

### Principes appliqués

1. **Pas d'accès direct aux fichiers PHP**: Seul `public/index.php` est accessible
2. **Préparation des requêtes SQL**: Utilisation de PDO avec `prepare()` et `execute()` pour éviter les injections SQL
3. **Échappement XSS**: Toute sortie HTML utilise `htmlspecialchars(ENT_QUOTES, 'UTF-8')`
4. **Hachage des mots de passe**: `password_hash()` et `password_verify()`
5. **Configuration centralisée**: Les paramètres sensibles dans `/config`

### Fichiers .htaccess

- **Racine (`.htaccess`)**: Bloque l'accès aux dossiers sensibles et redirige vers `/public`
- **Public (`public/.htaccess`)**: Redirige les requêtes vers `index.php` en passant l'URL en paramètre

## Routage

Les routes sont enregistrées dans `public/index.php` selon ce pattern:

```php
$router->get('/memoires', 'MemoireController@lister');
$router->post('/memoires/creer', 'MemoireController@creer');
$router->get('/memoires/:id', 'MemoireController@afficher');
```

### Paramètres dynamiques

Les paramètres dans l'URL (`:id`) sont extraits automatiquement et passés à la méthode du contrôleur:

```php
// Route: /memoires/:id
public function afficher($id)
{
    // $id contient la valeur depuis l'URL
}
```

## Conventions de nommage

- **Contrôleurs**: PascalCase (ex: `MemoireController.php`)
- **Modèles**: PascalCase (ex: `Memoire.php`)
- **Méthodes et variables**: camelCase (ex: `getMemoiresValides()`)
- **Vues**: snake_case (ex: `memoire_detail.php`)
- **Colonnes SQL**: snake_case (ex: `id_memoire`, `date_creation`)

## Configuration

### Base de données

Modifier les paramètres dans `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_memoires');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Application

Les paramètres globaux se trouvent dans `config/app.php`:

- `APP_DEBUG`: Mode debug (affiche les erreurs détaillées)
- `APP_URL`: URL de base de l'application
- `UPLOAD_DIR`: Dossier de stockage des fichiers
- `MAX_FILE_SIZE`: Limite de taille des fichiers
- `SESSION_TIMEOUT`: Durée de la session en secondes

## Flux de requête

1. L'utilisateur accède à une URL: `/memoires`
2. Le `.htaccess` redirige vers `public/index.php?url=memoires`
3. `public/index.php` charge la configuration et initialise le Router
4. Le Router matching l'URL à une route enregistrée: `MemoireController@lister`
5. Le contrôleur `MemoireController` appelle la méthode `lister()`
6. La méthode récupère les données via les modèles (PDO)
7. Affiche la vue HTML correspondante
8. Les erreurs sont loggées et gérées globalement

## Installation

### Prérequis

- PHP 8.0+
- MySQL/MariaDB
- Apache avec `mod_rewrite` activé

### Étapes

1. Cloner le projet dans `/opt/lampp/htdocs/gestion_memoires_uatm`
2. Importer la base de données: `mysql -u root < 01_create_tables.sql`
3. S'assurer que Apache peut écrire dans `public/uploads/`: `chmod 755 public/uploads/`
4. Vérifier la configuration dans `config/database.php` et `config/app.php`
5. Accéder à `http://localhost/gestion_memoires_uatm`

## Structure d'un contrôleur

```php
<?php

class MemoireController
{
    /**
     * Liste les mémoires (GET)
     */
    public function lister()
    {
        // Récupérer les données du modèle
        require_once APP_PATH . '/models/Memoire.php';
        $memoireModel = new Memoire();
        $memoires = $memoireModel->getAll();
        
        // Afficher la vue
        require_once APP_PATH . '/views/memoire_liste.php';
    }
    
    /**
     * Affiche un mémoire (GET avec paramètre :id)
     */
    public function afficher($id)
    {
        // Validation et récupération
        require_once APP_PATH . '/models/Memoire.php';
        $memoireModel = new Memoire();
        $memoire = $memoireModel->getById($id);
        
        if (!$memoire) {
            http_response_code(404);
            echo "Mémoire non trouvé";
            return;
        }
        
        // Afficher la vue
        require_once APP_PATH . '/views/memoire_detail.php';
    }
    
    /**
     * Crée un mémoire (POST)
     */
    public function creer()
    {
        // Valider les données POST
        // Créer le mémoire
        // Rediriger
    }
}
```

## Gestion des erreurs

Les erreurs sont gérées globalement dans `public/index.php`:

- Exceptions non gérées
- Erreurs PHP
- Erreurs fatales

En mode debug (`APP_DEBUG = true`), les détails sont affichés. En production, un message générique s'affiche et les détails sont loggés.

## Logging

Tous les événements importants sont loggés dans les fichiers de log du serveur:

```
error_log('Message d\'information');
```

## Notes de sécurité

⚠️ **OBLIGATOIRE** avant la mise en production:

1. [ ] Définir `APP_DEBUG = false`
2. [ ] Utiliser des variables d'environnement pour les identifiants BD
3. [ ] Configurer les headers de sécurité (HSTS, CSP, X-Frame-Options)
4. [ ] Mettre en place un système d'authentification sécurisé
5. [ ] Valider et nettoyer TOUTES les entrées utilisateur
6. [ ] Utiliser HTTPS

---

**Auteur**: Équipe de développement UATM  
**Version**: 1.0.0  
**Date**: 25 mai 2026

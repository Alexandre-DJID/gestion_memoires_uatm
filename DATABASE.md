# Classe Database - Singleton PDO Sécurisé

## Vue d'Ensemble

La classe `Database` implémente le **pattern Singleton** pour gérer une connexion unique et sécurisée à la base de données MySQL/MariaDB via PDO.

### Avantages

✅ **Une seule connexion**: Même instance réutilisée partout  
✅ **PDO Sécurisé**: Requêtes préparées natives (ATTR_EMULATE_PREPARES = false)  
✅ **Mode Exception**: Gestion centralisée des erreurs SQL  
✅ **Transactions**: Support complet ACID (beginTransaction, commit, rollback)  
✅ **Raccourcis**: Méthodes helpers (fetchOne, fetchAll, execute)  
✅ **Zéro dépendances**: Vanilla PHP, utilise PDO natif

---

## Configuration

### Fichier: `/config/database.php`

Contient les paramètres de connexion:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_memoires');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);
```

⚠️ **Important**: En production, utilisez des variables d'environnement (`.env`) au lieu de constantes.

---

## Utilisation Basique

### Récupérer l'Instance

```php
// Récupérer l'instance unique (Singleton)
$db = Database::getInstance();

// Récupérer la connexion PDO brute
$pdo = $db->getConnection();
```

### Méthodes Disponibles

#### 1. `execute($sql, $params)`
Exécute une requête préparée et retourne le `PDOStatement`.

```php
$stmt = $db->execute("SELECT * FROM utilisateur WHERE id_user = ?", [1]);
$user = $stmt->fetch();
```

#### 2. `fetchOne($sql, $params)`
Récupère un seul résultat (tableau associatif).

```php
$user = $db->fetchOne("SELECT * FROM utilisateur WHERE email = ?", ['user@example.com']);
echo $user['nom']; // "Dupont"
```

#### 3. `fetchAll($sql, $params)`
Récupère tous les résultats (tableau de tableaux).

```php
$users = $db->fetchAll("SELECT * FROM utilisateur");
foreach ($users as $user) {
    echo $user['prenom'];
}
```

#### 4. `lastInsertId()`
Récupère l'ID du dernier enregistrement inséré.

```php
$db->execute("INSERT INTO utilisateur (nom, prenom) VALUES (?, ?)", ['Dupont', 'Jean']);
$newId = $db->lastInsertId();
echo $newId; // 5
```

---

## Transactions ACID

Les transactions garantissent que plusieurs opérations sont atomiques (tout ou rien).

### Exemple: Insérer un Mémoire et son Dépôt

```php
$db = Database::getInstance();

try {
    // Démarrer la transaction
    $db->beginTransaction();
    
    // Opération 1: Créer le mémoire
    $db->execute(
        "INSERT INTO memoire (theme, resume, id_statut) VALUES (?, ?, ?)",
        ['Mon sujet', 'Résumé du mémoire', 1]
    );
    $memoireId = $db->lastInsertId();
    
    // Opération 2: Créer l'association dépôt
    $db->execute(
        "INSERT INTO deposer (id_user, id_memoire) VALUES (?, ?)",
        [$userId, $memoireId]
    );
    
    // Valider (appliquer tous les changements)
    $db->commit();
    
    echo "✓ Mémoire déposé avec succès";
} catch (PDOException $e) {
    // Annuler (rejeter tous les changements)
    $db->rollback();
    
    error_log("Erreur dépôt: " . $e->getMessage());
    echo "✗ Erreur lors du dépôt";
}
```

**Important**: Si n'importe quelle opération échoue, `PDOException` est levée et `rollback()` annule tout.

---

## Sécurité

### Requêtes Préparées (Protection contre les Injections SQL)

✅ **BON** - Requête préparée avec paramètres:
```php
$user = $db->fetchOne("SELECT * FROM utilisateur WHERE email = ?", [$email]);
```

❌ **MAUVAIS** - Concaténation de chaîne (Injection SQL!):
```php
$user = $db->fetchOne("SELECT * FROM utilisateur WHERE email = '$email'");
```

### Attributs PDO Sécurisés

La classe configure trois attributs importants:

| Attribut | Valeur | Raison |
|----------|--------|--------|
| `ATTR_ERRMODE` | `ERRMODE_EXCEPTION` | Exceptions levées au lieu d'erreurs silencieuses |
| `ATTR_DEFAULT_FETCH_MODE` | `FETCH_ASSOC` | Retourne des tableaux associatifs propres |
| `ATTR_EMULATE_PREPARES` | `false` | Utilise les vrais préparés du serveur (+ sécurisé) |

---

## Modèle: Exemple Complet

```php
<?php

class Utilisateur
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupère un utilisateur par ID
     */
    public function getById($id)
    {
        return $this->db->fetchOne(
            "SELECT * FROM utilisateur WHERE id_user = ?",
            [$id]
        );
    }
    
    /**
     * Récupère tous les utilisateurs
     */
    public function getAll()
    {
        return $this->db->fetchAll("SELECT * FROM utilisateur");
    }
    
    /**
     * Crée un nouvel utilisateur
     */
    public function create($nom, $prenom, $email, $motDePasse, $typeUtilisateur)
    {
        $motDePasseHashe = password_hash($motDePasse, PASSWORD_BCRYPT);
        
        $this->db->execute(
            "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, type_utilisateur)
             VALUES (?, ?, ?, ?, ?)",
            [$nom, $prenom, $email, $motDePasseHashe, $typeUtilisateur]
        );
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Met à jour un utilisateur
     */
    public function update($id, $nom, $prenom)
    {
        $this->db->execute(
            "UPDATE utilisateur SET nom = ?, prenom = ? WHERE id_user = ?",
            [$nom, $prenom, $id]
        );
    }
    
    /**
     * Supprime un utilisateur
     */
    public function delete($id)
    {
        $this->db->execute(
            "DELETE FROM utilisateur WHERE id_user = ?",
            [$id]
        );
    }
}
```

---

## Contrôleur: Utilisation du Modèle

```php
<?php

class UtilisateurController
{
    /**
     * Affiche un utilisateur
     */
    public function afficher($id)
    {
        require_once APP_PATH . '/models/Utilisateur.php';
        $userModel = new Utilisateur();
        
        $user = $userModel->getById($id);
        
        if (!$user) {
            http_response_code(404);
            echo "Utilisateur non trouvé";
            return;
        }
        
        // Passer à la vue
        require_once APP_PATH . '/views/utilisateur_detail.php';
    }
    
    /**
     * Liste les utilisateurs
     */
    public function lister()
    {
        require_once APP_PATH . '/models/Utilisateur.php';
        $userModel = new Utilisateur();
        
        $users = $userModel->getAll();
        
        // Passer à la vue
        require_once APP_PATH . '/views/utilisateur_liste.php';
    }
    
    /**
     * Crée un utilisateur
     */
    public function creer()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Méthode non autorisée";
            return;
        }
        
        // Récupérer et valider les données
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $motDePasse = $_POST['motDePasse'] ?? '';
        
        // Validations...
        
        try {
            require_once APP_PATH . '/models/Utilisateur.php';
            $userModel = new Utilisateur();
            
            $newId = $userModel->create($nom, $prenom, $email, $motDePasse, 'etudiant');
            
            // Rediriger vers le nouvel utilisateur
            header('Location: ' . APP_URL . '/utilisateur/' . $newId);
        } catch (PDOException $e) {
            error_log('Erreur création utilisateur: ' . $e->getMessage());
            echo "✗ Erreur lors de la création";
        }
    }
}
```

---

## Routes

Enregistrez les routes dans `/public/index.php`:

```php
$router->get('/utilisateurs', 'UtilisateurController@lister');
$router->get('/utilisateurs/:id', 'UtilisateurController@afficher');
$router->post('/utilisateurs/creer', 'UtilisateurController@creer');
```

---

## Test

Accédez à `/db-test/info`, `/db-test/queries`, `/db-test/transactions` pour tester:
- ✓ Connexion à la base de données
- ✓ Récupération des données
- ✓ Transactions ACID

---

## Résumé

| Concept | Implémentation |
|---------|----------------|
| **Pattern** | Singleton (une seule instance) |
| **Connexion** | PDO MySQLi |
| **Requêtes** | Préparées (sécurité) |
| **Erreurs** | Exceptions |
| **Transactions** | Supportées (ACID) |
| **Fetch** | Associatif (tableaux clairs) |

---

**La classe Database est prête pour tous vos modèles!**
# Configuration Base de Données - Résumé

## ✅ Tâches Complétées

### 1. Classe Database (Singleton)
```
✓ /core/Database.php
```

**Caractéristiques:**
- Pattern **Singleton**: Une seule instance pour toute l'application
- Constructeur **privé**: Empêche l'instanciation directe
- Constructeur clone **privé**: Empêche le clonage
- Méthode `__wakeup()`: Empêche la désérialisation

**Attributs PDO Sécurisés:**
```php
[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]
```

**Méthodes Publiques:**
- `getInstance()` - Récupère l'instance unique
- `getConnection()` - Retourne l'objet PDO brut
- `execute($sql, $params)` - Exécute une requête préparée
- `fetchOne($sql, $params)` - Récupère 1 résultat
- `fetchAll($sql, $params)` - Récupère tous les résultats
- `lastInsertId()` - Récupère l'ID du dernier INSERT
- `beginTransaction()`, `commit()`, `rollback()` - Gestion des transactions

### 2. Configuration Base de Données
```
✓ /config/database.php (mise à jour)
```

**Constantes:**
```php
DB_HOST       = 'localhost'
DB_NAME       = 'gestion_memoires'
DB_USER       = 'root'
DB_PASS       = ''
DB_CHARSET    = 'utf8mb4'
DB_PORT       = 3306
```

### 3. Front Controller (mise à jour)
```
✓ /public/index.php
```

**Ajout:**
- Chargement de la classe `Database` après la configuration

```php
require_once CORE_PATH . '/Database.php';
```

### 4. Contrôleur de Test
```
✓ /app/controllers/DatabaseTestController.php
```

**Actions:**
- `info()` - Affiche les infos et tables de la BD
- `queries()` - Teste les méthodes de requête
- `transactions()` - Explique les transactions ACID

### 5. Vues de Test (3 fichiers)
```
✓ /app/views/database_test_info.php
✓ /app/views/database_test_queries.php
✓ /app/views/database_test_transactions.php
```

**Fonctionnalités:**
- Affichage des infos BD (version, charset, tables)
- Lecture des niveaux, statuts, rôles
- Tests des 3 méthodes (fetchOne, fetchAll, execute)
- Explications sur les transactions ACID

### 6. Routes Enregistrées
```php
$router->get('/db-test/info', 'DatabaseTestController@info');
$router->get('/db-test/queries', 'DatabaseTestController@queries');
$router->get('/db-test/transactions', 'DatabaseTestController@transactions');
```

### 7. Documentation
```
✓ DATABASE.md
```

**Contenu:**
- Guide complet d'utilisation
- Exemples de modèles
- Exemples de contrôleurs
- Explications des transactions
- Bonnes pratiques de sécurité

---

## 🚀 Comment Tester

### Test 1: Connexion
```
http://localhost/gestion_memoires_uatm/db-test/info
```
Affiche:
- ✓ Informations du serveur MySQL
- ✓ Liste de toutes les tables
- ✓ Données des dictionnaires (niveaux, statuts, rôles)

### Test 2: Requêtes
```
http://localhost/gestion_memoires_uatm/db-test/queries
```
Teste:
- ✓ `fetchOne()` - Récupère 1 niveau
- ✓ `fetchAll()` - Récupère tous les statuts
- ✓ `execute()` - Compte les statuts

### Test 3: Transactions
```
http://localhost/gestion_memoires_uatm/db-test/transactions
```
Explique:
- ✓ Qu'est-ce qu'une transaction
- ✓ Exemple réel: Dépôt de mémoire
- ✓ Atomicité, cohérence, isolation, durabilité

---

## 💡 Utilisation dans les Modèles

### Exemple 1: Modèle Simple

```php
<?php

class Memoire
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getAll()
    {
        return $this->db->fetchAll("SELECT * FROM memoire");
    }
    
    public function getById($id)
    {
        return $this->db->fetchOne(
            "SELECT * FROM memoire WHERE id_memoire = ?",
            [$id]
        );
    }
    
    public function create($theme, $resume)
    {
        $this->db->execute(
            "INSERT INTO memoire (theme, resume, id_statut) VALUES (?, ?, ?)",
            [$theme, $resume, 1]
        );
        return $this->db->lastInsertId();
    }
}
```

### Exemple 2: Avec Transactions

```php
<?php

class DepotMemoire
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function deposer($userId, $theme, $resume)
    {
        try {
            // Démarrer la transaction
            $this->db->beginTransaction();
            
            // 1. Créer le mémoire
            $this->db->execute(
                "INSERT INTO memoire (theme, resume, id_statut) VALUES (?, ?, ?)",
                [$theme, $resume, 1]
            );
            $memoireId = $this->db->lastInsertId();
            
            // 2. Créer l'association
            $this->db->execute(
                "INSERT INTO deposer (id_user, id_memoire) VALUES (?, ?)",
                [$userId, $memoireId]
            );
            
            // Valider
            $this->db->commit();
            
            return [
                'success' => true,
                'memoire_id' => $memoireId
            ];
        } catch (PDOException $e) {
            // Annuler
            $this->db->rollback();
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
```

---

## ✅ Checklist de Sécurité

- [x] PDO utilisé (pas de `mysql_*` ou `mysqli_*`)
- [x] Requêtes préparées obligatoires (paramètres `?`)
- [x] ATTR_EMULATE_PREPARES = false (vrais préparés)
- [x] Mode Exception activé (gestion erreurs centralisée)
- [x] Singleton (une connexion unique)
- [x] Transactions supportées (atomicité)
- [ ] Variables d'environnement en production (au lieu de constantes)
- [ ] HTTPS activé en production
- [ ] Logs sécurisés (pas de données sensibles loggées)

---

## 📊 Fichiers Créés/Modifiés

| Fichier | Statut | Type |
|---------|--------|------|
| `/core/Database.php` | ✅ Créé | Classe Singleton |
| `/config/database.php` | ℹ️ Existait | Config (constants) |
| `/public/index.php` | 🔄 Modifié | Front Controller |
| `/app/controllers/DatabaseTestController.php` | ✅ Créé | Contrôleur Test |
| `/app/views/database_test_info.php` | ✅ Créé | Vue Test |
| `/app/views/database_test_queries.php` | ✅ Créé | Vue Test |
| `/app/views/database_test_transactions.php` | ✅ Créé | Vue Test |
| `DATABASE.md` | ✅ Créé | Documentation |

---

## 🔗 Architecture Finale

```
index.php
    ↓ (charge)
Database.php (Singleton)
    ↓ (initialise)
PDO Connection
    ↓ (utilisé par)
Modèles (Utilisateur, Memoire, etc.)
    ↓ (appelés par)
Contrôleurs
    ↓ (affichent)
Vues
```

---

## 📝 Notes Importantes

### Pour les Modèles
Tous les modèles doivent:
1. Instancier `Database::getInstance()` dans le constructeur
2. Utiliser `$this->db->prepare()` ou les méthodes helpers
3. JAMAIS concaténer les chaînes dans les requêtes SQL

### Pour les Contrôleurs
1. Inclure le modèle: `require_once APP_PATH . '/models/NomModel.php'`
2. Instancier: `$model = new NomModel()`
3. Appeler les méthodes
4. Passer à la vue

### Pour les Vues
1. TOUJOURS échapper les variables: `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
2. Pas de requêtes SQL
3. Pas de logique métier

---

**La connexion à la base de données est sécurisée et prête pour le développement!**

Prochaine étape: Créer les modèles pour chaque entité métier.
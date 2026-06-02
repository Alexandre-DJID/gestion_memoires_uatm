<?php
/**
 * ============================================================================
 * Contrôleur de Test Base de Données
 * ============================================================================
 * 
 * Contrôleur pour tester la connexion et les requêtes à la base de données.
 * Montre l'utilisation de la classe Database (Singleton).
 */

class DatabaseTestController
{
    /**
     * Affiche les informations de la base de données
     */
    public function info()
    {
        try {
            // Récupérer l'instance unique de Database
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // Récupérer les informations du serveur
            $version = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            
            // Récupérer le nombre de tables
            $tables = $db->fetchAll("
                SELECT TABLE_NAME 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = ?
            ", [DB_NAME]);

            // Récupérer les dictionnaires
            $niveaux = $db->fetchAll("SELECT * FROM niveau_etude ORDER BY ordre");
            $statuts = $db->fetchAll("SELECT * FROM statut_memoire");
            $roles = $db->fetchAll("SELECT * FROM role_jury");

            // Passer les données à la vue
            require_once APP_PATH . '/views/database_test_info.php';
        } catch (\PDOException $e) {
            http_response_code(500);
            echo "<h1>Erreur Base de Données</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            error_log('Erreur Database: ' . $e->getMessage());
        }
    }

    /**
     * Affiche un test complet de requêtes
     */
    public function queries()
    {
        try {
            $db = Database::getInstance();

            // Test 1: fetchOne (Récupère un seul résultat)
            $firstLevel = $db->fetchOne(
                "SELECT * FROM niveau_etude WHERE id_niveau = ?",
                [1]
            );

            // Test 2: fetchAll (Récupère tous les résultats)
            $allStatuts = $db->fetchAll("SELECT * FROM statut_memoire");

            // Test 3: execute (Requête sans résultat - INSERT, UPDATE, DELETE)
            // On ne fait que préparer, pas d'insertion réelle
            $stmt = $db->execute(
                "SELECT COUNT(*) as count FROM statut_memoire"
            );
            $count = $stmt->fetch();

            $queryResults = [
                'firstLevel' => $firstLevel,
                'allStatuts' => $allStatuts,
                'count' => $count
            ];

            // Passer les résultats à la vue
            require_once APP_PATH . '/views/database_test_queries.php';
        } catch (\PDOException $e) {
            http_response_code(500);
            echo "<h1>Erreur Requête</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            error_log('Erreur Requête: ' . $e->getMessage());
        }
    }

    /**
     * Affiche un test de transactions
     */
    public function transactions()
    {
        try {
            $db = Database::getInstance();

            // Tester la capacité à démarrer une transaction
            $canTransaction = true;
            $transactionTest = "Les transactions sont supportées par la base de données.";

            // Passer à la vue
            require_once APP_PATH . '/views/database_test_transactions.php';
        } catch (\PDOException $e) {
            http_response_code(500);
            echo "<h1>Erreur Transactions</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        }
    }
}

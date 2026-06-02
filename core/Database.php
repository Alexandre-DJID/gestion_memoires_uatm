<?php
/**
 * ============================================================================
 * Classe Database - Singleton PDO
 * ============================================================================
 * 
 * Cette classe implémente le pattern Singleton pour gérer une connexion unique
 * à la base de données PDO. Elle garantit qu'une seule instance de connexion
 * existe tout au long du cycle de vie de l'application.
 * 
 * Utilisation:
 *   $db = Database::getInstance();
 *   $pdo = $db->getConnection();
 *   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
 *   $stmt->execute([1]);
 *   $result = $stmt->fetch();
 */

class Database
{
    /**
     * Instance unique de la classe (Singleton)
     * 
     * @var Database|null
     */
    private static $instance = null;

    /**
     * Connexion PDO à la base de données
     * 
     * @var \PDO
     */
    private $connection = null;

    /**
     * Constructeur privé pour éviter l'instanciation directe
     * Initialise la connexion à la base de données
     * 
     * @throws \PDOException En cas d'erreur de connexion
     */
    private function __construct()
    {
        // Charger la configuration
        require_once dirname(__DIR__) . '/config/database.php';

        // Construire le DSN (Data Source Name) pour MySQL
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        try {
            /**
             * Créer la connexion PDO avec les attributs de sécurité requis
             * 
             * Attributs configurés:
             * - ATTR_ERRMODE: Lever une exception en cas d'erreur SQL
             * - ATTR_DEFAULT_FETCH_MODE: Récupérer en tableaux associatifs
             * - ATTR_EMULATE_PREPARES: Utiliser les préparés natifs du serveur
             */
            $this->connection = new \PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    // Lever une exception en cas d'erreur SQL
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    // Retourner les résultats sous forme de tableau associatif
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    // Désactiver l'émulation des requêtes préparées
                    // Utilise les vrais préparés du serveur (plus sécurisé)
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Log de réussite de connexion (optionnel, en développement)
            if (APP_DEBUG) {
                error_log('✓ Connexion à la base de données établie avec succès');
            }
        } catch (\PDOException $e) {
            // Log de l'erreur
            error_log('✗ Erreur de connexion à la base de données: ' . $e->getMessage());
            
            // En mode développement, afficher l'erreur
            if (APP_DEBUG) {
                throw new \PDOException(
                    'Impossible de se connecter à la base de données: ' . $e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            } else {
                // En production, afficher un message générique et quitter
                die('Erreur de base de données. Veuillez réessayer plus tard.');
            }
        }
    }

    /**
     * Empêcher le clonage de l'instance (Singleton)
     * 
     * @return void
     * @throws Exception
     */
    private function __clone()
    {
        throw new \Exception('Clonage de Database non autorisé');
    }

    /**
     * Empêcher la désérialisation de l'instance (Singleton)
     * 
     * @return void
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new \Exception('Désérialisation de Database non autorisée');
    }

    /**
     * Récupère l'instance unique de la classe Database (Singleton)
     * Crée l'instance à la première utilisation
     * 
     * @return Database Instance unique
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retourne la connexion PDO
     * Permet d'accéder à l'objet PDO pour exécuter des requêtes
     * 
     * @return \PDO Objet connexion PDO configuré
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Exécute une requête préparée directement
     * Raccourci utile pour les opérations simples
     * 
     * @param string $sql Requête SQL avec placeholders (?)
     * @param array $params Paramètres à lier
     * @return \PDOStatement Statement préparé et exécuté
     * @throws \PDOException En cas d'erreur SQL
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Exécute une requête et récupère un seul résultat
     * Utile pour les requêtes SELECT qui retournent une seule ligne
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return array|null Résultat ou null
     * @throws \PDOException En cas d'erreur SQL
     */
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Exécute une requête et récupère tous les résultats
     * Utile pour les requêtes SELECT multiple
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return array Tableau de résultats
     * @throws \PDOException En cas d'erreur SQL
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Commence une transaction
     * 
     * @return bool True si la transaction a démarré
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Valide une transaction
     * 
     * @return bool True si la transaction a été validée
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Annule une transaction
     * 
     * @return bool True si la transaction a été annulée
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }

    /**
     * Récupère l'ID du dernier enregistrement inséré
     * 
     * @return string ID du dernier INSERT
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
}

<?php
/**
 * ============================================================================
 * Configuration de la Base de Données
 * ============================================================================
 * 
 * Ce fichier centralise tous les paramètres de connexion à la base de données.
 * Utilisation exclusive de PDO pour les requêtes préparées (Sécurité XSS/Injection SQL).
 */

// Paramètres de connexion
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_memoires_uatm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// Options PDO pour une meilleure sécurité
define('PDO_OPTIONS', [
    // Lever une exception en cas d'erreur
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    // Retourner les résultats sous forme de tableau associatif
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    // Empêcher l'accès aux attributs du statement
    \PDO::ATTR_EMULATE_PREPARES => false,
]);

/**
 * Fonction de connexion à la base de données
 * Crée une nouvelle instance PDO avec les paramètres définis
 * 
 * @return \PDO Instance de connexion à la base de données
 * @throws \PDOException En cas d'erreur de connexion
 */
function connectDatabase() {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );
    
    try {
        $pdo = new \PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        return $pdo;
    } catch (\PDOException $e) {
        // En production, logger l'erreur au lieu de l'afficher
        error_log('Erreur de connexion à la base de données: ' . $e->getMessage());
        die('Erreur de connexion à la base de données. Veuillez réessayer.');
    }
}

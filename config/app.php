<?php
/**
 * ============================================================================
 * Configuration Générale de l'Application
 * ============================================================================
 * 
 * Ce fichier contient les paramètres globaux et les constantes de l'application.
 */

// Mode développement (debug)
define('APP_DEBUG', true);

// Nom et version de l'application
define('APP_NAME', 'Plateforme de Gestion des Mémoires UATM');
define('APP_VERSION', '1.0.0');

// URL de base de l'application
define('APP_URL', 'http://localhost/gestion_memoires_uatm');

// Dossier de stockage des fichiers uploadés
define('UPLOAD_DIR', dirname(__DIR__) . '/public/uploads');
define('UPLOAD_PATH', '/gestion_memoires_uatm/public/uploads');

// Limite de taille des fichiers (en bytes) - 50 MB
define('MAX_FILE_SIZE', 50 * 1024 * 1024);

// Types de fichiers autorisés
define('ALLOWED_FILE_TYPES', [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'txt' => 'text/plain',
]);

// Fuseau horaire
date_default_timezone_set('Africa/Algiers');

// Paramètres de session
define('SESSION_TIMEOUT', 1800); // 30 minutes en secondes
define('SESSION_NAME', 'gestion_memoires_session');

// Messages d'erreur personnalisés
define('ERROR_MESSAGES', [
    'route_not_found' => 'Page non trouvée (404)',
    'unauthorized' => 'Accès non autorisé (401)',
    'forbidden' => 'Accès interdit (403)',
    'server_error' => 'Erreur serveur interne (500)',
    'database_error' => 'Erreur de base de données',
    'validation_error' => 'Erreur de validation',
]);

<?php
/**
 * ============================================================================
 * Front Controller - Point d'Entrée Unique de l'Application
 * ============================================================================
 * 
 * Ce fichier est le seul point d'entrée pour TOUTES les requêtes web.
 * Il initialise l'environnement, charge la configuration, et délègue le routage.
 * 
 * Flux:
 *   1. Définir les chemins de base
 *   2. Charger la configuration
 *   3. Démarrer la session
 *   4. Initialiser le routeur
 *   5. Dispatcher les routes
 *   6. Gérer les erreurs globales
 */

// ============================================================================
// 1. DÉFINITION DES CHEMINS
// ============================================================================

define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('APP_PATH', BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
define('PUBLIC_PATH', __DIR__);

// ============================================================================
// 2. CHARGEMENT DE LA CONFIGURATION
// ============================================================================

// Charger les fichiers de configuration
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';

// ============================================================================
// 2B. CHARGEMENT DES CLASSES CŒUR
// ============================================================================

// Charger la classe Database (Singleton PDO)
require_once CORE_PATH . '/Database.php';

// ============================================================================
// 3. DÉMARRAGE DE LA SESSION
// ============================================================================

session_name(SESSION_NAME);
session_start();

// Vérifier le timeout de session
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        // Session expirée, détruire la session
        session_destroy();
        header('Location: ' . APP_URL);
        exit();
    }
}
// Mettre à jour le timestamp d'activité
$_SESSION['last_activity'] = time();

// ============================================================================
// 4. GESTION GLOBALE DES ERREURS
// ============================================================================

// Configuration du gestionnaire d'erreurs
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (APP_DEBUG) {
        echo "<h2>Erreur PHP</h2>";
        echo "<p><strong>Type:</strong> $errno</p>";
        echo "<p><strong>Message:</strong> $errstr</p>";
        echo "<p><strong>Fichier:</strong> $errfile (ligne $errline)</p>";
    }
    error_log("Erreur PHP: [$errno] $errstr in $errfile:$errline");
});

// Configuration du gestionnaire d'exceptions
set_exception_handler(function (Throwable $exception) {
    http_response_code(500);
    if (APP_DEBUG) {
        echo "<h2>Exception non gérée</h2>";
        echo "<p><strong>Classe:</strong> " . get_class($exception) . "</p>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><strong>Fichier:</strong> " . htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8') . " (ligne {$exception->getLine()})</p>";
        echo "<h3>Stack Trace:</h3>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . "</pre>";
    }
    error_log('Exception non gérée: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
});

// Capturer les erreurs fatales
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        if (APP_DEBUG) {
            echo "<h2>Erreur Fatale</h2>";
            echo "<p><strong>Type:</strong> {$error['type']}</p>";
            echo "<p><strong>Message:</strong> {$error['message']}</p>";
            echo "<p><strong>Fichier:</strong> {$error['file']} (ligne {$error['line']})</p>";
        }
        error_log('Erreur fatale: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
    }
});

// ============================================================================
// 5. CHARGEMENT DU ROUTEUR
// ============================================================================

try {
    // Charger la classe Router
    require_once CORE_PATH . '/Router.php';

    // Instancier le routeur
    $router = new Router();

    // ========================================================================
    // 6. ENREGISTREMENT DES ROUTES (À REMPLIR SELON LES BESOINS)
    // ========================================================================
    
    // Routes d'authentification
    $router->get('/', 'AuthController@showLoginForm');
    $router->get('/login', 'AuthController@showLoginForm');
    $router->post('/login', 'AuthController@processLogin');
    $router->get('/logout', 'AuthController@logout');
    
    // Routes du tableau de bord
    $router->get('/dashboard', 'DashboardController@index');

    // Routes profil utilisateur
    $router->get('/profil', 'ProfilController@index');
    $router->post('/profil/update-info', 'ProfilController@updateInfo');
    $router->post('/profil/update-password', 'ProfilController@updatePassword');
    
    // Routes des mémoires
    $router->get('/memoires', 'MemoireController@index');
    $router->get('/memoires/creer', 'MemoireController@create');
    $router->post('/memoires/creer', 'MemoireController@store');
    $router->get('/memoires/telecharger/:id', 'MemoireController@download');
    $router->post('/memoires/:id/statut', 'MemoireController@updateStatus');
    $router->post('/memoires/:id/assigner-jury', 'MemoireController@assignJury');
    $router->post('/memoires/:id/commenter', 'MemoireController@posterCommentaire');
    $router->post('/memoires/:id/supprimer', 'MemoireController@delete');
    $router->get('/mes-depots', 'MemoireController@mesDepots');
    $router->get('/mes-evaluations', 'MemoireController@mesEvaluations');
    $router->get('/memoires/:id/like', 'MemoireController@like');
    $router->get('/memoires/:id', 'MemoireController@show');
    
    // Routes de test
    $router->get('/test', 'HomeController@test');
    
    // Routes à implémenter
    // $router->get('/memoires', 'MemoireController@lister');
    // $router->get('/memoires/:id', 'MemoireController@afficher');
    // $router->post('/memoires/creer', 'MemoireController@creer');
    // $router->get('/inscription', 'InscriptionController@formulaire');
    // $router->post('/inscription/enregistrer', 'InscriptionController@enregistrer');

    // ========================================================================
    // 7. DISPATCH DES ROUTES
    // ========================================================================

    $router->dispatch();

} catch (Exception $e) {
    http_response_code(500);
    error_log('Erreur critique du routeur: ' . $e->getMessage());
    
    if (APP_DEBUG) {
        echo "<h1>Erreur Critique</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    } else {
        echo "<h1>Erreur Serveur</h1>";
        echo "<p>Une erreur interne s'est produite. Veuillez réessayer.</p>";
    }
}

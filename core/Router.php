<?php
/**
 * ============================================================================
 * Classe Router - Moteur de Routage
 * ============================================================================
 * 
 * Cette classe gère le routage des requêtes HTTP vers les bons contrôleurs et méthodes.
 * Architecture simple et robuste sans dépendances externes.
 * 
 * Utilisation:
 *   $router = new Router();
 *   $router->get('/memoires', 'MemoireController@afficher');
 *   $router->post('/memoires/creer', 'MemoireController@creer');
 *   $router->dispatch();
 */

class Router
{
    /**
     * Tableau des routes GET
     * Format: ['pattern' => ['controller' => 'ClassName', 'method' => 'methodName']]
     * 
     * @var array
     */
    private $routesGet = [];

    /**
     * Tableau des routes POST
     * 
     * @var array
     */
    private $routesPost = [];

    /**
     * URL actuelle de la requête
     * 
     * @var string
     */
    private $currentUrl = '';

    /**
     * Méthode HTTP actuelle (GET ou POST)
     * 
     * @var string
     */
    private $currentMethod = '';

    /**
     * Constructeur du Router
     * Initialise la méthode HTTP et l'URL actuelle
     */
    public function __construct()
    {
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
        $this->parseUrl();
    }

    /**
     * Parse l'URL depuis REQUEST_URI (Méthode robuste pour Vanilla PHP)
     * Gère automatiquement les sous-dossiers locaux comme XAMPP
     * @return void
     */
    private function parseUrl()
    {
        // 1. Récupérer l'URL brute exacte (ex: /gestion_memoires_uatm/public/login)
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // 2. Définir le chemin de base de ton projet
        $basePath = '/gestion_memoires_uatm/public';
        
        // 3. Découper proprement l'URL pour isoler la route
        if (strpos($url, $basePath) === 0) {
            $url = substr($url, strlen($basePath));
        }
        
        // 4. Nettoyer les slashes et décoder
        $this->currentUrl = trim($url, '/');
        $this->currentUrl = urldecode($this->currentUrl);
    }

    /**
     * Enregistre une route GET
     * 
     * @param string $pattern Pattern de la route (ex: /memoires ou /memoires/:id)
     * @param string $action Contrôleur et méthode (ex: MemoireController@lister)
     * @return void
     */
    public function get($pattern, $action)
    {
        $this->registerRoute('GET', $pattern, $action);
    }

    /**
     * Enregistre une route POST
     * 
     * @param string $pattern Pattern de la route
     * @param string $action Contrôleur et méthode
     * @return void
     */
    public function post($pattern, $action)
    {
        $this->registerRoute('POST', $pattern, $action);
    }

    /**
     * Enregistre une route pour les deux méthodes GET et POST
     * 
     * @param string $pattern Pattern de la route
     * @param string $action Contrôleur et méthode
     * @return void
     */
    public function any($pattern, $action)
    {
        $this->registerRoute('GET', $pattern, $action);
        $this->registerRoute('POST', $pattern, $action);
    }

    /**
     * Enregistre une route (méthode interne)
     * 
     * @param string $method Méthode HTTP (GET ou POST)
     * @param string $pattern Pattern de la route
     * @param string $action Contrôleur@méthode
     * @return void
     */
    private function registerRoute($method, $pattern, $action)
    {
        // Nettoyer le pattern
        $pattern = trim($pattern, '/');

        if ($method === 'GET') {
            $this->routesGet[$pattern] = $this->parseAction($action);
        } elseif ($method === 'POST') {
            $this->routesPost[$pattern] = $this->parseAction($action);
        }
    }

    /**
     * Parse une action au format "ControllerName@methodName"
     * 
     * @param string $action Chaîne d'action
     * @return array Tableau avec les clés 'controller' et 'method'
     * @throws Exception Si le format est invalide
     */
    private function parseAction($action)
    {
        if (strpos($action, '@') === false) {
            throw new Exception("Format d'action invalide: '$action'. Utilisez 'ControllerName@methodName'");
        }

        [$controller, $method] = explode('@', $action);

        return [
            'controller' => trim($controller),
            'method' => trim($method)
        ];
    }

    /**
     * Lance le routage et exécute le contrôleur approprié
     * Gère aussi les erreurs 404
     * 
     * @return void
     */
    public function dispatch()
    {
        // Sélectionner le tableau de routes selon la méthode HTTP
        $routes = ($this->currentMethod === 'GET') ? $this->routesGet : $this->routesPost;

        // Chercher la route correspondante
        $matchedRoute = $this->matchRoute($routes);

        if ($matchedRoute === null) {
            // Aucune route trouvée : erreur 404
            $this->handle404();
            return;
        }

        // Extraire le contrôleur et la méthode
        $controllerName = $matchedRoute['action']['controller'];
        $methodName = $matchedRoute['action']['method'];
        $params = $matchedRoute['params'];

        // Vérifier que le fichier du contrôleur existe
        $controllerFile = dirname(__DIR__) . "/app/controllers/{$controllerName}.php";

        if (!file_exists($controllerFile)) {
            error_log("Fichier contrôleur introuvable: $controllerFile");
            $this->handle500('Contrôleur introuvable');
            return;
        }

        // Inclure le fichier du contrôleur
        require_once $controllerFile;

        // Vérifier que la classe existe
        if (!class_exists($controllerName)) {
            error_log("Classe contrôleur introuvable: $controllerName");
            $this->handle500('Classe contrôleur introuvable');
            return;
        }

        // Instancier le contrôleur et appeler la méthode
        try {
            $controller = new $controllerName();

            if (!method_exists($controller, $methodName)) {
                error_log("Méthode introuvable: $controllerName::$methodName");
                $this->handle500('Méthode introuvable');
                return;
            }

            // Appeler la méthode avec les paramètres extraits de l'URL
            call_user_func_array([$controller, $methodName], $params);
        } catch (Exception $e) {
            error_log('Erreur lors de l\'exécution du contrôleur: ' . $e->getMessage());
            $this->handle500($e->getMessage());
        }
    }

    /**
     * Cherche une route correspondant à l'URL actuelle
     * Supporte les paramètres dynamiques (ex: :id)
     * 
     * @param array $routes Tableau des routes à vérifier
     * @return array|null Tableau avec 'action' et 'params', ou null si aucune correspondance
     */
    private function matchRoute($routes)
    {
        foreach ($routes as $pattern => $action) {
            // Vérification exacte (pas de paramètres)
            if ($pattern === $this->currentUrl) {
                return [
                    'action' => $action,
                    'params' => []
                ];
            }

            // Vérification avec paramètres (pattern regex simple)
            if (strpos($pattern, ':') !== false) {
                $regex = $this->patternToRegex($pattern);
                if (preg_match($regex, $this->currentUrl, $matches)) {
                    // Extraire les paramètres nommés
                    $params = array_slice($matches, 1);
                    return [
                        'action' => $action,
                        'params' => $params
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Convertit un pattern avec paramètres en regex
     * Exemple: /memoires/:id => /^memoires\/(\d+)$/
     * 
     * @param string $pattern Pattern avec paramètres
     * @return string Regex correspondante
     */
    private function patternToRegex($pattern)
    {
        // Remplacer :param par une capture regex
        $regex = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', '([a-zA-Z0-9\-_]+)', $pattern);
        // On utilise # comme délimiteur au lieu de / pour éviter les conflits avec les slashes des URL
        return '#^' . $regex . '$#';
    }

    /**
     * Gère les erreurs 404 (Page non trouvée)
     * 
     * @return void
     */
    private function handle404()
    {
        http_response_code(404);
        
        if (APP_DEBUG) {
            echo "<h1>Erreur 404 - Page non trouvée</h1>";
            echo "<p>L'URL demandée '<code>{$this->currentUrl}</code>' n'existe pas.</p>";
            echo "<p>Méthode HTTP: <code>{$this->currentMethod}</code></p>";
        } else {
            // En production, afficher une page d'erreur générique
            echo "<h1>Page non trouvée</h1>";
            echo "<p>La page demandée n'existe pas.</p>";
        }

        error_log("Route introuvable: {$this->currentMethod} /{$this->currentUrl}");
    }

    /**
     * Gère les erreurs 500 (Erreur serveur)
     * 
     * @param string $message Message d'erreur
     * @return void
     */
    private function handle500($message = '')
    {
        http_response_code(500);

        if (APP_DEBUG) {
            echo "<h1>Erreur 500 - Erreur serveur interne</h1>";
            if (!empty($message)) {
                echo "<p><strong>Message:</strong> <code>$message</code></p>";
            }
        } else {
            echo "<h1>Erreur serveur</h1>";
            echo "<p>Une erreur interne s'est produite. Veuillez réessayer.</p>";
        }

        error_log("Erreur serveur: $message");
    }
}

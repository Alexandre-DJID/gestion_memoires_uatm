<?php
/**
 * ============================================================================
 * Contrôleur de Bienvenue - Test de Routage
 * ============================================================================
 * 
 * Contrôleur simple pour vérifier que l'infrastructure de routage fonctionne.
 * À supprimer ou modifier une fois que les vrais contrôleurs sont en place.
 */

class HomeController
{
    /**
     * Page d'accueil
     */
    public function index()
    {
        require_once APP_PATH . '/views/home_index.php';
    }

    /**
     * Page de test du routeur
     */
    public function test()
    {
        $routerWorks = true;
        $message = "Routeur fonctionnel ! Le système est prêt pour le développement.";
        require_once APP_PATH . '/views/test_router.php';
    }
}

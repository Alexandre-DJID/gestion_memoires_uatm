<?php
/**
 * ============================================================================
 * Contrôleur du Tableau de Bord
 * ============================================================================
 * 
 * Gère l'affichage du tableau de bord utilisateur.
 * Requiert une authentification valide.
 */

class DashboardController
{
    /**
     * Affiche le tableau de bord
     * 
     * SÉCURITÉ : Vérifie que l'utilisateur est authentifié avant d'afficher.
     * 
     * @return void
     */
    public function index()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
            header('Location: /gestion_memoires_uatm/public/login');
            exit();
        }

        $user_id = (int) $_SESSION['user_id'];
        $user_type = $_SESSION['user_type'] ?? 'etudiant';

        require_once APP_PATH . '/models/Memoire.php';

        if ($user_type === 'de') {
            $stats = Memoire::getGlobalStats();
            $recents = Memoire::getRecentMemoires(5);
            require_once APP_PATH . '/views/dashboard/de.php';
            return;
        }

        if ($user_type === 'professeur') {
            $memoires = Memoire::getMemoiresByProf($user_id);
            $stats = Memoire::getStatistics($user_id, 'professeur');
            require_once APP_PATH . '/views/dashboard/professeur.php';
            return;
        }

        $memoires = Memoire::getByAuteur($user_id);
        $stats = Memoire::getStatistics($user_id, 'etudiant');
        require_once APP_PATH . '/views/dashboard/etudiant.php';
    }
}

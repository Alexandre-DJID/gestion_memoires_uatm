<?php
/**
 * ============================================================================
 * Contrôleur d'Authentification
 * ============================================================================
 * 
 * Gère l'authentification des utilisateurs (connexion, déconnexion, etc.)
 */

class AuthController
{
    /**
     * Affiche le formulaire de connexion
     * 
     * @return void
     */
    public function showLoginForm()
    {
        $error = null;
        
        // Vérifier s'il y a un message d'erreur en session
        if (isset($_SESSION['auth_error'])) {
            $error = $_SESSION['auth_error'];
            unset($_SESSION['auth_error']);
        }
        
        require_once APP_PATH . '/views/auth/login.php';
    }

    /**
     * Traite la soumission du formulaire de connexion
     * 
     * @return void
     */
    public function processLogin()
    {
        // Vérifier que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        // Récupérer et nettoyer les données
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Valider les entrées
        if (empty($email) || empty($password)) {
            $_SESSION['auth_error'] = 'Email et mot de passe sont obligatoires.';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        // Valider le format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = 'Format d\'email invalide.';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        // Charger le modèle Utilisateur
        require_once APP_PATH . '/models/Utilisateur.php';

        // Rechercher l'utilisateur par email
        $utilisateur = Utilisateur::findByEmail($email);

        // Vérifier que l'utilisateur existe
        if ($utilisateur === false) {
            $_SESSION['auth_error'] = 'Identifiants incorrects.';
            error_log('Tentative de connexion avec un email non trouvé: ' . $email);
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $utilisateur['mot_de_passe'])) {
            $_SESSION['auth_error'] = 'Identifiants incorrects.';
            error_log('Tentative de connexion avec un mot de passe incorrect pour: ' . $email);
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        // Succès : initialiser la session
        $_SESSION['user_id'] = $utilisateur['id_user'];
        $_SESSION['user_email'] = $utilisateur['email'];
        $_SESSION['user_type'] = $utilisateur['type_utilisateur'];
        $_SESSION['user_nom'] = $utilisateur['nom'];
        $_SESSION['user_prenom'] = $utilisateur['prenom'];
        $_SESSION['authenticated'] = true;

        // Logger la connexion réussie
        error_log('Connexion réussie pour l\'utilisateur: ' . $email);

        // Rediriger vers le tableau de bord
        header('Location: ' . BASE_URL . '/dashboard');
        exit();
    }

    /**
     * Déconnecte l'utilisateur et détruit la session
     * 
     * @return void
     */
    public function logout()
    {
        // Détruire toutes les données de session
        $_SESSION = [];
        
        // Détruire le cookie de session
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Détruire la session elle-même
        session_destroy();
        
        // Logger la déconnexion
        error_log('Déconnexion utilisateur');
        
        // Rediriger vers la page de connexion
        header('Location: ' . BASE_URL . '/login');
        exit();
    }
}

<?php
/**
 * ============================================================================
 * Contrôleur Profil Utilisateur
 * ============================================================================
 *
 * Gestion des informations personnelles et du mot de passe.
 */

class ProfilController
{
    /**
     * Affiche la page Mon Profil
     *
     * @return void
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Utilisateur.php';

        $utilisateur = Utilisateur::getUserById((int) $_SESSION['user_id']);
        if ($utilisateur === false) {
            $_SESSION['flash_error'] = 'Utilisateur introuvable.';
            header('Location: ' . BASE_URL . '/dashboard');
            exit();
        }

        require_once APP_PATH . '/views/profil/index.php';
    }

    /**
     * Met à jour le nom et prénom
     *
     * @return void
     */
    public function updateInfo()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Utilisateur.php';

        $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
        $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';

        if ($nom === '' || $prenom === '') {
            $_SESSION['flash_error'] = 'Le nom et le prénom sont obligatoires.';
            header('Location: ' . BASE_URL . '/profil');
            exit();
        }

        $id_user = (int) $_SESSION['user_id'];

        if (Utilisateur::updateInfo($id_user, $nom, $prenom)) {
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_prenom'] = $prenom;
            $_SESSION['flash_success'] = 'Informations personnelles mises à jour avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour de vos informations.';
        }

        header('Location: ' . BASE_URL . '/profil');
        exit();
    }

    /**
     * Met à jour le mot de passe
     *
     * @return void
     */
    public function updatePassword()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Utilisateur.php';

        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $id_user = (int) $_SESSION['user_id'];
        $utilisateur = Utilisateur::getUserById($id_user);

        if ($utilisateur === false) {
            $_SESSION['flash_error'] = 'Utilisateur introuvable.';
            header('Location: ' . BASE_URL . '/profil');
            exit();
        }

        if (!password_verify($old_password, $utilisateur['mot_de_passe'])) {
            $_SESSION['flash_error'] = 'L\'ancien mot de passe est incorrect.';
            header('Location: ' . BASE_URL . '/profil');
            exit();
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['flash_error'] = 'Les nouveaux mots de passe ne correspondent pas.';
            header('Location: ' . BASE_URL . '/profil');
            exit();
        }

        if (strlen($new_password) < 6) {
            $_SESSION['flash_error'] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
            header('Location: ' . BASE_URL . '/profil');
            exit();
        }

        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);

        if (Utilisateur::updatePassword($id_user, $new_hash)) {
            $_SESSION['flash_success'] = 'Mot de passe modifié avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la modification du mot de passe.';
        }

        header('Location: ' . BASE_URL . '/profil');
        exit();
    }
}

<?php

class UserController
{
    /**
     * Vérifie que l'utilisateur est direction des études.
     */
    private function ensureDirection(): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'de') {
            $_SESSION['flash_error'] = 'Accès réservé à la direction des études.';
            header('Location: ' . BASE_URL . '/memoires');
            exit();
        }
    }

    /**
     * Affiche le formulaire de création d'utilisateur.
     */
    public function create()
    {
        $this->ensureDirection();

        require_once APP_PATH . '/models/Admin.php';
        require_once APP_PATH . '/models/Inscription.php';

        $filieres = Admin::getFilieres();
        $centres = Admin::getCentres();
        $niveaux = Inscription::getNiveaux();
        $annees = Inscription::getAnnees();

        require_once APP_PATH . '/views/admin/utilisateurs/create.php';
    }

    /**
     * Traite l'ajout d'un nouvel utilisateur.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/utilisateurs/create');
            exit();
        }

        $this->ensureDirection();

        $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
        $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $mot_de_passe = isset($_POST['mot_de_passe']) ? trim($_POST['mot_de_passe']) : '';
        $type_utilisateur = isset($_POST['type_utilisateur']) ? trim($_POST['type_utilisateur']) : '';

        if ($nom === '' || $prenom === '' || $email === '' || $mot_de_passe === '' || $type_utilisateur === '') {
            $_SESSION['flash_error'] = 'Tous les champs obligatoires doivent être remplis.';
            header('Location: ' . BASE_URL . '/admin/utilisateurs/create');
            exit();
        }

        if (!in_array($type_utilisateur, ['etudiant', 'professeur', 'de'], true)) {
            $_SESSION['flash_error'] = 'Type d\'utilisateur invalide.';
            header('Location: ' . BASE_URL . '/admin/utilisateurs/create');
            exit();
        }

        require_once APP_PATH . '/models/Utilisateur.php';
        require_once APP_PATH . '/models/Inscription.php';

        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
        $extra_data = [];

        if ($type_utilisateur === 'etudiant') {
            $matricule = isset($_POST['matricule']) ? trim($_POST['matricule']) : '';
            if ($matricule === '') {
                $_SESSION['flash_error'] = 'Le matricule de l\'étudiant est obligatoire.';
                header('Location: ' . BASE_URL . '/admin/utilisateurs/create');
                exit();
            }
            $extra_data['matricule'] = $matricule;
        } elseif ($type_utilisateur === 'professeur') {
            $grade = isset($_POST['grade']) ? trim($_POST['grade']) : 'Maître-assistant';
            $extra_data['grade'] = $grade;
        }

        $id_user = Utilisateur::create($nom, $prenom, $email, $mot_de_passe_hash, $type_utilisateur, $extra_data);

        if ($id_user === false) {
            $_SESSION['flash_error'] = 'Impossible de créer l\'utilisateur. L\'email existe peut-être déjà.';
            header('Location: ' . BASE_URL . '/admin/utilisateurs/create');
            exit();
        }

        // Si étudiant, créer aussi l'inscription
        if ($type_utilisateur === 'etudiant') {
            $id_filiere = isset($_POST['id_filiere']) ? (int) $_POST['id_filiere'] : 0;
            $id_centre = isset($_POST['id_centre']) ? (int) $_POST['id_centre'] : 0;
            $id_annee = isset($_POST['id_annee']) ? (int) $_POST['id_annee'] : 0;
            $id_niveau = isset($_POST['id_niveau']) ? (int) $_POST['id_niveau'] : 0;

            if ($id_filiere === 0 || $id_centre === 0 || $id_annee === 0 || $id_niveau === 0) {
                $_SESSION['flash_error'] = 'Tous les champs d\'inscription sont obligatoires pour un étudiant.';
                header('Location: ' . BASE_URL . '/admin/utilisateurs/create');
                exit();
            }

            if (!Inscription::create($id_user, $id_filiere, $id_centre, $id_annee, $id_niveau)) {
                $_SESSION['flash_error'] = 'Erreur lors de la création de l\'inscription.';
                header('Location: ' . BASE_URL . '/admin/utilisateurs/create');
                exit();
            }
        }

        $_SESSION['flash_success'] = 'Utilisateur créé avec succès.';
        header('Location: ' . BASE_URL . '/admin/parametres');
        exit();
    }
}

<?php
require_once APP_PATH . '/models/Admin.php';

class AdminController
{
    /**
     * Vérifie que l'utilisateur est direction des études.
     *
     * @return void
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
     * Affiche les paramètres de la direction des études.
     *
     * @return void
     */
    public function parametres()
    {
        $this->ensureDirection();

        
        $filieres = Admin::getFilieres();

        require_once APP_PATH . '/views/admin/parametres.php';
    }

    /**
     * Ajoute une nouvelle filière.
     *
     * @return void
     */
    public function addFiliere()
    {
        $this->ensureDirection();

        $nom = isset($_POST['libelle']) ? trim($_POST['libelle']) : '';
        if ($nom === '') {
            $_SESSION['flash_error'] = 'Le nom de la filière ne peut pas être vide.';
            header('Location: ' . BASE_URL . '/admin/parametres');
            exit();
        }

        if (Admin::addFiliere($nom)) {
            $_SESSION['flash_success'] = 'Filière ajoutée avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Impossible d\'ajouter la filière. Vérifiez qu\'elle n\'existe pas déjà.';
        }

        header('Location: ' . BASE_URL . '/admin/parametres');
        exit();
    }

    /**
     * Supprime une filière.
     *
     * @param int $id
     * @return void
     */
    public function deleteFiliere($id)
    {
        $this->ensureDirection();

        require_once APP_PATH . '/models/Admin.php';
        if (Admin::deleteFiliere((int) $id)) {
            $_SESSION['flash_success'] = 'Filière supprimée avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Impossible de supprimer la filière.';
        }

        header('Location: ' . BASE_URL . '/admin/parametres');
        exit();
    }

    /**
     * Affiche le formulaire d'import en masse de mémoires.
     */
    public function importMemoires()
    {
        $this->ensureDirection();
        require_once APP_PATH . '/views/admin/memoires/import.php';
    }

    /**
     * Traite l'import en masse de mémoires.
     */
    public function importMemoiresProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/memoires/import');
            exit();
        }

        $this->ensureDirection();

        if (!isset($_FILES['memoires']) || empty($_FILES['memoires']['name'][0])) {
            $_SESSION['flash_error'] = 'Veuillez sélectionner au moins un fichier.';
            header('Location: ' . BASE_URL . '/admin/memoires/import');
            exit();
        }

        $id_statut = isset($_POST['id_statut']) ? (int) $_POST['id_statut'] : 3;
        $upload_dir = PUBLIC_PATH . '/uploads/memoires';

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['flash_error'] = 'Erreur de création du dossier de destination.';
                header('Location: ' . BASE_URL . '/admin/memoires/import');
                exit();
            }
        }

        require_once APP_PATH . '/models/Memoire.php';
        $pdo = Database::getInstance()->getConnection();

        $count_success = 0;
        $count_error = 0;
        $errors = [];

        foreach ($_FILES['memoires']['name'] as $key => $filename) {
            $tmp_name = $_FILES['memoires']['tmp_name'][$key];
            $error = $_FILES['memoires']['error'][$key];

            if ($error !== UPLOAD_ERR_OK) {
                $count_error++;
                continue;
            }

            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($extension !== 'pdf') {
                $count_error++;
                continue;
            }

            // Extraire le thème du nom de fichier selon la nomenclature : Nom_Prenom_Centre_Filiere_Theme.pdf
            $nom_fichier_sans_ext = pathinfo($filename, PATHINFO_FILENAME);
            $parts = explode('_', $nom_fichier_sans_ext, 5);

            if (count($parts) < 5) {
                // Le format n'est pas bon (moins de 5 segments)
                error_log("Format fichier invalide (< 5 segments): $filename");
                $count_error++;
                continue;
            }

            $nom_part = trim($parts[0]);
            $prenom_part = trim($parts[1]);
            $centre_part = trim($parts[2]);
            $filiere_part = trim($parts[3]);
            $theme = trim($parts[4]);

            if ($theme === '' || $nom_part === '') {
                $count_error++;
                continue;
            }

            // Générer un nom unique pour le fichier
            $nom_unique = uniqid('memoire_') . '.pdf';
            $chemin_destination = $upload_dir . '/' . $nom_unique;

            if (!move_uploaded_file($tmp_name, $chemin_destination)) {
                $count_error++;
                continue;
            }

            // Insérer le mémoire dans la BD
            $chemin_relatif = '/gestion_memoires_uatm/public/uploads/memoires/' . $nom_unique;
            $sql = 'INSERT INTO memoire (theme, resume, fichier_path, id_statut) 
                    VALUES (:theme, :resume, :fichier_path, :id_statut)';
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([
                ':theme' => $theme,
                ':resume' => 'Importation en masse - À compléter',
                ':fichier_path' => $chemin_relatif,
                ':id_statut' => $id_statut,
            ])) {
                $count_success++;
            } else {
                $count_error++;
                @unlink($chemin_destination);
            }
        }

        if ($count_success > 0) {
            $_SESSION['flash_success'] = "$count_success mémoire(s) importé(s) avec succès.";
        }
        if ($count_error > 0) {
            $_SESSION['flash_error'] = "$count_error fichier(s) n'a (n')pu être importé(s).";
        }

        header('Location: ' . BASE_URL . '/admin/parametres');
        exit();
    }
}


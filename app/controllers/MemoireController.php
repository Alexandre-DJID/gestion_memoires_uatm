<?php
/**
 * ============================================================================
 * Contrôleur des Mémoires
 * ============================================================================
 * 
 * Gère l'affichage et la gestion des mémoires.
 */

class MemoireController
{
    /**
     * Nettoie et normalise une chaîne pour nommer un fichier :
     * - Retrait des accents
     * - Remplacement des espaces par des tirets bas
     * - Minuscules
     * - Suppression des caractères spéciaux
     *
     * @param string $str
     * @return string
     */
    private function sanitizeString(string $str): string
    {
        // Convertir en minuscules
        $str = mb_strtolower($str, 'UTF-8');

        // Retrait des accents
        $str = preg_replace('/&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);/i', '$1', htmlentities($str, ENT_QUOTES, 'UTF-8'));
        $str = preg_replace('/&([A-z])+([A-z])+;/i', '', $str);

        // Remplacer les espaces par des tirets bas
        $str = preg_replace('/\s+/', '_', $str);

        // Supprimer les caractères spéciaux sauf tirets et tirets bas
        $str = preg_replace('/[^a-z0-9_-]/', '', $str);

        // Supprimer les tirets bas multiples
        $str = preg_replace('/_+/', '_', $str);

        // Supprimer les tirets bas en début/fin
        $str = trim($str, '_');

        return $str;
    }

    /**
     * Vérifie si l'utilisateur connecté est administrateur (Direction ou Professeur)
     *
     * @return bool
     */
    private function isAdmin(): bool
    {
        return in_array($_SESSION['user_type'] ?? '', ['de', 'professeur'], true);
    }

    /**
     * Matrice stricte : accès au fichier physique (téléchargement / PDF)
     * - DE : tous les mémoires
     * - Auteur (id_auteur) : son propre mémoire
     * - Professeur : mémoires assignés dans evaluer uniquement
     * - Public / étudiant lambda : aucun accès fichier
     *
     * @param array $memoire Ligne mémoire (id_auteur requis)
     * @param int $id ID du mémoire
     * @return bool
     */
    private function canAccessFile(array $memoire, int $id): bool
    {
        require_once APP_PATH . '/models/Memoire.php';

        $is_de = ($_SESSION['user_type'] ?? '') === 'de';
        $is_auteur = isset($_SESSION['user_id']) && (
            (isset($memoire['id_user']) && (int) $_SESSION['user_id'] === (int) $memoire['id_user']) ||
            (isset($memoire['id_auteur']) && (int) $_SESSION['user_id'] === (int) $memoire['id_auteur'])
        );
        $is_prof_assigne = ($_SESSION['user_type'] ?? '') === 'professeur'
            && Memoire::isProfAssigne($id, (int) $_SESSION['user_id']);

        return $is_de || $is_auteur || $is_prof_assigne;
    }

    /**
     * Vérifie si le mémoire est validé pour permettre une prévisualisation publique.
     *
     * @param array $memoire
     * @return bool
     */
    private function isMemoireValide(array $memoire): bool
    {
        if (isset($memoire['id_statut']) && (int) $memoire['id_statut'] === 3) {
            return true;
        }

        $statutLabel = '';
        if (!empty($memoire['statut_libelle'])) {
            $statutLabel = $memoire['statut_libelle'];
        } elseif (!empty($memoire['libelle'])) {
            $statutLabel = $memoire['libelle'];
        }

        if ($statutLabel !== '') {
            return mb_strtolower(trim($statutLabel), 'UTF-8') === 'validé';
        }

        if (isset($memoire['id_statut'])) {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('SELECT libelle FROM statut_memoire WHERE id_statut = :id LIMIT 1');
            $stmt->execute([':id' => (int) $memoire['id_statut']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row && mb_strtolower(trim($row['libelle']), 'UTF-8') === 'validé';
        }

        return false;
    }

    /**
     * Renvoie les droits de téléchargement et de prévisualisation pour un mémoire.
     *
     * @param array $memoire
     * @param int $id
     * @return array{can_download: bool, can_preview: bool}
     */
    private function getFileAccessRights(array $memoire, int $id): array
    {
        $can_download = false;

        if (isset($_SESSION['user_id'])) {
            if (($_SESSION['user_type'] ?? '') === 'de') {
                $can_download = true;
            }

            if (isset($memoire['id_user']) && (int) $_SESSION['user_id'] === (int) $memoire['id_user']) {
                $can_download = true;
            }

            if (isset($memoire['id_auteur']) && (int) $_SESSION['user_id'] === (int) $memoire['id_auteur']) {
                $can_download = true;
            }

            if (($_SESSION['user_type'] ?? '') === 'professeur' && Memoire::isProfAssigne($id, (int) $_SESSION['user_id'])) {
                $can_download = true;
            }
        }

        $can_preview = $can_download || $this->isMemoireValide($memoire);

        return [
            'can_download' => $can_download,
            'can_preview' => $can_preview,
        ];
    }

    /**
     * Refuse l'accès avec un message flash et redirection vers la liste
     *
     * @param string $message Message d'erreur
     * @return void
     */
    private function denyAccess(string $message): void
    {
        $_SESSION['flash_error'] = $message;
        header('Location: ' . BASE_URL . '/memoires');
        exit();
    }

    /**
     * Affiche la liste de tous les mémoires
     * 
     * SÉCURITÉ : Vérifie que l'utilisateur est authentifié.
     * 
     * @return void
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';

        $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
        $statut = isset($_GET['statut']) ? trim($_GET['statut']) : '';
        $filiere = isset($_GET['filiere']) ? trim($_GET['filiere']) : '';
        $annee = isset($_GET['annee']) ? trim($_GET['annee']) : '';
        $centre = isset($_GET['centre']) ? trim($_GET['centre']) : '';

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $limit = 10;
        $total_items = Memoire::countSearchResults($keyword, $statut, $filiere, $annee, $centre);
        $total_pages = $total_items > 0 ? (int) ceil($total_items / $limit) : 0;

        if ($total_pages > 0 && $page > $total_pages) {
            $page = $total_pages;
        }

        $offset = ($page - 1) * $limit;
        $memoires = Memoire::search($keyword, $statut, $filiere, $annee, $centre, $limit, $offset);
        $filtres_actifs = ($keyword !== '' || $statut !== '' || $filiere !== '' || $annee !== '' || $centre !== '');

        require_once APP_PATH . '/models/Admin.php';
        $filieres = Admin::getFilieres();
        $centres = Admin::getCentres();

        $pdo = Database::getInstance()->getConnection();
        $stmtStatuts = $pdo->query('SELECT * FROM statut_memoire ORDER BY id_statut');
        $liste_statuts = $stmtStatuts->fetchAll(\PDO::FETCH_ASSOC);

        $is_admin = $this->isAdmin();

        require_once APP_PATH . '/views/memoires/index.php';
    }
    
    /**
     * Affiche le détail d'un mémoire
     * 
     * @param int $id ID du mémoire
     * @return void
     */
    public function show($id)
    {
        require_once APP_PATH . '/models/Memoire.php';
        $memoire = Memoire::getById($id);

        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        $fileRights = $this->getFileAccessRights($memoire, (int) $id);
        $can_download = $fileRights['can_download'];
        $can_preview = $fileRights['can_preview'];
        $is_admin = $this->isAdmin();

        $pdo = Database::getInstance()->getConnection();

        // Composition du jury actuel (Directeur, Co-Directeur, Examinateur…)
        $sqlJury = 'SELECT u.id_user, u.nom, u.prenom, r.libelle AS role
                    FROM evaluer e
                    JOIN utilisateur u ON e.id_user_prof = u.id_user
                    JOIN role_jury r ON e.id_role = r.id_role
                    WHERE e.id_memoire = :id
                    ORDER BY e.id_role, u.nom, u.prenom';
        $stmtJury = $pdo->prepare($sqlJury);
        $stmtJury->execute([':id' => $id]);
        $jury_actuel = $stmtJury->fetchAll(\PDO::FETCH_ASSOC);

        // Données pour le formulaire d'assignation (Direction uniquement)
        $professeurs = [];
        $roles_jury = [];
        if (($_SESSION['user_type'] ?? '') === 'de') {
            $stmtProf = $pdo->prepare("SELECT id_user, nom, prenom FROM utilisateur WHERE type_utilisateur = 'professeur' ORDER BY nom, prenom");
            $stmtProf->execute();
            $professeurs = $stmtProf->fetchAll(\PDO::FETCH_ASSOC);

            $stmtRoles = $pdo->prepare('SELECT id_role, libelle FROM role_jury ORDER BY id_role');
            $stmtRoles->execute();
            $roles_jury = $stmtRoles->fetchAll(\PDO::FETCH_ASSOC);
        }

        require_once APP_PATH . '/models/Commentaire.php';
        $commentaires = Commentaire::getCommentaires((int) $id);

        $user_has_liked = false;
        if (isset($_SESSION['user_id'])) {
            $user_has_liked = Memoire::isLikedByUser((int) $id, (int) $_SESSION['user_id']);
        }

        require_once APP_PATH . '/views/memoires/show.php';
    }

    /**
     * Like ou unlike un mémoire (toggle)
     *
     * @param int $id_memoire ID du mémoire
     * @return void
     */
    public function like($id_memoire)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';

        $memoire = Memoire::getById($id_memoire);
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        if (Memoire::toggleLike((int) $id_memoire, (int) $_SESSION['user_id'])) {
            $_SESSION['flash_success'] = 'Votre réaction a été enregistrée.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de l\'enregistrement du like.';
        }

        header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
        exit();
    }

    /**
     * Supprime un membre du jury pour un mémoire (Direction uniquement)
     *
     * @param int $id_memoire ID du mémoire
     * @param int $id_prof ID du professeur à retirer
     * @return void
     */
    public function removeJury($id_memoire, $id_prof)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'de') {
            $this->denyAccess('Accès réservé à la direction des études.');
        }

        require_once APP_PATH . '/models/Memoire.php';

        $memoire = Memoire::getById($id_memoire);
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('DELETE FROM evaluer WHERE id_memoire = :id_memoire AND id_user_prof = :id_prof');
        $success = $stmt->execute([
            ':id_memoire' => (int) $id_memoire,
            ':id_prof' => (int) $id_prof,
        ]);

        if ($success) {
            $_SESSION['flash_success'] = 'Membre du jury retiré avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Impossible de supprimer l\'assignment du jury.';
        }

        header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
        exit();
    }

    /**
     * Publie un commentaire sur un mémoire
     *
     * @param int $id_memoire ID du mémoire
     * @return void
     */
    public function posterCommentaire($id_memoire)
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';
        require_once APP_PATH . '/models/Commentaire.php';

        $memoire = Memoire::getById($id_memoire);
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

        if ($contenu === '') {
            $_SESSION['flash_error'] = 'Le commentaire ne peut pas être vide.';
            header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
            exit();
        }

        $id_user = (int) $_SESSION['user_id'];

        if (Commentaire::addCommentaire((int) $id_memoire, $id_user, $contenu)) {
            $_SESSION['flash_success'] = 'Commentaire publié avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la publication du commentaire.';
        }

        header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
        exit();
    }

    /**
     * Assigne un membre au jury d'un mémoire (Direction uniquement)
     *
     * @param int $id_memoire ID du mémoire
     * @return void
     */
    public function assignJury($id_memoire)
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        if (($_SESSION['user_type'] ?? '') !== 'de') {
            $_SESSION['flash_error'] = 'Seule la Direction peut assigner des membres au jury.';
            header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';
        $memoire = Memoire::getById($id_memoire);
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        $id_prof = isset($_POST['id_prof']) ? (int) $_POST['id_prof'] : 0;
        $id_role = isset($_POST['id_role']) ? (int) $_POST['id_role'] : 0;

        if ($id_prof <= 0 || $id_role <= 0) {
            $_SESSION['flash_error'] = 'Veuillez sélectionner un professeur et un rôle.';
            header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
            exit();
        }

        $pdo = Database::getInstance()->getConnection();

        // Vérifier que le professeur existe
        $stmtProf = $pdo->prepare("SELECT id_user FROM utilisateur WHERE id_user = :id AND type_utilisateur = 'professeur' LIMIT 1");
        $stmtProf->execute([':id' => $id_prof]);
        if ($stmtProf->fetch(\PDO::FETCH_ASSOC) === false) {
            $_SESSION['flash_error'] = 'Le professeur sélectionné est invalide.';
            header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
            exit();
        }

        // Vérifier que le rôle existe
        $stmtRole = $pdo->prepare('SELECT id_role FROM role_jury WHERE id_role = :id LIMIT 1');
        $stmtRole->execute([':id' => $id_role]);
        if ($stmtRole->fetch(\PDO::FETCH_ASSOC) === false) {
            $_SESSION['flash_error'] = 'Le rôle sélectionné est invalide.';
            header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
            exit();
        }

        try {
            $sql = 'INSERT INTO evaluer (id_user_prof, id_memoire, id_role) VALUES (:id_prof, :id_memoire, :id_role)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_prof' => $id_prof,
                ':id_memoire' => (int) $id_memoire,
                ':id_role' => $id_role,
            ]);
            $_SESSION['flash_success'] = 'Membre ajouté au jury avec succès.';
        } catch (\PDOException $e) {
            // Clé primaire composite dupliquée (prof déjà assigné pour ce rôle/mémoire)
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = 'Ce professeur est déjà assigné à ce mémoire pour ce rôle.';
            } else {
                error_log('Erreur assignJury: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Erreur lors de l\'assignation au jury.';
            }
        }

        header('Location: ' . BASE_URL . '/memoires/' . (int) $id_memoire);
        exit();
    }
    
    /**
     * Affiche le formulaire de création de mémoire
     * 
     * @return void
     */
    public function create()
    {
        // Vérifier que l'utilisateur est authentifié
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        // Vérifier le verrou de scolarité (L3 ou M2 uniquement)
        require_once APP_PATH . '/models/Utilisateur.php';
        if (!Utilisateur::canDeposit((int) $_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Vous n\'êtes pas autorisé à déposer un mémoire. Seuls les étudiants en Licence 3 ou Master 2 peuvent déposer.';
            header('Location: ' . BASE_URL . '/memoires');
            exit();
        }

        // Charger la liste des professeurs pour le select "Maître de mémoire"
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT id_user, nom, prenom FROM utilisateur WHERE type_utilisateur = 'professeur' ORDER BY nom, prenom");
        $stmt->execute();
        $professeurs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Récupérer l'inscription active de l'étudiant
        require_once APP_PATH . '/models/Inscription.php';
        $inscription = Inscription::getActive((int) $_SESSION['user_id']);

        require_once APP_PATH . '/models/Admin.php';
        $filieres = Admin::getFilieres();
        $centres = Admin::getCentres();

        require_once APP_PATH . '/views/memoires/create.php';
    }
    
    /**
     * Traite la soumission du formulaire de création de mémoire
     * 
     * SÉCURITÉ : Valide l'upload, vérifie les extensions et types MIME.
     * 
     * @return void
     */
    public function store()
    {
        // Vérifier que l'utilisateur est authentifié
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        // Vérifier que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        // Récupérer et nettoyer les données
        $titre = isset($_POST['theme']) ? trim($_POST['theme']) : '';
        $resume = isset($_POST['resume']) ? trim($_POST['resume']) : '';
        $id_maitre_memoire = isset($_POST['id_maitre_memoire']) ? (int) $_POST['id_maitre_memoire'] : 0;

        // Valider les entrées
        if (empty($titre) || empty($resume)) {
            $_SESSION['flash_error'] = 'Le titre et le résumé sont obligatoires.';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        if ($id_maitre_memoire <= 0) {
            $_SESSION['flash_error'] = 'Veuillez sélectionner un maître de mémoire.';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        // Vérifier que le professeur sélectionné existe bien en base
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $stmtProf = $pdo->prepare("SELECT id_user FROM utilisateur WHERE id_user = :id AND type_utilisateur = 'professeur' LIMIT 1");
        $stmtProf->execute([':id' => $id_maitre_memoire]);
        if ($stmtProf->fetch(\PDO::FETCH_ASSOC) === false) {
            $_SESSION['flash_error'] = 'Le maître de mémoire sélectionné est invalide.';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash_error'] = 'Veuillez sélectionner un fichier.';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        $fichier = $_FILES['fichier'];

        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Erreur lors de l\'upload du fichier.';
            error_log('Erreur upload: ' . $fichier['error']);
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        $max_size = 50 * 1024 * 1024;
        if ($fichier['size'] > $max_size) {
            $_SESSION['flash_error'] = 'Le fichier est trop volumineux (maximum 50 Mo).';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        $extensions_autorisees = ['pdf', 'doc', 'docx'];
        $nom_fichier = $fichier['name'];
        $extension = strtolower(pathinfo($nom_fichier, PATHINFO_EXTENSION));

        if (!in_array($extension, $extensions_autorisees)) {
            $_SESSION['flash_error'] = 'Format de fichier non autorisé. Utilisez PDF, DOC ou DOCX.';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        $types_mime_autorisees = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $type_mime = mime_content_type($fichier['tmp_name']);

        if (!in_array($type_mime, $types_mime_autorisees)) {
            $_SESSION['flash_error'] = 'Type de fichier invalide.';
            error_log('Type MIME rejeté: ' . $type_mime);
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        $upload_dir = PUBLIC_PATH . '/uploads/memoires';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['flash_error'] = 'Erreur de création du dossier de destination.';
                header('Location: ' . BASE_URL . '/memoires/creer');
                exit();
            }
        }

        // Générer le nom de fichier selon la nomenclature stricte : Nom_Prenom_Centre_Filiere_Theme.extension
        $nom_etudiant = isset($_SESSION['user_nom']) ? $_SESSION['user_nom'] : '';
        $prenom_etudiant = isset($_SESSION['user_prenom']) ? $_SESSION['user_prenom'] : '';
        $centre = isset($_POST['centre']) ? trim($_POST['centre']) : '';
        $filiere = isset($_POST['filiere']) ? trim($_POST['filiere']) : '';

        // Si centre ou filière manquants du POST, les récupérer de l'inscription active
        if (empty($centre) || empty($filiere)) {
            require_once APP_PATH . '/models/Inscription.php';
            $inscription = Inscription::getActive((int) $_SESSION['user_id']);
            if (!empty($inscription)) {
                $centre = $centre ?: ($inscription['centre_libelle'] ?? '');
                $filiere = $filiere ?: ($inscription['filiere_libelle'] ?? '');
            }
        }

        // Nettoyer chaque composant du nom
        $nom_sanitized = $this->sanitizeString($nom_etudiant);
        $prenom_sanitized = $this->sanitizeString($prenom_etudiant);
        $centre_sanitized = $this->sanitizeString($centre);
        $filiere_sanitized = $this->sanitizeString($filiere);
        $theme_sanitized = $this->sanitizeString($titre);

        // Construire le nom complet
        $nom_unique = "{$nom_sanitized}_{$prenom_sanitized}_{$centre_sanitized}_{$filiere_sanitized}_{$theme_sanitized}.{$extension}";

        $chemin_destination = $upload_dir . '/' . $nom_unique;

        if (!move_uploaded_file($fichier['tmp_name'], $chemin_destination)) {
            $_SESSION['flash_error'] = 'Erreur lors du déplacement du fichier.';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';

        $chemin_relatif = '/gestion_memoires_uatm/public/uploads/memoires/' . $nom_unique;
        $user_id = (int) $_SESSION['user_id'];

        try {
            $db->beginTransaction();

            $id_memoire = Memoire::create($titre, $resume, $chemin_relatif);
            if ($id_memoire === false) {
                throw new \RuntimeException('Erreur lors de la création du mémoire.');
            }

            $sqlDeposer = 'INSERT INTO deposer (id_user, id_memoire) VALUES (:user_id, :memoire_id)';
            $stmtDeposer = $pdo->prepare($sqlDeposer);
            $stmtDeposer->execute([':user_id' => $user_id, ':memoire_id' => $id_memoire]);

            $sqlEvaluer = 'INSERT INTO evaluer (id_user_prof, id_memoire, id_role) VALUES (:id_prof, :id_memoire, 1)';
            $stmtEvaluer = $pdo->prepare($sqlEvaluer);
            $stmtEvaluer->execute([
                ':id_prof' => $id_maitre_memoire,
                ':id_memoire' => $id_memoire,
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            @unlink($chemin_destination);
            error_log('Erreur transaction dépôt mémoire: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Erreur lors de l\'enregistrement du mémoire. Veuillez réessayer.';
            header('Location: ' . BASE_URL . '/memoires/creer');
            exit();
        }

        $_SESSION['flash_success'] = 'Mémoire déposé avec succès!';
        header('Location: ' . BASE_URL . '/memoires');
        exit();
    }
    
    /**
     * Télécharge un fichier de mémoire
     * 
     * @param int $id ID du mémoire
     * @return void
     */
    public function download($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';
        
        $memoire = Memoire::getById($id);
        
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        if (!$this->canAccessFile($memoire, (int) $id)) {
            $this->denyAccess('Accès interdit : vous n\'êtes pas autorisé à télécharger ce document.');
        }
        
        $filepath = PUBLIC_PATH . str_replace('/gestion_memoires_uatm/public', '', $memoire['fichier_path']);
        
        if (!file_exists($filepath)) {
            $_SESSION['flash_error'] = 'Fichier non trouvé.';
            header('Location: ' . BASE_URL . '/memoires');
            exit();
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }

    /**
     * Sert un fichier de mémoire pour l'aperçu ou le téléchargement.
     *
     * @param int $id ID du mémoire
     * @return void
     */
    public function serveFichier($id)
    {
        require_once APP_PATH . '/models/Memoire.php';

        $memoire = Memoire::getById($id);
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        $fileRights = $this->getFileAccessRights($memoire, (int) $id);
        $can_download = $fileRights['can_download'];
        $can_preview = $fileRights['can_preview'];

        $action = isset($_GET['action']) ? strtolower(trim($_GET['action'])) : 'preview';
        if (!in_array($action, ['preview', 'download'], true)) {
            $this->denyAccess('Action invalide.');
        }

        if ($action === 'download' && !$can_download) {
            $this->denyAccess('Accès interdit : vous n\'êtes pas autorisé à télécharger ce document.');
        }

        if ($action === 'preview' && !$can_preview) {
            $this->denyAccess('Accès interdit : vous n\'êtes pas autorisé à prévisualiser ce document.');
        }

        $filepath = PUBLIC_PATH . str_replace('/gestion_memoires_uatm/public', '', $memoire['fichier_path']);
        if (!file_exists($filepath)) {
            $_SESSION['flash_error'] = 'Fichier non trouvé.';
            header('Location: ' . BASE_URL . '/memoires/' . (int) $id);
            exit();
        }

        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if ($action === 'preview' && $extension !== 'pdf') {
            $this->denyAccess('Prévisualisation disponible uniquement pour les fichiers PDF.');
        }

        header('Content-Type: application/pdf');
        if ($action === 'download') {
            header('Content-Disposition: attachment; filename="memoire_' . (int) $id . '.pdf"');
        } else {
            header('Content-Disposition: inline; filename="memoire_' . (int) $id . '.pdf"');
        }
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }
    
    /**
     * Met à jour le statut d'un mémoire
     * 
     * @param int $id ID du mémoire
     * @return void
     */
    public function updateStatus($id)
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
        
        if (!$this->isAdmin()) {
            $this->denyAccess('Seuls les administrateurs peuvent modifier les statuts.');
        }

        require_once APP_PATH . '/models/Memoire.php';

        $memoire = Memoire::getById($id);
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        $statut = isset($_POST['statut']) ? intval($_POST['statut']) : 0;
        
        if ($statut < 1 || $statut > 5) {
            $_SESSION['flash_error'] = 'Statut invalide.';
            header('Location: ' . BASE_URL . '/memoires');
            exit();
        }
        
        if (Memoire::updateStatus($id, $statut)) {
            $_SESSION['flash_success'] = 'Statut mis à jour avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour du statut.';
        }
        
        header('Location: ' . BASE_URL . '/memoires');
        exit();
    }
    
    /**
     * Affiche les mémoires de l'utilisateur connecté
     * 
     * @return void
     */
    /**
     * Affiche les mémoires déposés par l'utilisateur connecté (Étudiant)
     */
    public function mesDepots()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';
        
        // Récupérer uniquement les mémoires de cet auteur
        $memoires = Memoire::getByAuteur($_SESSION['user_id']);
        
        // Charger la vue
        require_once APP_PATH . '/views/memoires/mes_depots.php';
    }

    /**
     * Affiche les mémoires assignés au professeur connecté (jury)
     *
     * @return void
     */
    public function mesEvaluations()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        if (($_SESSION['user_type'] ?? '') !== 'professeur') {
            $_SESSION['flash_error'] = 'Accès réservé aux professeurs.';
            header('Location: ' . BASE_URL . '/memoires');
            exit();
        }

        require_once APP_PATH . '/models/Memoire.php';

        $id_prof = (int) $_SESSION['user_id'];
        $memoires = Memoire::getMemoiresByProf($id_prof);

        require_once APP_PATH . '/views/memoires/mes_evaluations.php';
    }
    
    /**
     * Supprime un mémoire
     * 
     * @param int $id ID du mémoire
     * @return void
     */
    public function delete($id)
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/memoires');
            exit();
        }
        
        if (!$this->isAdmin()) {
            $this->denyAccess('Seuls les administrateurs peuvent supprimer des mémoires.');
        }

        require_once APP_PATH . '/models/Memoire.php';

        $memoire = Memoire::getById($id);
        if ($memoire === false) {
            $this->denyAccess('Mémoire non trouvé.');
        }

        if (Memoire::delete($id)) {
            $_SESSION['flash_success'] = 'Mémoire supprimé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression du mémoire.';
        }

        header('Location: ' . BASE_URL . '/memoires');
        exit();
    }
}
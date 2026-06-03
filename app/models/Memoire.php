<?php
/**
 * ============================================================================
 * Modèle Memoire
 * ============================================================================
 * 
 * Classe d'accès aux données pour la table `memoire`.
 * Utilise PDO avec requêtes préparées pour la sécurité.
 */

class Memoire
{
    /**
     * Recherche et filtre les mémoires par mot-clé et/ou statut (paginée)
     *
     * @param string $keyword Mot-clé (thème ou résumé)
     * @param string|int $id_statut ID du statut (vide = tous)
     * @param int $limit Nombre de résultats par page
     * @param int $offset Décalage SQL
     * @return array Tableau des mémoires ou tableau vide
     */
    public static function search($mot_cle = '', $id_statut = '', $filiere = '', $annee = '', $centre = '', $limit = 10, $offset = 0)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();

            $hasFiliere = self::hasColumn($pdo, $databaseName, 'memoire', 'filiere');
            $hasAnnee = self::hasColumn($pdo, $databaseName, 'memoire', 'annee');
            $hasCentre = self::hasColumn($pdo, $databaseName, 'memoire', 'centre');

            $sql = 'SELECT DISTINCT m.*, d.id_user AS id_auteur, s.libelle AS statut_libelle,
                           auteur.nom AS auteur_nom, auteur.prenom AS auteur_prenom,
                           prof.nom AS prof_nom, prof.prenom AS prof_prenom
                    FROM memoire m
                    LEFT JOIN deposer d ON m.id_memoire = d.id_memoire
                    LEFT JOIN utilisateur auteur ON d.id_user = auteur.id_user
                    LEFT JOIN evaluer e ON m.id_memoire = e.id_memoire
                    LEFT JOIN utilisateur prof ON e.id_user_prof = prof.id_user
                    LEFT JOIN statut_memoire s ON m.id_statut = s.id_statut
                    WHERE 1=1';

            if ($mot_cle !== '') {
                $sql .= ' AND (m.theme LIKE :mot OR m.resume LIKE :mot OR auteur.nom LIKE :mot OR auteur.prenom LIKE :mot OR prof.nom LIKE :mot OR prof.prenom LIKE :mot)';
            }
            if ($id_statut !== '' && $id_statut !== null) {
                $sql .= ' AND m.id_statut = :statut';
            }
            if ($filiere !== '' && $hasFiliere) {
                $sql .= ' AND m.filiere = :filiere';
            }
            if ($annee !== '' && $hasAnnee) {
                $sql .= ' AND m.annee = :annee';
            }
            if ($centre !== '' && $hasCentre) {
                $sql .= ' AND m.centre = :centre';
            }

            $sql .= ' ORDER BY m.id_memoire DESC LIMIT :limit OFFSET :offset';
            $stmt = $pdo->prepare($sql);

            if ($mot_cle !== '') {
                $stmt->bindValue(':mot', '%' . $mot_cle . '%', \PDO::PARAM_STR);
            }
            if ($id_statut !== '' && $id_statut !== null) {
                $stmt->bindValue(':statut', (int) $id_statut, \PDO::PARAM_INT);
            }
            if ($filiere !== '' && $hasFiliere) {
                $stmt->bindValue(':filiere', $filiere, \PDO::PARAM_STR);
            }
            if ($annee !== '' && $hasAnnee) {
                $stmt->bindValue(':annee', $annee, \PDO::PARAM_STR);
            }
            if ($centre !== '' && $hasCentre) {
                $stmt->bindValue(':centre', $centre, \PDO::PARAM_STR);
            }

            $stmt->bindValue(':limit', (int) $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, \PDO::PARAM_INT);

            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $results !== false ? $results : [];

        } catch (\PDOException $e) {
            error_log('Erreur search memoires: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total de résultats pour une recherche/filtre
     *
     * @param string $keyword Mot-clé (thème ou résumé)
     * @param string|int $id_statut ID du statut (vide = tous)
     * @return int
     */
    public static function countSearchResults($mot_cle = '', $id_statut = '', $filiere = '', $annee = '', $centre = '')
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();

            $hasFiliere = self::hasColumn($pdo, $databaseName, 'memoire', 'filiere');
            $hasAnnee = self::hasColumn($pdo, $databaseName, 'memoire', 'annee');
            $hasCentre = self::hasColumn($pdo, $databaseName, 'memoire', 'centre');

            $sql = 'SELECT COUNT(DISTINCT m.id_memoire) FROM memoire m
                    LEFT JOIN deposer d ON m.id_memoire = d.id_memoire
                    LEFT JOIN utilisateur auteur ON d.id_user = auteur.id_user
                    LEFT JOIN evaluer e ON m.id_memoire = e.id_memoire
                    LEFT JOIN utilisateur prof ON e.id_user_prof = prof.id_user
                    WHERE 1=1';

            if ($mot_cle !== '') {
                $sql .= ' AND (m.theme LIKE :mot OR m.resume LIKE :mot OR auteur.nom LIKE :mot OR auteur.prenom LIKE :mot OR prof.nom LIKE :mot OR prof.prenom LIKE :mot)';
            }
            if ($id_statut !== '' && $id_statut !== null) {
                $sql .= ' AND m.id_statut = :statut';
            }
            if ($filiere !== '' && $hasFiliere) {
                $sql .= ' AND m.filiere = :filiere';
            }
            if ($annee !== '' && $hasAnnee) {
                $sql .= ' AND m.annee = :annee';
            }
            if ($centre !== '' && $hasCentre) {
                $sql .= ' AND m.centre = :centre';
            }

            $stmt = $pdo->prepare($sql);

            if ($mot_cle !== '') {
                $stmt->bindValue(':mot', '%' . $mot_cle . '%', \PDO::PARAM_STR);
            }
            if ($id_statut !== '' && $id_statut !== null) {
                $stmt->bindValue(':statut', (int) $id_statut, \PDO::PARAM_INT);
            }
            if ($filiere !== '' && $hasFiliere) {
                $stmt->bindValue(':filiere', $filiere, \PDO::PARAM_STR);
            }
            if ($annee !== '' && $hasAnnee) {
                $stmt->bindValue(':annee', $annee, \PDO::PARAM_STR);
            }
            if ($centre !== '' && $hasCentre) {
                $stmt->bindValue(':centre', $centre, \PDO::PARAM_STR);
            }

            $stmt->execute();
            return (int) $stmt->fetchColumn();

        } catch (\PDOException $e) {
            error_log('Erreur countSearchResults: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère tous les mémoires avec recherche optionnelle
     * 
     * @param string $search Terme de recherche optionnel
     * @return array Tableau des mémoires ou tableau vide
     */
    public static function getAll($search = '')
    {
        return self::search($search, '');
    }
    
    /**
     * Récupère un mémoire par son ID
     * 
     * @param int $id ID du mémoire
     * @return array|false Tableau associatif avec les données ou false
     */
    public static function getById($id)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            $sql = "SELECT m.*, d.id_user AS id_auteur
                    FROM memoire m
                    LEFT JOIN deposer d ON m.id_memoire = d.id_memoire
                    WHERE m.id_memoire = :id
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result !== false ? $result : false;
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la récupération du mémoire par ID: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si une colonne existe dans une table donnée.
     *
     * @param \PDO $pdo Instance PDO
     * @param string $database Nom de la base de données
     * @param string $table Nom de la table
     * @param string $column Nom de la colonne
     * @return bool
     */
    private static function hasColumn(\PDO $pdo, string $database, string $table, string $column): bool
    {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :column');
            $stmt->execute([
                ':schema' => $database,
                ':table' => $table,
                ':column' => $column,
            ]);

            return (bool) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupère tous les mémoires avec recherche optionnelle
     * 
     * @param string $titre Titre du mémoire
     * @param string $resume Résumé du mémoire
     * @param string $cheminFichier Chemin du fichier uploadé
     * @return int|false ID du mémoire créé ou false
     */
    public static function create($titre, $resume, $cheminFichier)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            $sql = "INSERT INTO memoire (theme, resume, fichier_path, id_statut) VALUES (:theme, :resume, :fichier_path, :statut)";
            $stmt = $pdo->prepare($sql);
            
            $success = $stmt->execute([
                ':theme' => $titre,
                ':resume' => $resume,
                ':fichier_path' => $cheminFichier,
                ':statut' => 1  // Statut "Brouillon"
            ]);
            
            if ($success) {
                return $pdo->lastInsertId();
            }
            
            return false;
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la création du mémoire: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour le statut d'un mémoire
     * 
     * @param int $id ID du mémoire
     * @param int $statut Nouveau statut
     * @return bool true si succès, false sinon
     */
    public static function updateStatus($id, $statut)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            $sql = "UPDATE memoire SET id_statut = :statut WHERE id_memoire = :id";
            $stmt = $pdo->prepare($sql);
            
            return $stmt->execute([':id' => $id, ':statut' => $statut]);
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la mise à jour du statut: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les statistiques des mémoires
     * 
     * @param int|null $userId ID de l'utilisateur (optionnel)
     * @param string $userType Type d'utilisateur
     * @return array Tableau avec 'total', 'valide', 'en_attente', 'rejete'
     */
    public static function getStatistics($userId = null, $userType = 'Admin')
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            if (!is_null($userId) && $userType !== 'Admin' && $userType !== 'Direction') {
                if ($userType === 'professeur') {
                    $sql = "SELECT m.id_statut, COUNT(*) as count FROM memoire m INNER JOIN evaluer e ON m.id_memoire = e.id_memoire WHERE e.id_user_prof = :userId GROUP BY m.id_statut";
                } else {
                    $sql = "SELECT m.id_statut, COUNT(*) as count FROM memoire m INNER JOIN deposer d ON m.id_memoire = d.id_memoire WHERE d.id_user = :userId GROUP BY m.id_statut";
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':userId' => $userId]);
            } else {
                $sql = "SELECT id_statut, COUNT(*) as count FROM memoire GROUP BY id_statut";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }
            
            $stats = ['total' => 0, 'valide' => 0, 'en_attente' => 0, 'rejete' => 0];
            
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $stats['total'] += $row['count'];
                if ($row['id_statut'] == 3) {
                    $stats['valide'] += $row['count'];
                } elseif ($row['id_statut'] == 2) {
                    $stats['en_attente'] += $row['count'];
                } elseif ($row['id_statut'] == 4) {
                    $stats['rejete'] += $row['count'];
                }
            }
            
            return $stats;
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return ['total' => 0, 'valide' => 0, 'en_attente' => 0, 'rejete' => 0];
        }
    }

    /**
     * Statistiques globales de la plateforme (tous les mémoires)
     *
     * @return array total, brouillon, en_attente, valide, rejete, archive
     */
    public static function getGlobalStats()
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $sql = 'SELECT s.libelle, s.id_statut, COUNT(m.id_memoire) AS total
                    FROM statut_memoire s
                    LEFT JOIN memoire m ON s.id_statut = m.id_statut
                    GROUP BY s.id_statut, s.libelle
                    ORDER BY s.id_statut';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $stats = [
                'total' => 0,
                'brouillon' => 0,
                'en_attente' => 0,
                'valide' => 0,
                'rejete' => 0,
                'archive' => 0,
            ];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $count = (int) $row['total'];
                $stats['total'] += $count;

                switch ((int) $row['id_statut']) {
                    case 1: $stats['brouillon'] = $count; break;
                    case 2: $stats['en_attente'] = $count; break;
                    case 3: $stats['valide'] = $count; break;
                    case 4: $stats['rejete'] = $count; break;
                    case 5: $stats['archive'] = $count; break;
                }
            }

            return $stats;

        } catch (\PDOException $e) {
            error_log('Erreur getGlobalStats: ' . $e->getMessage());
            return ['total' => 0, 'brouillon' => 0, 'en_attente' => 0, 'valide' => 0, 'rejete' => 0, 'archive' => 0];
        }
    }

    /**
     * Récupère les derniers mémoires déposés avec auteur et statut
     *
     * @param int $limit Nombre de résultats
     * @return array
     */
    public static function getRecentMemoires($limit = 5)
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $sql = 'SELECT m.id_memoire, m.theme, m.date_depot, s.libelle AS statut, s.id_statut,
                           u.nom, u.prenom
                    FROM memoire m
                    LEFT JOIN deposer d ON m.id_memoire = d.id_memoire
                    LEFT JOIN utilisateur u ON d.id_user = u.id_user
                    JOIN statut_memoire s ON m.id_statut = s.id_statut
                    ORDER BY m.date_depot DESC
                    LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, \PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $results !== false ? $results : [];

        } catch (\PDOException $e) {
            error_log('Erreur getRecentMemoires: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les mémoires d'un utilisateur (auteur)
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Tableau des mémoires ou tableau vide
     */
    public static function getByAuteur($userId)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            $sql = "SELECT m.* FROM memoire m INNER JOIN deposer d ON m.id_memoire = d.id_memoire WHERE d.id_user = :userId ORDER BY m.id_memoire DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':userId' => $userId]);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $results !== false ? $results : [];
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la récupération des mémoires par auteur: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les mémoires assignés à un professeur (table evaluer / jury)
     *
     * @param int $id_prof ID du professeur
     * @return array Tableau des mémoires ou tableau vide
     */
    public static function getMemoiresByProf($id_prof)
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $sql = 'SELECT m.id_memoire, m.theme, m.date_depot, s.libelle AS statut, s.id_statut,
                           r.libelle AS role_jury, e.date_eval AS date_assignation
                    FROM memoire m
                    JOIN evaluer e ON m.id_memoire = e.id_memoire
                    JOIN statut_memoire s ON m.id_statut = s.id_statut
                    JOIN role_jury r ON e.id_role = r.id_role
                    WHERE e.id_user_prof = :id_prof
                    ORDER BY m.date_depot DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_prof' => $id_prof]);

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $results !== false ? $results : [];

        } catch (\PDOException $e) {
            error_log('Erreur getMemoiresByProf: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un professeur est assigné à l'évaluation d'un mémoire (table evaluer)
     *
     * @param int $id_memoire ID du mémoire
     * @param int $id_prof ID du professeur
     * @return bool
     */
    public static function isProfAssigne($id_memoire, $id_prof)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = 'SELECT COUNT(*) FROM evaluer WHERE id_memoire = :id_memoire AND id_user_prof = :id_prof';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_memoire' => $id_memoire,
                ':id_prof' => $id_prof,
            ]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Erreur isProfAssigne: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur a déjà liké un mémoire
     *
     * @param int $id_memoire ID du mémoire
     * @param int $id_user ID de l'utilisateur
     * @return bool
     */
    public static function isLikedByUser($id_memoire, $id_user)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = 'SELECT COUNT(*) FROM liker WHERE id_memoire = :id_memoire AND id_user = :id_user';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_memoire' => $id_memoire,
                ':id_user' => $id_user,
            ]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Erreur isLikedByUser: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajoute ou retire un like (toggle) et met à jour nb_likes
     *
     * @param int $id_memoire ID du mémoire
     * @param int $id_user ID de l'utilisateur
     * @return bool true si succès, false sinon
     */
    public static function toggleLike($id_memoire, $id_user)
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        try {
            $db->beginTransaction();

            if (self::isLikedByUser($id_memoire, $id_user)) {
                $stmtDelete = $pdo->prepare('DELETE FROM liker WHERE id_memoire = :id_memoire AND id_user = :id_user');
                $stmtDelete->execute([
                    ':id_memoire' => $id_memoire,
                    ':id_user' => $id_user,
                ]);

                $stmtUpdate = $pdo->prepare('UPDATE memoire SET nb_likes = GREATEST(nb_likes - 1, 0) WHERE id_memoire = :id_memoire');
                $stmtUpdate->execute([':id_memoire' => $id_memoire]);
            } else {
                $stmtInsert = $pdo->prepare('INSERT INTO liker (id_user, id_memoire) VALUES (:id_user, :id_memoire)');
                $stmtInsert->execute([
                    ':id_user' => $id_user,
                    ':id_memoire' => $id_memoire,
                ]);

                $stmtUpdate = $pdo->prepare('UPDATE memoire SET nb_likes = nb_likes + 1 WHERE id_memoire = :id_memoire');
                $stmtUpdate->execute([':id_memoire' => $id_memoire]);
            }

            $db->commit();
            return true;

        } catch (\PDOException $e) {
            $db->rollback();
            error_log('Erreur toggleLike: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un mémoire
     * 
     * @param int $id ID du mémoire
     * @return bool true si succès, false sinon
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            $sql = "DELETE FROM memoire WHERE id_memoire = :id";
            $stmt = $pdo->prepare($sql);
            
            return $stmt->execute([':id' => $id]);
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la suppression du mémoire: ' . $e->getMessage());
            return false;
        }
    }
}

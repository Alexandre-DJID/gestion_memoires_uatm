<?php
/**
 * ============================================================================
 * Modèle Utilisateur
 * ============================================================================
 * 
 * Classe d'accès aux données pour la table `utilisateur`.
 * Utilise PDO avec requêtes préparées pour la sécurité.
 */

class Utilisateur
{
    /**
     * Récupère un utilisateur par son email
     * 
     * @param string $email Email de l'utilisateur
     * @return array|false Tableau associatif avec les données ou false
     */
    public static function findByEmail($email)
    {
        try {
            // Récupérer l'instance PDO via le Singleton Database
            $pdo = Database::getInstance()->getConnection();
            
            // Préparer la requête
            $sql = "SELECT * FROM utilisateur WHERE email = :email LIMIT 1";
            $stmt = $pdo->prepare($sql);
            
            // Exécuter la requête avec les paramètres liés
            $stmt->execute([':email' => $email]);
            
            // Récupérer le résultat
            $result = $stmt->fetch();
            
            // Retourner le résultat ou false
            return $result !== false ? $result : false;
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la recherche d\'utilisateur par email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère un utilisateur par son ID
     * 
     * @param int $id ID de l'utilisateur
     * @return array|false Tableau associatif avec les données ou false
     */
    public static function findById($id)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            $sql = "SELECT * FROM utilisateur WHERE id_user = :id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $result = $stmt->fetch();
            
            return $result !== false ? $result : false;
            
        } catch (\PDOException $e) {
            error_log('Erreur lors de la recherche d\'utilisateur par ID: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur par son ID (alias explicite pour le profil)
     *
     * @param int $id_user ID de l'utilisateur
     * @return array|false
     */
    public static function getUserById($id_user)
    {
        return self::findById($id_user);
    }

    /**
     * Met à jour le nom et prénom de l'utilisateur
     *
     * @param int $id_user ID de l'utilisateur
     * @param string $nom Nom
     * @param string $prenom Prénom
     * @return bool
     */
    public static function updateInfo($id_user, $nom, $prenom)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = 'UPDATE utilisateur SET nom = :nom, prenom = :prenom WHERE id_user = :id_user';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':id_user' => $id_user,
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur updateInfo utilisateur: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le mot de passe hashé de l'utilisateur
     *
     * @param int $id_user ID de l'utilisateur
     * @param string $new_password_hashed Hash bcrypt
     * @return bool
     */
    public static function updatePassword($id_user, $new_password_hashed)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = 'UPDATE utilisateur SET mot_de_passe = :hash WHERE id_user = :id_user';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':hash' => $new_password_hashed,
                ':id_user' => $id_user,
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur updatePassword utilisateur: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouvel utilisateur avec son type spécifique.
     *
     * @param string $nom
     * @param string $prenom
     * @param string $email
     * @param string $mot_de_passe Doit être hashé via password_hash()
     * @param string $type_utilisateur ('etudiant', 'professeur', 'de')
     * @param array $extra_data Données supplémentaires (matricule, grade, etc.)
     * @return int|false ID du nouvel utilisateur ou false
     */
    public static function create($nom, $prenom, $email, $mot_de_passe, $type_utilisateur, $extra_data = [])
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            // Démarrer une transaction
            $pdo->beginTransaction();

            // Insérer dans utilisateur
            $sql = 'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, type_utilisateur) 
                    VALUES (:nom, :prenom, :email, :mot_de_passe, :type)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mot_de_passe' => $mot_de_passe,
                ':type' => $type_utilisateur,
            ]);

            $id_user = (int) $pdo->lastInsertId();

            // Insérer dans la table spécialisée selon le type
            if ($type_utilisateur === 'etudiant') {
                $matricule = $extra_data['matricule'] ?? 'AUTO_' . $id_user;
                $sql = 'INSERT INTO etudiant (id_user, matricule) VALUES (:id_user, :matricule)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_user' => $id_user, ':matricule' => $matricule]);
            } elseif ($type_utilisateur === 'professeur') {
                $grade = $extra_data['grade'] ?? 'Maître-assistant';
                $sql = 'INSERT INTO professeur (id_user, grade) VALUES (:id_user, :grade)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_user' => $id_user, ':grade' => $grade]);
            } elseif ($type_utilisateur === 'de') {
                $sql = 'INSERT INTO de (id_user) VALUES (:id_user)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_user' => $id_user]);
            }

            $pdo->commit();
            return $id_user;
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Erreur création utilisateur: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un étudiant peut déposer un mémoire (L3 ou M2).
     *
     * @param int $id_user ID de l'utilisateur
     * @return bool
     */
    public static function canDeposit($id_user): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            // Récupérer l'inscription active (L3 ou M2 : id 3 ou 5)
            $sql = 'SELECT COUNT(*) FROM inscription 
                    WHERE id_user = :id_user AND id_niveau IN (3, 5)
                    ORDER BY date_inscription DESC LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_user' => $id_user]);
            return (bool) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log('Erreur canDeposit: ' . $e->getMessage());
            return false;
        }
    }
}

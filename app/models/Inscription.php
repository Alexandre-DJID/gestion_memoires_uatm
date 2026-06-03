<?php

class Inscription
{
    /**
     * Crée une nouvelle inscription pour un étudiant.
     *
     * @param int $id_user
     * @param int $id_filiere
     * @param int $id_centre
     * @param int $id_annee
     * @param int $id_niveau
     * @return bool
     */
    public static function create($id_user, $id_filiere, $id_centre, $id_annee, $id_niveau): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = 'INSERT INTO inscription (id_user, id_filiere, id_centre, id_annee, id_niveau) 
                    VALUES (:id_user, :id_filiere, :id_centre, :id_annee, :id_niveau)';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':id_user' => (int) $id_user,
                ':id_filiere' => (int) $id_filiere,
                ':id_centre' => (int) $id_centre,
                ':id_annee' => (int) $id_annee,
                ':id_niveau' => (int) $id_niveau,
            ]);
        } catch (\PDOException $e) {
            error_log('Erreur création inscription: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère l'inscription active d'un étudiant.
     *
     * @param int $id_user
     * @return array|false
     */
    public static function getActive($id_user)
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = 'SELECT i.*, f.libelle AS filiere_libelle, c.libelle AS centre_libelle, 
                           n.libelle AS niveau_libelle, a.annee 
                    FROM inscription i
                    LEFT JOIN filiere f ON i.id_filiere = f.id_filiere
                    LEFT JOIN centre c ON i.id_centre = c.id_centre
                    LEFT JOIN niveau_etude n ON i.id_niveau = n.id_niveau
                    LEFT JOIN annee_academique a ON i.id_annee = a.id_annee
                    WHERE i.id_user = :id_user
                    ORDER BY i.date_inscription DESC LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id_user' => (int) $id_user]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result !== false ? $result : false;
        } catch (\PDOException $e) {
            error_log('Erreur getActive inscription: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les niveaux d'étude.
     *
     * @return array
     */
    public static function getNiveaux(): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->query('SELECT id_niveau, libelle FROM niveau_etude ORDER BY ordre');
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur getNiveaux: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère toutes les années académiques.
     *
     * @return array
     */
    public static function getAnnees(): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->query('SELECT id_annee, annee FROM annee_academique ORDER BY annee DESC');
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur getAnnees: ' . $e->getMessage());
            return [];
        }
    }
}

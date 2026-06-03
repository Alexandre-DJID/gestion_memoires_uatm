<?php

class Admin
{
    /**
     * Récupère toutes les filières.
     *
     * @return array
     */
    public static function getFilieres(): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->query('SELECT id_filiere, libelle FROM filiere ORDER BY libelle');
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur getFilieres: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajoute une nouvelle filière.
     *
     * @param string $nom
     * @return bool
     */
    public static function addFiliere(string $nom): bool
    {
        if (trim($nom) === '') {
            return false;
        }

        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('INSERT INTO filiere (libelle) VALUES (:nom)');
            return $stmt->execute([':nom' => trim($nom)]);
        } catch (\PDOException $e) {
            error_log('Erreur addFiliere: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une filière.
     *
     * @param int $id
     * @return bool
     */
    public static function deleteFiliere(int $id): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare('DELETE FROM filiere WHERE id_filiere = :id');
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            error_log('Erreur deleteFiliere: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les centres.
     *
     * @return array
     */
    public static function getCentres(): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->query('SELECT id_centre, libelle FROM centre ORDER BY libelle');
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur getCentres: ' . $e->getMessage());
            return [];
        }
    }
}

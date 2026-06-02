<?php
/**
 * ============================================================================
 * Modèle Commentaire
 * ============================================================================
 *
 * Accès aux données de la table `commenter`.
 */

class Commentaire
{
    /**
     * Récupère les commentaires d'un mémoire avec les infos auteur
     *
     * @param int $id_memoire ID du mémoire
     * @return array
     */
    public static function getCommentaires($id_memoire)
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $sql = 'SELECT c.contenu, c.date_pub, u.nom, u.prenom, u.type_utilisateur
                    FROM commenter c
                    JOIN utilisateur u ON c.id_user = u.id_user
                    WHERE c.id_memoire = :id
                    ORDER BY c.date_pub DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_memoire]);

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $results !== false ? $results : [];

        } catch (\PDOException $e) {
            error_log('Erreur getCommentaires: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajoute un commentaire sur un mémoire
     *
     * @param int $id_memoire ID du mémoire
     * @param int $id_user ID de l'utilisateur
     * @param string $contenu Texte du commentaire
     * @return bool
     */
    public static function addCommentaire($id_memoire, $id_user, $contenu)
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $sql = 'INSERT INTO commenter (id_memoire, id_user, contenu) VALUES (:id_memoire, :id_user, :contenu)';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':id_memoire' => $id_memoire,
                ':id_user' => $id_user,
                ':contenu' => $contenu,
            ]);

        } catch (\PDOException $e) {
            error_log('Erreur addCommentaire: ' . $e->getMessage());
            return false;
        }
    }
}

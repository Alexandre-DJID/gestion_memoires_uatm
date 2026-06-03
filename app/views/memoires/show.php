<?php
/**
 * Vue Détail d'un Mémoire
 */
ob_start();
?>
<div class="card">
    <a href="javascript:history.back()" class="btn btn-ghost btn-sm" style="margin-bottom:16px;">Retour</a>

    <div class="memoire-detail-header">
        <h2><?= htmlspecialchars($memoire['theme'] ?? $memoire['titre'] ?? 'Thème non défini', ENT_QUOTES, 'UTF-8'); ?></h2>
        <p><?= htmlspecialchars(trim(($memoire['prenom'] ?? '') . ' ' . ($memoire['nom'] ?? '')), ENT_QUOTES, 'UTF-8'); ?> &nbsp;&nbsp; <?= htmlspecialchars(!empty($memoire['date_depot']) ? (new DateTime($memoire['date_depot']))->format('Y') : '', ENT_QUOTES, 'UTF-8'); ?></p>
        <p style="margin-top:4px;">Statut actuel : <?php $statut_id = (int) ($memoire['id_statut'] ?? 0); require APP_PATH . '/views/partials/status_badge.php'; ?></p>
        <div class="memoire-actions">
            <a href="<?= BASE_URL ?>/memoires/<?= (int) ($memoire['id_memoire'] ?? 0); ?>/like" class="like-chip<?= !empty($user_has_liked) ? ' active' : ''; ?>" aria-label="J'aime">
                <?php if (!empty($user_has_liked)): ?>
                    <i class="fas fa-heart" style="color:#dc3545;font-size:20px;"></i>
                <?php else: ?>
                    <i class="far fa-heart" style="font-size:20px;"></i>
                <?php endif; ?>
                <span><?= (int) ($memoire['nb_likes'] ?? 0); ?></span>
            </a>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><h3 class="card-title">Résumé</h3></div>
        <p><?= nl2br(htmlspecialchars($memoire['resume'] ?? 'Aucun résumé fourni.', ENT_QUOTES, 'UTF-8')); ?></p>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Composition du jury</h3></div>
        <?php if (!empty($jury_actuel)): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Nom</th><th>Prénom</th><th>Rôle</th><?php if (($_SESSION['user_type'] ?? '') === 'de'): ?><th>Action</th><?php endif; ?></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jury_actuel as $membre): ?>
                            <tr>
                                <td><?= htmlspecialchars($membre['nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($membre['prenom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($membre['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php if (($_SESSION['user_type'] ?? '') === 'de'): ?>
                                    <td>
                                        <a href="<?= BASE_URL ?>/memoires/<?= (int) $memoire['id_memoire']; ?>/jury/delete/<?= (int) $membre['id_user']; ?>" class="btn btn-danger btn-sm" title="Supprimer du jury" onclick="return confirm('Confirmer la suppression du membre du jury ?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Aucun membre assigné.</p>
        <?php endif; ?>
    </div>

    <?php if (($_SESSION['user_type'] ?? '') === 'de'): ?>
        <div class="card" style="margin-top:16px;">
            <div class="card-header"><h3 class="card-title">Assigner un membre au jury</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/memoires/<?= (int) ($memoire['id_memoire'] ?? 0); ?>/assigner-jury">
                <div class="grid-2" style="gap:16px; margin-top:16px;">
                    <div>
                        <label class="form-label" for="id_prof">Professeur</label>
                        <select id="id_prof" name="id_prof" class="form-control" required>
                            <option value="">Sélectionnez un professeur</option>
                            <?php foreach ($professeurs ?? [] as $prof): ?>
                                <option value="<?= (int) $prof['id_user']; ?>"><?= htmlspecialchars($prof['prenom'] . ' ' . $prof['nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="id_role">Rôle</label>
                        <select id="id_role" name="id_role" class="form-control" required>
                            <option value="">Sélectionnez un rôle</option>
                            <?php foreach ($roles_jury ?? [] as $role): ?>
                                <option value="<?= (int) $role['id_role']; ?>"><?= htmlspecialchars($role['libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:16px;">Ajouter au jury</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="card" style="margin-top:20px;">
        <?php $cheminFichier = $memoire['fichier_path'] ?? ''; $extension = strtolower(pathinfo($cheminFichier, PATHINFO_EXTENSION)); ?>
        <?php if (!empty($can_preview)): ?>
            <?php if (!empty($can_download)): ?>
                <div style="margin-bottom:16px;">
                    <a href="<?= BASE_URL ?>/memoires/<?= (int) ($memoire['id_memoire'] ?? 0); ?>/fichier?action=download" class="btn btn-primary">
                        <i class="fas fa-download"></i> Télécharger le document complet
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($extension === 'pdf'): ?>
                <iframe src="<?= BASE_URL ?>/memoires/<?= (int) ($memoire['id_memoire'] ?? 0); ?>/fichier?action=preview#toolbar=0" width="100%" height="600px" style="border: 1px solid #ddd; border-radius: 8px;"></iframe>
            <?php else: ?>
                <div style="color:var(--gris-texte);text-align:center;padding:60px 0;">
                    <p>Prévisualisation indisponible pour ce format.</p>
                    <?php if (!empty($can_download)): ?>
                        <p>Utilisez le bouton de téléchargement pour accéder au document.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Le fichier de ce mémoire est réservé à l'auteur, son jury et la direction.</p>
        <?php endif; ?>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-header"><h3 class="card-title">Retours et discussions</h3></div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" action="<?= BASE_URL ?>/memoires/<?= (int) ($memoire['id_memoire'] ?? 0); ?>/commenter" style="margin-bottom:20px;">
                <textarea name="contenu" class="form-control" required placeholder="Votre message..."></textarea>
                <button type="submit" class="btn btn-primary" style="margin-top:12px;">Publier</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($commentaires)): ?>
            <div class="comment-list">
                <?php foreach ($commentaires as $com): ?>
                    <?php
                    $typeUser = $com['type_utilisateur'] ?? 'etudiant';
                    $badgeLabel = $typeUser === 'de' ? 'Direction' : ($typeUser === 'professeur' ? 'Professeur' : 'Étudiant');
                    ?>
                    <div class="comment-item">
                        <div class="flex flex-between" style="gap:10px;flex-wrap:wrap;">
                            <strong><?= htmlspecialchars($com['prenom'] . ' ' . $com['nom'], ENT_QUOTES, 'UTF-8'); ?> <span class="badge badge-info"><?= htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></span></strong>
                            <span class="comment-date"><?= (new DateTime($com['date_pub']))->format('d/m/Y H:i'); ?></span>
                        </div>
                        <div><?= nl2br(htmlspecialchars($com['contenu'], ENT_QUOTES, 'UTF-8')); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aucun commentaire pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Détail du mémoire';
$pageSubtitle = 'Informations détaillées, jury et discussions associées.';
$page_css = [BASE_URL . '/assets/css/consulter.css'];
require_once APP_PATH . '/views/layouts/main.php';

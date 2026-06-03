<?php
/**
 * Vue Mes Dépôts
 */
ob_start();
?>
<div class="card">
    <?php if (!empty($memoires)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Thème</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memoires as $memoire): ?>
                        <tr>
                            <td><?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php $statut_id = $memoire['id_statut'] ?? 1; require APP_PATH . '/views/partials/status_badge.php'; ?></td>
                            <td><?= (new DateTime($memoire['date_depot']))->format('d/m/Y'); ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/memoires/<?= (int) $memoire['id_memoire']; ?>" class="btn btn-outline btn-sm">Consulter</a>
                                <a href="<?= BASE_URL ?>/memoires/telecharger/<?= (int) $memoire['id_memoire']; ?>" class="btn btn-primary btn-sm">Télécharger</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card">
            <p><strong>Aucun dépôt</strong></p>
            <p>Vous n'avez pas encore déposé de mémoire.</p>
            <a href="<?= BASE_URL ?>/memoires/creer" class="btn btn-primary">Déposer un mémoire</a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Mes dépôts';
$pageSubtitle = 'Vos mémoires soumis sur la plateforme.';
$page_css = [BASE_URL . '/assets/css/consulter.css'];
require_once APP_PATH . '/views/layouts/main.php';

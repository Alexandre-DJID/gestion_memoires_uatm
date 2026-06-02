<?php
/**
 * Vue Mes Évaluations
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
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memoires as $memoire): ?>
                        <tr>
                            <td><?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($memoire['role_jury'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php $statut_id = $memoire['id_statut'] ?? 1; require APP_PATH . '/views/partials/status_badge.php'; ?></td>
                            <td><?= (new DateTime($memoire['date_assignation'] ?? $memoire['date_depot']))->format('d/m/Y'); ?></td>
                            <td><a href="/gestion_memoires_uatm/public/memoires/<?= (int) $memoire['id_memoire']; ?>" class="btn btn-primary btn-sm">Consulter</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card">
            <p><strong>Aucun mémoire assigné</strong></p>
            <p>Aucun mémoire ne vous a été assigné pour le moment.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Mes évaluations';
$pageSubtitle = 'Mémoires qui vous sont assignés en tant que membre du jury.';
$page_css = ['/gestion_memoires_uatm/public/assets/css/consulter.css'];
require_once APP_PATH . '/views/layouts/main.php';

<?php
ob_start();
?>
<div class="welcome-banner">
    <div>
        <h2>Bienvenue <?= htmlspecialchars($_SESSION['user_prenom'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
        <p>Suivez l'état de validation de vos mémoires et l'activité de votre jury.</p>
    </div>
    <div class="welcome-icon"><i class="fas fa-pen-nib" style="color:#6c757d; font-size:28px;"></i></div>
</div>

<section class="stats-grid">
    <article class="stat-card card">
        <div class="stat-icon"><i class="fas fa-folder" style="color:#6c757d; font-size:24px;"></i></div>
        <div class="stat-number"><?= count($memoires); ?></div>
        <div class="stat-label">Mémoires soumis</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon"><i class="fas fa-check-circle" style="color:#28a745; font-size:24px;"></i></div>
        <div class="stat-number"><?= (int) ($stats['valide'] ?? 0); ?></div>
        <div class="stat-label">Validés</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon"><i class="fas fa-hourglass-half" style="color:#ffc107; font-size:24px;"></i></div>
        <div class="stat-number"><?= (int) ($stats['en_attente'] ?? 0); ?></div>
        <div class="stat-label">En attente</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon"><i class="fas fa-times-circle" style="color:#dc3545; font-size:24px;"></i></div>
        <div class="stat-number"><?= (int) ($stats['rejete'] ?? 0); ?></div>
        <div class="stat-label">Rejetés</div>
    </article>
</section>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mes mémoires</h3>
    </div>
    <?php if (!empty($memoires)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Thème</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memoires as $memoire): ?>
                        <tr>
                            <td><?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php $statut_id = $memoire['id_statut'] ?? 1; require APP_PATH . '/views/partials/status_badge.php'; ?></td>
                            <td><?= (new DateTime($memoire['date_depot']))->format('d/m/Y'); ?></td>
                            <td><a href="<?= BASE_URL ?>/memoires/<?= (int) $memoire['id_memoire']; ?>" class="btn btn-primary btn-sm">Voir</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="uatm-page-subtitle">Vous n'avez pas encore déposé de mémoire.</p>
        <p><a href="<?= BASE_URL ?>/memoires/creer" class="btn btn-primary">Déposer un mémoire</a></p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Tableau de bord Étudiant';
$pageSubtitle = 'Visualisez l’état de validation de vos documents et accédez à votre dépôt.';
$page_css = [BASE_URL . '/assets/css/dashboard.css'];
require_once APP_PATH . '/views/layouts/main.php';

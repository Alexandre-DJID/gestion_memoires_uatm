<?php
ob_start();
?>
<div class="welcome-banner">
    <div>
        <h2>Bonjour Professeur <?= htmlspecialchars($_SESSION['user_prenom'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
        <p>Voici les mémoires qui vous sont assignés pour évaluation.</p>
    </div>
    <div class="welcome-icon">🎓</div>
</div>

<section class="stats-grid">
    <article class="stat-card card">
        <div class="stat-icon">📌</div>
        <div class="stat-number"><?= count($memoires); ?></div>
        <div class="stat-label">Mémoires assignés</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon">📅</div>
        <div class="stat-number"><?= (int) ($stats['total'] ?? 0); ?></div>
        <div class="stat-label">Total</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon">⏳</div>
        <div class="stat-number"><?= (int) ($stats['en_attente'] ?? 0); ?></div>
        <div class="stat-label">En attente</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon">✅</div>
        <div class="stat-number"><?= (int) ($stats['valide'] ?? 0); ?></div>
        <div class="stat-label">Validés</div>
    </article>
</section>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mes évaluations</h3>
    </div>
    <?php if (!empty($memoires)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Thème</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memoires as $memoire): ?>
                        <tr>
                            <td><?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($memoire['role_jury'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php $statut_id = $memoire['id_statut'] ?? 1; require APP_PATH . '/views/partials/status_badge.php'; ?></td>
                            <td><?= (new DateTime($memoire['date_assignation'] ?? $memoire['date_depot']))->format('d/m/Y'); ?></td>
                            <td><a href="/gestion_memoires_uatm/public/memoires/<?= (int) $memoire['id_memoire']; ?>" class="btn btn-primary btn-sm">Voir</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="uatm-page-subtitle">Aucun mémoire ne vous a été assigné.</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Tableau de bord Professeur';
$pageSubtitle = 'Suivez vos continuations et évaluations en cours.';
$page_css = ['/gestion_memoires_uatm/public/assets/css/dashboard.css'];
require_once APP_PATH . '/views/layouts/main.php';

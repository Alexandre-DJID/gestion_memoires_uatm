<?php
ob_start();
?>
<div class="welcome-banner">
    <div>
        <h2>Bienvenue, <?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8'); ?></h2>
        <p>Vue synthétique de la Direction — suivi global des mémoires.</p>
    </div>
    <div class="welcome-icon">📊</div>
</div>

<section class="stats-grid">
    <article class="stat-card card">
        <div class="stat-icon">🏁</div>
        <div class="stat-number"><?= (int) ($stats['total'] ?? 0); ?></div>
        <div class="stat-label">Total dépôts</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon">✅</div>
        <div class="stat-number"><?= (int) ($stats['valide'] ?? 0); ?></div>
        <div class="stat-label">Mémoire validés</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon">⏳</div>
        <div class="stat-number"><?= (int) ($stats['en_attente'] ?? 0); ?></div>
        <div class="stat-label">En attente</div>
    </article>
    <article class="stat-card card">
        <div class="stat-icon">🚫</div>
        <div class="stat-number"><?= (int) ($stats['rejete'] ?? 0); ?></div>
        <div class="stat-label">Rejetés</div>
    </article>
</section>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Activité récente</h3>
    </div>
    <?php if (!empty($recents)): ?>
        <div class="recent-list">
            <?php foreach ($recents as $memoire): ?>
                <article class="recent-item">
                    <div class="recent-icon">📄</div>
                    <div class="recent-info">
                        <strong><?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <span><?= htmlspecialchars($memoire['prenom'] . ' ' . $memoire['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="recent-date"><?= (new DateTime($memoire['date_depot']))->format('d/m/Y'); ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="uatm-page-subtitle">Aucune activité récente.</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Tableau de bord Direction';
$pageSubtitle = 'Vue globale des indicateurs et des derniers dépôts.';
$page_css = ['/gestion_memoires_uatm/public/assets/css/dashboard.css'];
require_once APP_PATH . '/views/layouts/main.php';

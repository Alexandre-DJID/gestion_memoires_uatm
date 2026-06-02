<?php
/**
 * Vue Tableau de Bord (charte institutionnelle)
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/gestion_memoires_uatm/public/css/institutional.css">
</head>
<body class="uatm-body">
    <?php require_once APP_PATH . '/views/partials/header_institutional.php'; ?>

    <main class="uatm-container">
        <?php require_once APP_PATH . '/views/partials/flash_messages.php'; ?>

        <header class="uatm-page-header">
            <h1 class="uatm-page-title">Tableau de Bord</h1>
            <p class="uatm-page-subtitle">Vue d'ensemble statistique de la plateforme de gestion des mémoires.</p>
        </header>

        <section class="uatm-kpi-grid" aria-label="Indicateurs clés">
            <div class="uatm-kpi">
                <div class="uatm-kpi__label">Total dépôts</div>
                <div class="uatm-kpi__value"><?php echo (int) ($stats['total'] ?? 0); ?></div>
            </div>
            <div class="uatm-kpi">
                <div class="uatm-kpi__label">Validés</div>
                <div class="uatm-kpi__value"><?php echo (int) ($stats['valide'] ?? 0); ?></div>
            </div>
            <div class="uatm-kpi">
                <div class="uatm-kpi__label">En attente</div>
                <div class="uatm-kpi__value"><?php echo (int) ($stats['en_attente'] ?? 0); ?></div>
            </div>
            <div class="uatm-kpi">
                <div class="uatm-kpi__label">Brouillons</div>
                <div class="uatm-kpi__value"><?php echo (int) ($stats['brouillon'] ?? 0); ?></div>
            </div>
            <div class="uatm-kpi">
                <div class="uatm-kpi__label">Rejetés</div>
                <div class="uatm-kpi__value"><?php echo (int) ($stats['rejete'] ?? 0); ?></div>
            </div>
        </section>

        <section class="uatm-card">
            <h2 class="uatm-page-title" style="font-size:1.15rem;margin-bottom:16px;">Activité récente</h2>

            <?php if (!empty($recents)): ?>
                <div class="uatm-table-wrap" style="border:none;box-shadow:none;">
                    <table class="uatm-table">
                        <thead>
                            <tr>
                                <th>Thème</th>
                                <th>Auteur</th>
                                <th>Statut</th>
                                <th>Date de dépôt</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recents as $memoire): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars(substr($memoire['theme'], 0, 50), ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <?php if (strlen($memoire['theme']) > 50): ?>...<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (!empty($memoire['prenom']) || !empty($memoire['nom'])) {
                                            echo htmlspecialchars(trim($memoire['prenom'] . ' ' . $memoire['nom']), ENT_QUOTES, 'UTF-8');
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php $statut_id = $memoire['id_statut'] ?? 1; require APP_PATH . '/views/partials/status_badge.php'; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $date = new DateTime($memoire['date_depot']);
                                        echo htmlspecialchars($date->format('d/m/Y'), ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                    <td>
                                        <a href="/gestion_memoires_uatm/public/memoires/<?php echo (int) $memoire['id_memoire']; ?>"
                                           class="uatm-btn uatm-btn-secondary">Voir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="uatm-page-subtitle">Aucun mémoire déposé pour le moment.</p>
            <?php endif; ?>
        </section>

        <section class="uatm-card">
            <h2 class="uatm-page-title" style="font-size:1.15rem;margin-bottom:16px;">Accès rapide</h2>
            <div class="uatm-quick-grid">
                <a href="/gestion_memoires_uatm/public/memoires" class="uatm-quick-link">Liste des mémoires</a>
                <?php if (($user_type ?? '') === 'professeur'): ?>
                    <a href="/gestion_memoires_uatm/public/mes-evaluations" class="uatm-quick-link">Mes évaluations</a>
                <?php endif; ?>
                <a href="/gestion_memoires_uatm/public/mes-depots" class="uatm-quick-link">Mes dépôts</a>
                <a href="/gestion_memoires_uatm/public/memoires/creer" class="uatm-quick-link">Déposer un mémoire</a>
                <a href="/gestion_memoires_uatm/public/profil" class="uatm-quick-link">Mon profil</a>
            </div>
        </section>
    </main>
</body>
</html>

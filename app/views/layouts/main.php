<?php
$pageTitle = $pageTitle ?? APP_NAME;
$pageSubtitle = $pageSubtitle ?? '';
$page_css = $page_css ?? [];
$page_js = $page_js ?? [];
$baseUrl = '/gestion_memoires_uatm/public';

function activeClass(string $path): string
{
    $current = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($current, $path) !== false ? 'active' : '';
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css">
    <?php foreach ($page_css as $css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>
</head>
<body class="dashboard-body">
    <?php require_once APP_PATH . '/views/partials/header_institutional.php'; ?>

    <div class="layout-with-sidebar">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-section-title">Navigation</div>
            <a href="<?= $baseUrl ?>/dashboard" class="<?= activeClass('/dashboard'); ?>">Tableau de bord</a>
            <a href="<?= $baseUrl ?>/memoires" class="<?= activeClass('/memoires'); ?>">Mémoires</a>
            <a href="<?= $baseUrl ?>/mes-depots" class="<?= activeClass('/mes-depots'); ?>">Mes dépôts</a>
            <a href="<?= $baseUrl ?>/mes-evaluations" class="<?= activeClass('/mes-evaluations'); ?>">Mes évaluations</a>
            <a href="<?= $baseUrl ?>/memoires/creer" class="<?= activeClass('/memoires/creer'); ?>">Déposer un mémoire</a>
            <a href="<?= $baseUrl ?>/profil" class="<?= activeClass('/profil'); ?>">Mon profil</a>
            <div class="sidebar-logout">
                <a href="<?= $baseUrl ?>/logout" class="btn btn-ghost btn-sm">Déconnexion</a>
            </div>
        </aside>

        <main class="main-content">
            <section class="page-header">
                <div>
                    <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <?php if (!empty($pageSubtitle)): ?>
                        <p><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <?php require_once APP_PATH . '/views/partials/flash_messages.php'; ?>
            <?= $content ?>
        </main>
    </div>

    <footer class="footer">© 2026 UATM — Système de Gestion des Mémoires</footer>

    <?php foreach ($page_js as $script): ?>
        <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endforeach; ?>
    <script>
        document.querySelector('.menu-toggle')?.addEventListener('click', function () {
            document.getElementById('sidebar')?.classList.toggle('open');
        });
    </script>
</body>
</html>

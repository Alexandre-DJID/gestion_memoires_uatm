<?php
/**
 * En-tête institutionnel UATM (logo + utilisateur)
 */
$header_user_nom = $_SESSION['user_nom'] ?? '';
$header_user_prenom = $_SESSION['user_prenom'] ?? '';
$header_user_type = $_SESSION['user_type'] ?? '';

$roles_label = [
    'etudiant' => 'Étudiant',
    'professeur' => 'Professeur',
    'de' => 'Direction',
];
$role_display = $roles_label[$header_user_type] ?? $header_user_type;
?>
<nav class="navbar">
    <button class="menu-toggle" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <div class="navbar-brand">
        <img src="<?= BASE_URL ?>/assets/images/logo-uatm.png" alt="UATM" />
        <div class="brand-text">UATM <span>Gestion des Mémoires</span></div>
    </div>
    <div class="navbar-user">
        <span class="badge badge-info"><?= htmlspecialchars($role_display, ENT_QUOTES, 'UTF-8'); ?></span>
        <strong class="user-name"><?= htmlspecialchars(trim($header_user_prenom . ' ' . $header_user_nom), ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>
</nav>

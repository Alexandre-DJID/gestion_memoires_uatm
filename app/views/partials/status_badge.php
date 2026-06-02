<?php
/** @var int $statut_id */
$statut_id = (int) ($statut_id ?? 1);
$labels = [
    1 => ['Brouillon', 'badge-warning'],
    2 => ['En attente', 'badge-info'],
    3 => ['Validé', 'badge-success'],
    4 => ['Rejeté', 'badge-danger'],
    5 => ['Archivé', 'badge'],
];
[$label, $class] = $labels[$statut_id] ?? ['Inconnu', 'badge'];
?>
<span class="badge <?= htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
</span>

<?php
/**
 * Vue Liste des Mémoires
 */
ob_start();
?>
<div class="card">
    <form method="GET" action="<?= BASE_URL ?>/memoires" class="search-bar">
        <input type="text" name="q" class="form-control" placeholder="Rechercher par thème, étudiant ou enseignant..."
               value="<?= htmlspecialchars($keyword ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <?php if (($_SESSION['user_type'] ?? '') === 'de'): ?>
            <select name="statut" class="form-control" style="width:200px;">
                <option value="">Tous les statuts</option>
                <?php foreach ($liste_statuts ?? [] as $s): ?>
                    <option value="<?= (int) $s['id_statut']; ?>" <?= (isset($statut) && (string) $statut === (string) $s['id_statut']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($s['libelle'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <select name="filiere" class="form-control" style="width:200px;">
            <option value="">Toutes les filières</option>
            <?php foreach ($filieres ?? [] as $f): ?>
                <option value="<?= htmlspecialchars($f['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?= (isset($filiere) && $filiere === ($f['libelle'] ?? '')) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($f['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="annee" class="form-control" style="width:200px;">
            <option value="">Toutes les années</option>
            <?php $optionsAnnee = ['2023', '2024', '2025', '2026']; ?>
            <?php foreach ($optionsAnnee as $option): ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?= (isset($annee) && $annee === $option) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="centre" class="form-control" style="width:200px;">
            <option value="">Tous les centres</option>
            <?php foreach ($centres ?? [] as $c): ?>
                <option value="<?= htmlspecialchars($c['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?= (isset($centre) && $centre === ($c['libelle'] ?? '')) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($c['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <?php if (!empty($keyword) || ($statut ?? '') !== '' || ($filiere ?? '') !== '' || ($annee ?? '') !== '' || ($centre ?? '') !== ''): ?>
            <a href="<?= BASE_URL ?>/memoires" class="btn btn-outline">Réinitialiser</a>
        <?php endif; ?>
    </form>
</div>

<?php if (!empty($memoires)): ?>
    <div class="table-wrapper card">
        <table>
            <thead>
                <tr>
                    <th>Thème</th>
                    <th>Résumé</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($memoires as $memoire): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($memoire['theme'], 0, 60), ENT_QUOTES, 'UTF-8'); ?><?php if (strlen($memoire['theme']) > 60): ?>...<?php endif; ?></td>
                        <td><?= htmlspecialchars(substr($memoire['resume'], 0, 80), ENT_QUOTES, 'UTF-8'); ?><?php if (strlen($memoire['resume']) > 80): ?>...<?php endif; ?></td>
                        <td><?php $statut_id = $memoire['id_statut'] ?? 1; require APP_PATH . '/views/partials/status_badge.php'; ?></td>
                        <td><?= (new DateTime($memoire['date_depot']))->format('d/m/Y'); ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/memoires/<?= (int) $memoire['id_memoire']; ?>" class="btn btn-outline btn-sm">Consulter</a>
                            <?php
                            $is_de = (($_SESSION['user_type'] ?? '') === 'de');
                            $is_auteur = isset($_SESSION['user_id'], $memoire['id_auteur']) && (int) $_SESSION['user_id'] === (int) $memoire['id_auteur'];
                            $is_prof_assigne = (($_SESSION['user_type'] ?? '') === 'professeur') && Memoire::isProfAssigne((int) $memoire['id_memoire'], (int) $_SESSION['user_id']);
                            $can_access_file = $is_de || $is_auteur || $is_prof_assigne;
                            ?>
                            <?php if ($can_access_file): ?>
                                <a href="<?= BASE_URL ?>/memoires/telecharger/<?= (int) $memoire['id_memoire']; ?>" class="btn btn-primary btn-sm">Télécharger</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($total_pages ?? 0) > 1): ?>
        <div class="card" style="margin-top:16px;">
            <div class="flex flex-between" style="flex-wrap:wrap;gap:8px;">
                <span>Page <?= (int) ($page ?? 1); ?> sur <?= (int) $total_pages; ?> (<?= (int) ($total_items ?? 0); ?> résultat<?= ($total_items ?? 0) > 1 ? 's' : ''; ?>)</span>
                <div class="flex gap-md" style="align-items:center;flex-wrap:wrap;">
                    <?php if (($page ?? 1) > 1): ?>
                        <a href="<?= htmlspecialchars($paginationQuery($page - 1), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline btn-sm">Précédent</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?= htmlspecialchars($paginationQuery($i), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-ghost btn-sm<?= $i === ($page ?? 1) ? ' active' : ''; ?>"><?= $i; ?></a>
                    <?php endfor; ?>
                    <?php if (($page ?? 1) < $total_pages): ?>
                        <a href="<?= htmlspecialchars($paginationQuery($page + 1), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline btn-sm">Suivant</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="card">
        <p><strong><?= $filtres_actifs ? 'Aucun résultat' : 'Aucun mémoire disponible'; ?></strong></p>
        <p>
            <?= $filtres_actifs ? 'Aucun mémoire ne correspond à vos critères de recherche.' : 'La plateforme ne contient actuellement aucun mémoire déposé.'; ?>
        </p>
        <?php if ($filtres_actifs): ?>
            <a href="<?= BASE_URL ?>/memoires" class="btn btn-outline">Réinitialiser les filtres</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Liste des mémoires';
$pageSubtitle = 'Consultation et gestion des mémoires déposés sur la plateforme.';
$page_css = [BASE_URL . '/assets/css/consulter.css'];
require_once APP_PATH . '/views/layouts/main.php';

<?php
/**
 * Vue des paramètres de la Direction des Études
 */
ob_start();
?>
<div class="card">
    <div class="flex flex-between" style="align-items:center; gap:16px;">
        <div>
            <h2>Paramètres administratifs</h2>
            <p>Gérez les filières de l'UATM depuis ce panneau.</p>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-header"><h3 class="card-title">Filières enregistrées</h3></div>
        <?php if (!empty($filieres)): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nom de la filière</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filieres as $filiere): ?>
                            <tr>
                                <td><?= htmlspecialchars($filiere['libelle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/filiere/delete/<?= (int) $filiere['id_filiere']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette filière ?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Aucune filière enregistrée pour le moment.</p>
        <?php endif; ?>
    </div>

    <div class="card" style="margin-top:20px; max-width:520px;">
        <div class="card-header"><h3 class="card-title">Ajouter une nouvelle filière</h3></div>
        <form method="POST" action="<?= BASE_URL ?>/admin/filiere/add" style="margin-top:16px;">
            <label class="form-label" for="libelle">Nom de la filière</label>
            <input type="text" id="libelle" name="libelle" class="form-control" placeholder="Ex. Génie Informatique" required>
            <button type="submit" class="btn btn-primary" style="margin-top:16px;">
                <i class="fas fa-plus"></i> Ajouter la filière
            </button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Paramètres DE';
$pageSubtitle = 'Gestion des filières et des configurations de la Direction des Études.';
$page_css = [BASE_URL . '/assets/css/consulter.css'];
require_once APP_PATH . '/views/layouts/main.php';

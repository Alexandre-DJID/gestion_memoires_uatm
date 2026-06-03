<?php
/**
 * Vue d'import en masse de mémoires (Admin DE)
 */
ob_start();
?>
<div class="card">
    <h2>Import en masse de mémoires</h2>
    <p>Importez des fichiers PDF de mémoires en lot. Les fichiers seront créés avec le statut "Validé".</p>

    <div class="card" style="margin-top:20px; background:#fffacd; padding:16px; border-left:4px solid #ffc107;">
        <strong>Nomenclature obligatoire :</strong>
        <p style="margin-top:8px; font-family:monospace;">
            <code>[Nom]_[Prénom]_[Centre]_[Filière]_[Thème].pdf</code>
        </p>
        <p style="margin-top:8px; font-size:14px;">
            Le fichier doit contenir exactement 5 segments séparés par des tirets bas. Le 5ème segment (avant .pdf) sera utilisé comme thème du mémoire.<br>
            <strong>Exemple :</strong> <code>Doe_John_Calavi_SIL_Conception_Application_Web.pdf</code><br>
            → Thème inséré : "Conception_Application_Web"
        </p>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/memoires/import-process" enctype="multipart/form-data" style="margin-top:20px;">
        <label class="form-label" for="memoires">Sélectionnez les fichiers PDF à importer *</label>
        <input type="file" id="memoires" name="memoires[]" class="form-control" accept=".pdf" multiple required>
        <p style="font-size:14px; color:#666; margin-top:8px;">Vous pouvez sélectionner plusieurs fichiers à la fois.</p>

        <div style="margin-top:20px;">
            <label class="form-label" for="id_statut">Statut des mémoires importés</label>
            <select id="id_statut" name="id_statut" class="form-control" style="max-width:300px;">
                <option value="3">Validé</option>
                <option value="5">Archivé</option>
                <option value="1">Brouillon</option>
            </select>
        </div>

        <div style="margin-top:24px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Importer les mémoires
            </button>
            <a href="<?= BASE_URL ?>/admin/parametres" class="btn btn-outline" style="margin-left:8px;">Annuler</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Import en masse de mémoires';
$pageSubtitle = 'Téléchargez plusieurs mémoires à la fois.';
$page_css = [BASE_URL . '/assets/css/consulter.css'];
require_once APP_PATH . '/views/layouts/main.php';

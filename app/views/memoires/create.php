<?php
/**
 * Vue Créer un Mémoire
 */
ob_start();
?>
<div class="card" style="max-width:760px; margin:auto;">
    <form method="POST" action="<?= BASE_URL ?>/memoires/creer" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label" for="theme">Titre du mémoire *</label>
            <input id="theme" name="theme" type="text" class="form-control" required maxlength="512" placeholder="Thème de votre mémoire">
        </div>

        <div class="form-group">
            <label class="form-label" for="resume">Résumé *</label>
            <textarea id="resume" name="resume" class="form-control" required placeholder="Résumé du mémoire"></textarea>
        </div>

        <div class="grid-2" style="gap:16px;">
            <div class="form-group">
                <label class="form-label" for="filiere">Filière</label>
                <select id="filiere" name="filiere" class="form-control">
                    <option value="">Sélectionnez une filière</option>
                    <?php foreach ($filieres ?? [] as $f): ?>
                        <option value="<?= htmlspecialchars($f['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                            <?php if (!empty($inscription) && isset($inscription['filiere_libelle']) && $inscription['filiere_libelle'] === $f['libelle']): ?>selected<?php endif; ?>>
                            <?= htmlspecialchars($f['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#666;">Pré-rempli selon votre inscription.</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="centre">Centre</label>
                <select id="centre" name="centre" class="form-control">
                    <option value="">Sélectionnez un centre</option>
                    <?php foreach ($centres ?? [] as $c): ?>
                        <option value="<?= htmlspecialchars($c['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                            <?php if (!empty($inscription) && isset($inscription['centre_libelle']) && $inscription['centre_libelle'] === $c['libelle']): ?>selected<?php endif; ?>>
                            <?= htmlspecialchars($c['libelle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#666;">Pré-rempli selon votre inscription.</small>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="id_maitre_memoire">Maître de mémoire *</label>
            <select id="id_maitre_memoire" name="id_maitre_memoire" class="form-control" required>
                <option value="">Sélectionnez un professeur</option>
                <?php foreach ($professeurs ?? [] as $prof): ?>
                    <option value="<?= (int) $prof['id_user']; ?>"><?= htmlspecialchars($prof['prenom'] . ' ' . $prof['nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="fichier">Fichier (PDF, DOC, DOCX — max. 50 Mo) *</label>
            <input id="fichier" name="fichier" type="file" class="form-control" accept=".pdf,.doc,.docx" required>
        </div>

        <div class="flex gap-md" style="flex-wrap:wrap; margin-top:16px;">
            <button type="submit" class="btn btn-primary">Déposer</button>
            <a href="<?= BASE_URL ?>/memoires" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Déposer un mémoire';
$pageSubtitle = 'Soumettez votre document et désignez votre maître de mémoire.';
$page_css = [BASE_URL . '/assets/css/upload.css'];
require_once APP_PATH . '/views/layouts/main.php';

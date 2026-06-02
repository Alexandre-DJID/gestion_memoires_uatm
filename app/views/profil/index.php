<?php
/**
 * Vue Profil
 */
ob_start();
?>
<div class="grid-2" style="gap:24px;">
    <div class="card">
        <h2 class="card-title">Informations personnelles</h2>
        <form method="POST" action="/gestion_memoires_uatm/public/profil/update-info">
            <div class="form-group">
                <label class="form-label" for="nom">Nom</label>
                <input id="nom" name="nom" type="text" class="form-control" required maxlength="100" value="<?= htmlspecialchars($utilisateur['nom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="prenom">Prénom</label>
                <input id="prenom" name="prenom" type="text" class="form-control" required maxlength="100" value="<?= htmlspecialchars($utilisateur['prenom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control" readonly value="<?= htmlspecialchars($utilisateur['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>

    <div class="card">
        <h2 class="card-title">Sécurité du compte</h2>
        <form method="POST" action="/gestion_memoires_uatm/public/profil/update-password">
            <div class="form-group">
                <label class="form-label" for="old_password">Ancien mot de passe</label>
                <input id="old_password" name="old_password" type="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="new_password">Nouveau mot de passe</label>
                <input id="new_password" name="new_password" type="password" class="form-control" required minlength="6" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirmer le mot de passe</label>
                <input id="confirm_password" name="confirm_password" type="password" class="form-control" required minlength="6" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Mon profil';
$pageSubtitle = 'Gérez vos informations personnelles et votre mot de passe.';
require_once APP_PATH . '/views/layouts/main.php';

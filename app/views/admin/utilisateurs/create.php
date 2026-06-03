<?php
/**
 * Vue de création d'un nouvel utilisateur (Admin DE)
 */
ob_start();
?>
<div class="card">
    <h2>Ajouter un nouvel utilisateur</h2>
    <p>Complétez le formulaire pour créer un compte pour un étudiant, professeur ou membre de la direction.</p>

    <form method="POST" action="<?= BASE_URL ?>/admin/utilisateurs/store" style="max-width:720px; margin-top:20px;">
        <!-- Champs généraux -->
        <div class="grid-2" style="gap:16px;">
            <div>
                <label class="form-label" for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" class="form-control" required>
            </div>
            <div>
                <label class="form-label" for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" class="form-control" required>
            </div>
        </div>

        <div style="margin-top:16px;">
            <label class="form-label" for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div style="margin-top:16px;">
            <label class="form-label" for="mot_de_passe">Mot de passe *</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required>
        </div>

        <div style="margin-top:16px;">
            <label class="form-label" for="type_utilisateur">Type d'utilisateur *</label>
            <select id="type_utilisateur" name="type_utilisateur" class="form-control" required onchange="updateFormFields()">
                <option value="">Sélectionnez un type</option>
                <option value="etudiant">Étudiant</option>
                <option value="professeur">Professeur</option>
                <option value="de">Direction des Études</option>
            </select>
        </div>

        <!-- Champs spécifiques : Étudiant -->
        <div id="etudiant-fields" style="display:none; margin-top:20px; padding:16px; background:#f5f5f5; border-radius:8px;">
            <h3 style="margin-top:0;">Informations d'inscription (Étudiant)</h3>

            <div style="margin-top:12px;">
                <label class="form-label" for="matricule">Matricule *</label>
                <input type="text" id="matricule" name="matricule" class="form-control" placeholder="Ex. ETD2024001">
            </div>

            <div class="grid-2" style="gap:16px; margin-top:12px;">
                <div>
                    <label class="form-label" for="id_filiere">Filière *</label>
                    <select id="id_filiere" name="id_filiere" class="form-control">
                        <option value="">Sélectionnez une filière</option>
                        <?php foreach ($filieres ?? [] as $f): ?>
                            <option value="<?= (int) $f['id_filiere']; ?>"><?= htmlspecialchars($f['libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="id_centre">Centre *</label>
                    <select id="id_centre" name="id_centre" class="form-control">
                        <option value="">Sélectionnez un centre</option>
                        <?php foreach ($centres ?? [] as $c): ?>
                            <option value="<?= (int) $c['id_centre']; ?>"><?= htmlspecialchars($c['libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid-2" style="gap:16px; margin-top:12px;">
                <div>
                    <label class="form-label" for="id_annee">Année académique *</label>
                    <select id="id_annee" name="id_annee" class="form-control">
                        <option value="">Sélectionnez une année</option>
                        <?php foreach ($annees ?? [] as $a): ?>
                            <option value="<?= (int) $a['id_annee']; ?>"><?= (int) $a['annee']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="id_niveau">Niveau d'étude *</label>
                    <select id="id_niveau" name="id_niveau" class="form-control">
                        <option value="">Sélectionnez un niveau</option>
                        <?php foreach ($niveaux ?? [] as $n): ?>
                            <option value="<?= (int) $n['id_niveau']; ?>"><?= htmlspecialchars($n['libelle'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Champs spécifiques : Professeur -->
        <div id="professeur-fields" style="display:none; margin-top:20px; padding:16px; background:#f5f5f5; border-radius:8px;">
            <h3 style="margin-top:0;">Informations du Professeur</h3>
            <div style="margin-top:12px;">
                <label class="form-label" for="grade">Grade</label>
                <input type="text" id="grade" name="grade" class="form-control" placeholder="Ex. Docteur, Maître-assistant, Assistant">
            </div>
        </div>

        <!-- Boutons -->
        <div style="margin-top:24px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Créer l'utilisateur
            </button>
            <a href="<?= BASE_URL ?>/admin/parametres" class="btn btn-outline" style="margin-left:8px;">Annuler</a>
        </div>
    </form>
</div>

<script>
function updateFormFields() {
    const type = document.getElementById('type_utilisateur').value;
    document.getElementById('etudiant-fields').style.display = (type === 'etudiant') ? 'block' : 'none';
    document.getElementById('professeur-fields').style.display = (type === 'professeur') ? 'block' : 'none';

    // Marquer les champs comme required/optional
    const etudiantInputs = ['matricule', 'id_filiere', 'id_centre', 'id_annee', 'id_niveau'];
    etudiantInputs.forEach(id => document.getElementById(id).required = (type === 'etudiant'));

    const professeurInputs = ['grade'];
    professeurInputs.forEach(id => document.getElementById(id).required = (type === 'professeur'));
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Ajouter un utilisateur';
$pageSubtitle = 'Créez un nouveau compte pour un étudiant, professeur ou membre de la direction.';
$page_css = [BASE_URL . '/assets/css/consulter.css'];
require_once APP_PATH . '/views/layouts/main.php';

<?php
/**
 * Vue de Connexion (charte institutionnelle)
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/institutional.css">
    <style>
        body.uatm-login {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            background: var(--uatm-surface);
            border: 1px solid var(--uatm-border);
            border-radius: var(--uatm-radius);
            padding: 36px 32px;
            box-shadow: var(--uatm-shadow);
        }
        .login-logo { display: block; height: 72px; margin: 0 auto 24px; }
        .login-title { text-align: center; font-size: 1.25rem; font-weight: 600; color: var(--uatm-text); margin-bottom: 4px; }
        .login-subtitle { text-align: center; font-size: 0.85rem; color: var(--uatm-text-muted); margin-bottom: 28px; }
        .login-field { margin-bottom: 16px; }
        .login-field label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--uatm-text); }
        .login-error { background: #FCE8EA; border: 1px solid var(--uatm-accent); color: var(--uatm-accent); padding: 12px; margin-bottom: 16px; font-size: 0.9rem; border-radius: var(--uatm-radius); }
        .login-note { margin-top: 20px; padding: 12px; background: #F4F4F4; border: 1px solid var(--uatm-border); font-size: 0.8rem; color: var(--uatm-text-muted); border-radius: var(--uatm-radius); }
    </style>
</head>
<body class="uatm-body uatm-login">
    <div class="login-box">
        <img src="<?= BASE_URL ?>/assets/images/logo-uatm.png" alt="UATM" class="login-logo">
        <h1 class="login-title">Connexion</h1>
        <p class="login-subtitle"><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></p>

        <?php if (!empty($error)): ?>
            <div class="login-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/login" method="POST">
            <div class="login-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="uatm-input" style="width:100%;" placeholder="votre@email.com" required autofocus>
            </div>
            <div class="login-field">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="uatm-input" style="width:100%;" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="uatm-btn uatm-btn-primary" style="width:100%;margin-top:8px;">Se connecter</button>
        </form>

        <div class="login-note">
            Utilisez vos identifiants institutionnels fournis par l'établissement.
        </div>
    </div>
</body>
</html>

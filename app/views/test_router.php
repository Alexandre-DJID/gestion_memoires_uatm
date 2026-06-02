<?php
/**
 * ============================================================================
 * Vue de Test du Routeur
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test du Routeur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 60px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            width: 100%;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            font-size: 2em;
            text-align: center;
        }
        
        .success-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            width: 100%;
            font-size: 1.1em;
        }
        
        .message {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            font-size: 1em;
            color: #065f46;
        }
        
        .details {
            background: #f8fafc;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .detail-section {
            margin-bottom: 25px;
        }
        
        .detail-section:last-child {
            margin-bottom: 0;
        }
        
        .detail-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 12px;
            font-size: 1.1em;
        }
        
        .detail-list {
            list-style: none;
            margin-left: 0;
        }
        
        .detail-list li {
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
            color: #666;
        }
        
        .detail-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }
        
        .code-block {
            background: #1f2937;
            color: #e5e7eb;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            overflow-x: auto;
            margin-top: 10px;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e8ecff;
            color: #667eea;
        }
        
        .btn-secondary:hover {
            background: #d0deff;
            transform: translateY(-2px);
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 0.9em;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Test du Routeur</h1>
        
        <?php if ($routerWorks): ?>
            <div class="success-badge">✓ Succès - Routeur Fonctionnel</div>
            
            <div class="message">
                <strong>Excellent !</strong> <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <div class="details">
            <div class="detail-section">
                <div class="detail-title">Infrastructure Vérifiée</div>
                <ul class="detail-list">
                    <li>Front Controller (public/index.php) chargé avec succès</li>
                    <li>Configuration globale (config/app.php) activée</li>
                    <li>Configuration base de données (config/database.php) activée</li>
                    <li>Classe Router (core/Router.php) instantiée</li>
                    <li>Contrôleur HomeController appelé</li>
                    <li>Vue render.php affichée correctement</li>
                </ul>
            </div>
            
            <div class="detail-section">
                <div class="detail-title">Informations Système</div>
                <ul class="detail-list">
                    <li><strong>Version PHP:</strong> <?php echo phpversion(); ?></li>
                    <li><strong>Mode:</strong> <?php echo APP_DEBUG ? '🔧 Développement (APP_DEBUG = true)' : '🚀 Production (APP_DEBUG = false)'; ?></li>
                    <li><strong>URL de base:</strong> <?php echo htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>Session:</strong> <?php echo isset($_SESSION) ? '✓ Active' : '✗ Inactive'; ?></li>
                </ul>
            </div>
            
            <div class="detail-section">
                <div class="detail-title">Prochaines Étapes</div>
                <ul class="detail-list">
                    <li>Créer les modèles d'accès aux données (app/models/)</li>
                    <li>Implémenter les contrôleurs métier (app/controllers/)</li>
                    <li>Développer les vues HTML (app/views/)</li>
                    <li>Enregistrer les routes dans public/index.php</li>
                    <li>Tester l'authentification et l'autorisation</li>
                </ul>
            </div>
            
            <div class="detail-section">
                <div class="detail-title">Exemple de Registrage de Route</div>
                <div class="code-block">
$router->get('/', 'HomeController@index');<br>
$router->get('/memoires', 'MemoireController@lister');<br>
$router->get('/memoires/:id', 'MemoireController@afficher');<br>
$router->post('/memoires/creer', 'MemoireController@creer');
                </div>
            </div>
        </div>
        
        <div class="buttons">
            <a href="<?php echo APP_URL; ?>" class="btn btn-primary">← Retour à l'Accueil</a>
            <a href="<?php echo APP_URL; ?>/docs" class="btn btn-secondary">Documentation</a>
        </div>
        
        <div class="footer">
            <p>Plateforme de gestion des mémoires - UATM © 2026</p>
        </div>
    </div>
</body>
</html>

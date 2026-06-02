<?php
/**
 * ============================================================================
 * Vue d'Accueil
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
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
        }
        
        .container {
            background: white;
            padding: 60px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            text-align: center;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 40px;
        }
        
        .info {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e7ff;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #667eea;
        }
        
        .info-value {
            color: #666;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
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
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e7ff;
            font-size: 0.9em;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Bienvenue</h1>
        <p class="subtitle"><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></p>
        
        <div class="info">
            <div class="info-item">
                <span class="info-label">Version:</span>
                <span class="info-value"><?php echo APP_VERSION; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Mode:</span>
                <span class="info-value"><?php echo APP_DEBUG ? '🔧 Développement' : '🚀 Production'; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">PHP:</span>
                <span class="info-value"><?php echo phpversion(); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Session:</span>
                <span class="info-value"><?php echo session_id() ? '✅ Active' : '❌ Inactive'; ?></span>
            </div>
        </div>
        
        <p style="color: #666; margin-bottom: 30px;">
            L'infrastructure MVC est en place et fonctionnelle. Le système de routage est prêt pour le développement des contrôleurs et des modèles.
        </p>
        
        <div class="buttons">
            <a href="<?php echo APP_URL; ?>/test" class="btn btn-primary">Tester le Routeur</a>
            <a href="<?php echo APP_URL; ?>" class="btn btn-secondary">Accueil</a>
        </div>
        
        <div class="footer">
            <p>Plateforme de gestion des mémoires - UATM © 2026</p>
        </div>
    </div>
</body>
</html>

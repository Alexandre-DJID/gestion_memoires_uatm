<?php
/**
 * ============================================================================
 * Vue de Test - Informations Base de Données
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Base de Données - Infos</title>
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
            align-items: flex-start;
            color: #333;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 100%;
            margin-top: 20px;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            font-size: 1.8em;
        }
        
        h2 {
            color: #764ba2;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .info-card strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        
        .info-card span {
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        thead {
            background: #667eea;
            color: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:hover {
            background: #f8fafc;
        }
        
        .badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .badge.warning {
            background: #f59e0b;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 0.95em;
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
        }
        
        .btn-secondary {
            background: #e8ecff;
            color: #667eea;
        }
        
        .btn-secondary:hover {
            background: #d0deff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Test Base de Données - Informations</h1>
        
        <h2>Connexion à la Base de Données</h2>
        <div class="info-grid">
            <div class="info-card">
                <strong>Base de Données</strong>
                <span><?php echo htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="info-card">
                <strong>Host</strong>
                <span><?php echo htmlspecialchars(DB_HOST, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="info-card">
                <strong>Port</strong>
                <span><?php echo DB_PORT; ?></span>
            </div>
            <div class="info-card">
                <strong>Charset</strong>
                <span><?php echo htmlspecialchars(DB_CHARSET, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="info-card">
                <strong>Utilisateur</strong>
                <span><?php echo htmlspecialchars(DB_USER, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="info-card">
                <strong>Version Serveur</strong>
                <span><?php echo htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
        
        <h2>Tables Disponibles</h2>
        <p>Nombre de tables: <span class="badge"><?php echo count($tables); ?></span></p>
        <table>
            <thead>
                <tr>
                    <th>Nom de la Table</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $table): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($table['TABLE_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge">✓ OK</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Niveaux d'Études</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Libellé</th>
                    <th>Ordre</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($niveaux as $niveau): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($niveau['id_niveau'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($niveau['libelle'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($niveau['ordre'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Statuts de Mémoire</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Libellé</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statuts as $statut): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($statut['id_statut'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($statut['libelle'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($statut['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Rôles de Jury</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Libellé</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role['id_role'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($role['libelle'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($role['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="buttons">
            <a href="<?php echo APP_URL; ?>/db-test/queries" class="btn btn-primary">Tests de Requêtes →</a>
            <a href="<?php echo APP_URL; ?>/db-test/transactions" class="btn btn-primary">Tests de Transactions →</a>
            <a href="<?php echo APP_URL; ?>" class="btn btn-secondary">← Accueil</a>
        </div>
    </div>
</body>
</html>

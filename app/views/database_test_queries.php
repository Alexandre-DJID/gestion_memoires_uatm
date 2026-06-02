<?php
/**
 * ============================================================================
 * Vue de Test - Requêtes Base de Données
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Base de Données - Requêtes</title>
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
        
        .test-case {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .test-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .test-description {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.95em;
        }
        
        .code-block {
            background: #1f2937;
            color: #e5e7eb;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            overflow-x: auto;
            margin-bottom: 15px;
        }
        
        .result {
            background: #ecfdf5;
            border-left: 3px solid #10b981;
            padding: 12px;
            border-radius: 5px;
            color: #065f46;
        }
        
        .result.error {
            background: #fee2e2;
            border-left-color: #dc2626;
            color: #991b1b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        thead {
            background: #667eea;
            color: white;
        }
        
        tbody tr:hover {
            background: #f0f4ff;
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
        <h1>🔍 Test Base de Données - Requêtes</h1>
        
        <h2>Test 1: fetchOne (Récupère un seul résultat)</h2>
        <div class="test-case">
            <div class="test-title">✓ Méthode fetchOne()</div>
            <div class="test-description">Récupère le premier niveau d'études (id = 1)</div>
            <div class="code-block">
$db->fetchOne("SELECT * FROM niveau_etude WHERE id_niveau = ?", [1]);
            </div>
            <div class="result">
                <strong>Résultat:</strong><br>
                <?php if ($queryResults['firstLevel']): ?>
                    <table>
                        <thead>
                            <tr>
                                <?php foreach (array_keys($queryResults['firstLevel']) as $key): ?>
                                    <th><?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php foreach ($queryResults['firstLevel'] as $value): ?>
                                    <td><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <span>Aucun résultat</span>
                <?php endif; ?>
            </div>
        </div>
        
        <h2>Test 2: fetchAll (Récupère tous les résultats)</h2>
        <div class="test-case">
            <div class="test-title">✓ Méthode fetchAll()</div>
            <div class="test-description">Récupère tous les statuts de mémoire</div>
            <div class="code-block">
$db->fetchAll("SELECT * FROM statut_memoire");
            </div>
            <div class="result">
                <strong>Résultats (<?php echo count($queryResults['allStatuts']); ?> lignes):</strong><br>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($queryResults['allStatuts'][0]) as $key): ?>
                                <th><?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queryResults['allStatuts'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <h2>Test 3: execute (Requête préparée)</h2>
        <div class="test-case">
            <div class="test-title">✓ Méthode execute()</div>
            <div class="test-description">Compte le nombre de statuts avec une requête préparée</div>
            <div class="code-block">
$stmt = $db->execute("SELECT COUNT(*) as count FROM statut_memoire");
$count = $stmt->fetch();
            </div>
            <div class="result">
                <strong>Résultat:</strong><br>
                Nombre de statuts: <strong><?php echo htmlspecialchars($queryResults['count']['count'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        </div>
        
        <h2>✓ Avantages de la Classe Database</h2>
        <div class="test-case">
            <ul style="margin-left: 20px; line-height: 1.8;">
                <li><strong>Singleton:</strong> Une seule connexion pour toute l'application</li>
                <li><strong>PDO Sécurisé:</strong> ATTR_EMULATE_PREPARES = false (vrais préparés du serveur)</li>
                <li><strong>Mode Exception:</strong> ATTR_ERRMODE = ERRMODE_EXCEPTION (gestion globale des erreurs)</li>
                <li><strong>Fetch Associatif:</strong> ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC (tableaux clairs)</li>
                <li><strong>Transactions:</strong> beginTransaction(), commit(), rollback() supportées</li>
                <li><strong>Requêtes Rapides:</strong> fetchOne(), fetchAll(), execute() sont des raccourcis</li>
            </ul>
        </div>
        
        <div class="buttons">
            <a href="<?php echo APP_URL; ?>/db-test/info" class="btn btn-secondary">← Infos BD</a>
            <a href="<?php echo APP_URL; ?>/db-test/transactions" class="btn btn-primary">Tests Transactions →</a>
            <a href="<?php echo APP_URL; ?>" class="btn btn-secondary">← Accueil</a>
        </div>
    </div>
</body>
</html>

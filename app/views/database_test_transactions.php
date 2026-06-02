<?php
/**
 * ============================================================================
 * Vue de Test - Transactions Base de Données
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Base de Données - Transactions</title>
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
        
        .feature-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .feature-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        
        .feature-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .code-block {
            background: #1f2937;
            color: #e5e7eb;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .method-list {
            list-style: none;
            margin-left: 0;
        }
        
        .method-list li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #666;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .method-list li:last-child {
            border-bottom: none;
        }
        
        .method-list li::before {
            content: "→";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .method-list code {
            background: #f0f4ff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        
        .highlight {
            background: #fef3c7;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
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
        <h1>💳 Test Base de Données - Transactions</h1>
        
        <h2>Qu'est-ce qu'une Transaction ?</h2>
        <div class="feature-box">
            <p class="feature-description">
                Une transaction est un ensemble d'opérations de base de données qui doivent être exécutées comme une unité atomique.
                Soit toutes les opérations réussissent (COMMIT), soit aucune n'est appliquée (ROLLBACK).
            </p>
            <p class="feature-description">
                <span class="highlight">Exemple:</span> Lors d'une opération bancaire, vous devez débiter le compte A et créditer le compte B.
                Si l'une échoue, l'autre ne doit pas être exécutée.
            </p>
        </div>
        
        <h2>Méthodes de Transactions Disponibles</h2>
        <div class="feature-box">
            <ul class="method-list">
                <li>
                    <code>$db->beginTransaction()</code><br>
                    Commence une transaction (désactive l'auto-commit)
                </li>
                <li>
                    <code>$db->commit()</code><br>
                    Valide la transaction et applique tous les changements
                </li>
                <li>
                    <code>$db->rollback()</code><br>
                    Annule la transaction et rejette tous les changements
                </li>
            </ul>
        </div>
        
        <h2>Exemple: Déposer un Mémoire (Scénario Réel)</h2>
        <div class="feature-box">
            <div class="feature-title">✓ Cas d'Utilisation</div>
            <div class="feature-description">
                Quand un étudiant dépose un mémoire, deux opérations doivent être atomiques:
                <ol style="margin-left: 20px; margin-top: 10px;">
                    <li>Insérer le mémoire dans la table <code>memoire</code></li>
                    <li>Insérer l'association dans la table <code>deposer</code></li>
                </ol>
                <p style="margin-top: 10px;">
                    Si l'une échoue, l'autre ne doit pas être appliquée. Sinon, on aurait:
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>Un mémoire orphelin sans dépositaire</li>
                        <li>Ou une association sans mémoire correspondant</li>
                    </ul>
                </p>
            </div>
            
            <div class="code-block">
$db = Database::getInstance();

try {
    // Démarrer la transaction
    $db->beginTransaction();
    
    // Opération 1: Insérer le mémoire
    $stmt = $db->execute(
        "INSERT INTO memoire (theme, resume, id_statut) VALUES (?, ?, ?)",
        [$theme, $resume, 1]
    );
    $memoireId = $db->lastInsertId();
    
    // Opération 2: Créer l'association
    $db->execute(
        "INSERT INTO deposer (id_user, id_memoire, date_action) VALUES (?, ?, NOW())",
        [$userId, $memoireId]
    );
    
    // Valider la transaction
    $db->commit();
    
    echo "✓ Mémoire déposé avec succès";
} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $db->rollback();
    
    error_log("Erreur dépôt mémoire: " . $e->getMessage());
    echo "✗ Erreur lors du dépôt";
}
            </div>
        </div>
        
        <h2>✓ Avantages des Transactions</h2>
        <div class="feature-box">
            <ul class="method-list" style="margin-top: 15px;">
                <li><span class="badge">Atomicité</span> Soit tout réussit, soit rien</li>
                <li><span class="badge">Cohérence</span> La BD reste dans un état valide</li>
                <li><span class="badge">Isolation</span> Les transactions concurrentes ne s'interfèrent pas</li>
                <li><span class="badge">Durabilité</span> Les changements validés sont permanents</li>
            </ul>
        </div>
        
        <h2>✓ Statut de Votre Base de Données</h2>
        <div class="feature-box">
            <span class="badge">✓ InnoDB</span> Moteur supportant les transactions<br>
            <span class="badge">✓ Prêt</span> Votre base de données est configurée pour les transactions
        </div>
        
        <div class="buttons">
            <a href="<?php echo APP_URL; ?>/db-test/info" class="btn btn-secondary">← Infos BD</a>
            <a href="<?php echo APP_URL; ?>/db-test/queries" class="btn btn-secondary">← Tests de Requêtes</a>
            <a href="<?php echo APP_URL; ?>" class="btn btn-secondary">← Accueil</a>
        </div>
    </div>
</body>
</html>

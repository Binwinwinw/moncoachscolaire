<?php
// dev/tools/exercises/check_db_status.php
// Vérifie l'état actuel de la table `exercises` (résumé + 3 derniers exercices)

define('PROJECT_ROOT', dirname(__DIR__, 3));

// Prefer legacy wrapper which will include src/database/connection.php when present
if (is_file(PROJECT_ROOT . '/db/connection.php')) {
    require_once PROJECT_ROOT . '/db/connection.php';
} elseif (is_file(PROJECT_ROOT . '/src/database/connection.php')) {
    require_once PROJECT_ROOT . '/src/database/connection.php';
}

// Récupérer le PDO via getConnection() si disponible, sinon via $GLOBALS['pdo']
$pdo = null;
$hasGetConnection = function_exists('getConnection');
if ($hasGetConnection) {
    $pdo = getConnection();
}
if (!$pdo && !empty($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
    $pdo = $GLOBALS['pdo'];
}

if (!$pdo) {
    // Diagnostic utile
    $envHost = getenv('DB_HOST') ?: '(unset)';
    $envDb = getenv('DB_DATABASE') ?: '(unset)';
    $envUser = getenv('DB_USERNAME') ?: '(unset)';

    echo "❌ Impossible de se connecter à la base de données. Diagnostic:\n";
    echo "  - getConnection() disponible: " . ($hasGetConnection ? 'oui' : 'non') . "\n";
    echo "  - DB_HOST (env): $envHost\n";
    echo "  - DB_DATABASE (env): $envDb\n";
    echo "  - DB_USERNAME (env): $envUser\n";
    echo "\nVérifiez .env ou src/config/config.php et relancez le script.\n";
    exit(1);
}

try {
    echo "\n📊 État de la table `exercises` — moncoachscolaire\n";
    echo str_repeat('═', 60) . "\n\n";

    $sql = "SELECT
        COUNT(*) AS Total,
        SUM(CASE WHEN Answer IS NOT NULL AND Answer != '' AND Answer != 'null' THEN 1 ELSE 0 END) AS Avec_reponse,
        SUM(CASE WHEN Answer IS NULL OR Answer = '' OR Answer = 'null' THEN 1 ELSE 0 END) AS Sans_reponse,
        ROUND(SUM(CASE WHEN Answer IS NOT NULL AND Answer != '' AND Answer != 'null' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS Pourcentage_complet
    FROM exercises";

    $stmt = $pdo->query($sql);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stats) {
        printf("📈 Total exercices       : %s\n", $stats['Total']);
        printf("✅ Avec réponse         : %s\n", $stats['Avec_reponse']);
        printf("⚠️  Sans réponse        : %s\n", $stats['Sans_reponse']);
        printf("📊 Pourcentage complet  : %s%%\n", $stats['Pourcentage_complet']);
    } else {
        echo "⚠️  Aucune donnée retournée par la requête\n";
    }

    // Section "3 derniers exercices" omise sur demande
    echo "\n📋 Section '3 derniers exercices' : omise (demande utilisateur)\n\n";
    // 3️⃣ Créés / modifiés dans les dernières 24 heures
    echo "\n⏱️  Activité dernières 24h\n";
    $sql3 = "SELECT
        SUM(CASE WHEN CreatedAt >= NOW() - INTERVAL 1 DAY THEN 1 ELSE 0 END) AS Created_last_24h,
        SUM(CASE WHEN UpdatedAt >= NOW() - INTERVAL 1 DAY THEN 1 ELSE 0 END) AS Updated_last_24h,
        COUNT(*) AS Rows_changed_last_24h
    FROM exercises
    WHERE COALESCE(UpdatedAt, CreatedAt) >= NOW() - INTERVAL 1 DAY";

    $stmt3 = $pdo->query($sql3);
    $stats24 = $stmt3->fetch(PDO::FETCH_ASSOC);

    printf("   • Créés (24h)        : %d\n", $stats24['Created_last_24h'] ?? 0);
    printf("   • Mis à jour (24h)   : %d\n", $stats24['Updated_last_24h'] ?? 0);
    printf("   • Lignes modifiées   : %d\n", $stats24['Rows_changed_last_24h'] ?? 0);

    // Lister les enregistrements modifiés dans les dernières 24h (si existants)
    $sql4 = "SELECT Identifier, Subject, Level, LEFT(Answer,50) AS Answer_preview, COALESCE(UpdatedAt, CreatedAt) AS ModifiedAt
             FROM exercises
             WHERE COALESCE(UpdatedAt, CreatedAt) >= NOW() - INTERVAL 1 DAY
             ORDER BY ModifiedAt DESC";
    $stmt4 = $pdo->query($sql4);
    $recent = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($recent)) {
        echo "\n📝 Modifications (24h) :\n";
        foreach ($recent as $rec) {
            $idf = $rec['Identifier'] ?? '(no id)';
            $when = $rec['ModifiedAt'] ?? '(n/a)';
            printf("   - %s  (%s)\n", $idf, $when);
        }
    }

    echo "\n✅ Vérification terminée.\n";
    exit(0);
} catch (PDOException $e) {
    echo "❌ Erreur SQL : " . $e->getMessage() . "\n";
    exit(1);
}

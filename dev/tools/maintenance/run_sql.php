<?php
// dev/tools/maintenance/run_sql.php
// Usage: php dev/tools/maintenance/run_sql.php dev/db/updates/file.sql

if (php_sapi_name() !== 'cli') {
    die("CLI only");
}

if ($argc < 2) {
    die("Usage: php run_sql.php <path_to_sql_file>\n");
}

$sqlFile = $argv[1];
if (!file_exists($sqlFile)) {
    die("File not found: $sqlFile\n");
}

$rootDir = dirname(dirname(dirname(__DIR__)));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🚀 Executing SQL: $sqlFile\n";

try {
    $sql = file_get_contents($sqlFile);
    // Split by semicolons if needed, but PDO might handle multiple queries depending on driver
    // MySQL driver usually supports it if ATTR_EMULATE_PREPARES is true (default often)
    // Safer to split just in case specific driver config forbids it

    // Simple split (frail but mostly ok for these generated files)
    $queries = explode(';', $sql);

    $pdo->beginTransaction();
    $count = 0;
    foreach ($queries as $q) {
        $q = trim($q);
        if (empty($q)) continue;

        $pdo->exec($q);
        $count++;
    }
    $pdo->commit();
    echo "✅ Success! $count queries executed.\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "❌ SQL Error: " . $e->getMessage() . "\n";
}

<?php
$root = __DIR__;
$indexFile = $root . '/index.php';
if (!is_file($indexFile)) {
    echo "❌ index.php introuvable à l’emplacement attendu : $indexFile\n";
    exit(1);
}
$content = file_get_contents($indexFile);
if ($content === false) {
    echo "❌ Impossible de lire index.php\n";
    exit(1);
}
$orig = $content;

// Corrections simples pour index.php
$replacements = [
    // Chemins d'accès
    "if (is_file(\$root . '/config.php'))" => "if (is_file(\$root . '/src/config/config.php'))",
    'if (is_file($root . \'/config.php\'))' => 'if (is_file($root . \'/src/config/config.php\'))',

    "\$root . '/config.php'" => "\$root . '/src/config/config.php'",
    "\$root . '/site_boot.php'" => "\$root . '/src/config/site_boot.php'",
    "\$root . '/includes/" => "\$root . '/src/includes/",
    "\$root . '/topbar.php'" => "\$root . '/src/includes/topbar.php'",
    "\$root . '/footer.php'" => "\$root . '/src/includes/footer.php'",

    // Pages
    "'/' . $postPage . ".php'" => "'/src/pages/' . $postPage . '.php'",
    "'/login.php'" => "'/src/pages/login.php'",
    "'/register.php'" => "'/src/pages/register.php'",
    "'/landingpage.php'" => "'/src/pages/landingpage.php'",

    // Aussi sans les variables
    "'/config.php'" => "'/src/config/config.php'",
    "'/site_boot.php'" => "'/src/config/site_boot.php'",
];

foreach ($replacements as $old => $new) {
    $content = str_replace($old, $new, $content);
}

if ($content !== $orig) {
    $result = file_put_contents($indexFile, $content);
    if ($result === false) {
        echo "❌ Impossible d’écrire dans index.php\n";
        exit(1);
    }
    echo "✅ index.php corrigé\n";
} else {
    echo "ℹ️ Pas de changements\n";
}
?>

<?php
/**
 * Audit CSS complet pour migration Tailwind
 * Usage : php dev/tools/css/audit_css_complet.php
 * Génère 4 rapports dans dev/reports/
 */

ini_set('memory_limit', '512M');
set_time_limit(300);
$start = microtime(true);

$baseDir = __DIR__;
$cssRoot = realpath($baseDir . '/../../../public/assets/css');
$reportDir = realpath($baseDir . '/../../reports');
if (!$cssRoot || !$reportDir) {
    fwrite(STDERR, "Erreur : Dossiers introuvables.\n");
    exit(1);
}

function getFileStats($file) {
    $size = filesize($file);
    $lines = count(file($file));
    $content = file_get_contents($file);
    return [$size, $lines, $content];
}

function detectPatterns($content) {
    $patterns = [
        'flex' => '/(display\s*:\s*flex)|(flex(-|\s|;|:))/i',
        'grid' => '/(display\s*:\s*grid)|(grid(-|\s|;|:))/i',
        'color' => '/(color\s*:\s*#[0-9a-fA-F]{3,6})|(color\s*:\s*rgba?\()|(background(-color)?\s*:)/i',
        'animation' => '/(@keyframes)|(animation(-|\s|:))/i',
    ];
    $scoreMap = [
        'flex' => 20,
        'grid' => 20,
        'color' => 15,
        'animation' => -10,
    ];
    $score = 50; // Score de base
    foreach ($patterns as $name => $pattern) {
        if (preg_match($pattern, $content)) {
            $score += $scoreMap[$name];
        }
    }
    // Détection séparée des hacks IE11
    if (
        preg_match('/filter:/i', $content) ||
        preg_match('/behavior:/i', $content) ||
        preg_match('/ms-/i', $content) ||
        preg_match('/progid:/i', $content) ||
        preg_match('/expression\\(/i', $content) ||
        preg_match('/\\0/', $content) ||
        preg_match('/\\9/', $content)
    ) {
        $score -= 20; // Pénalité IE11
    }
    return max(0, min(100, $score)); // Score entre 0 et 100
}

function getCssSelectors($content, $max = 3) {
    preg_match_all('/\.([a-zA-Z0-9_-]+)\s*[{,]/', $content, $matches);
    return array_slice(array_unique($matches[1]), 0, $max);
}

function findDuplicates($files) {
    $hashes = [];
    $dupes = [];
    foreach ($files as $file) {
        $hash = md5_file($file);
        if (isset($hashes[$hash])) {
            $dupes[] = [$file, $hashes[$hash]];
        } else {
            $hashes[$hash] = $file;
        }
    }
    return $dupes;
}

function findObsolete($files, $root) {
    // Recherche des inclusions dans les fichiers PHP du projet
    $phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname($root, 3)));
    $used = [];
    foreach ($phpFiles as $php) {
        if ($php->isFile() && strtolower($php->getExtension()) === 'php') {
            $content = file_get_contents($php->getPathname());
            foreach ($files as $css) {
                $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $css);
                if (strpos($content, $rel) !== false) {
                    $used[$css] = true;
                }
            }
        }
    }
    $obsolete = [];
    foreach ($files as $css) {
        if (empty($used[$css])) $obsolete[] = $css;
    }
    return $obsolete;
}

function statusEmoji($score) {
    if ($score >= 80) return ['✅', 'Facile'];
    if ($score >= 40) return ['⚠️', 'Partiel'];
    return ['❌', 'À conserver'];
}

function formatSize($kb) {
    return $kb < 1 ? round($kb * 1024) . ' o' : $kb . ' Ko';
}

// Fonction manquante : scanCssFiles
function scanCssFiles($root) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'css') {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

// 1️⃣ Scan
$cssFiles = scanCssFiles($cssRoot);
$total = count($cssFiles);
$totalSize = 0;
$totalLines = 0;
$filesData = [];

// 2️⃣ Analyse
$progressBar = function($done, $total) {
    $percent = round($done / $total * 100);
    $bar = str_repeat('█', round($percent/4)) . str_repeat(' ', 25-round($percent/4));
    printf("\r[%s] %d/%d fichiers analysés (%d%%)", $bar, $done, $total, $percent);
};

foreach ($cssFiles as $i => $file) {
    [$size, $lines, $content] = getFileStats($file);
    $score = detectPatterns($content);
    $selectors = getCssSelectors($content);
    $cat = 'global';
    if (strpos($file, 'components') !== false) $cat = 'components';
    elseif (strpos($file, 'pages') !== false) $cat = 'page-specific';
    elseif (strpos($file, 'util') !== false) $cat = 'utilities';
    elseif (strpos($file, 'anim') !== false) $cat = 'animations';
    elseif (strpos($file, 'responsive') !== false) $cat = 'responsive';
    [$emoji, $statut] = statusEmoji($score);
    $filesData[] = [
        'name' => basename($file),
        'path' => $file,
        'size_kb' => $size,
        'lines' => $lines,
        'category' => $cat,
        'tailwind_compatibility' => $score,
        'status' => $emoji,
        'selectors' => $selectors,
    ];
    $totalSize += $size;
    $totalLines += $lines;
    $progressBar($i+1, $total);
}

// 3️⃣ Doublons & obsolètes
$duplicates = findDuplicates($cssFiles);
$obsolete = findObsolete($cssFiles, $cssRoot);

// 4️⃣ Génération des rapports
$summary = "═══════════════════════════════════════════════════════════\n";
$summary .= "   AUDIT CSS - MIGRATION TAILWIND\n   Date : " . date('Y-m-d') . "\n";
$summary .= "═══════════════════════════════════════════════════════════\n\n";
$summary .= "📊 STATISTIQUES GLOBALES\n────────────────────────────────────────────────────────────\n";
$summary .= "Total fichiers CSS        : $total\n";
$summary .= "Taille totale            : ".formatSize($totalSize)."\n";
$summary .= "Lignes de code totales   : $totalLines\n\n";

// Répartition compatibilité
$facile = $partiel = $conserver = 0;
foreach ($filesData as $f) {
    if ($f['tailwind_compatibility'] >= 80) $facile++;
    elseif ($f['tailwind_compatibility'] >= 40) $partiel++;
    else $conserver++;
}
$summary .= "📈 RÉPARTITION PAR COMPATIBILITÉ TAILWIND\n────────────────────────────────────────────────────────────\n";
$summary .= "✅ Facile (>80%)         : $facile fichiers (".round($facile/$total*100)."%)\n";
$summary .= "⚠️ Partiel (40-80%)     : $partiel fichiers (".round($partiel/$total*100)."%)\n";
$summary .= "❌ À conserver (<40%)   : $conserver fichiers (".round($conserver/$total*100)."%)\n\n";

$summary .= "📂 LISTE COMPLÈTE DES FICHIERS\n────────────────────────────────────────────────────────────\n";
foreach ($filesData as $i => $f) {
    $summary .= ($i+1).'. '.str_pad($f['name'], 24).'| '.str_pad(formatSize($f['size_kb']),6).'| '.$f['status'].' '.str_pad($f['tailwind_compatibility'],3)."% | ".$f['category']."\n";
}

$summary .= "\n🎯 PRIORITÉS DE MIGRATION\n────────────────────────────────────────────────────────────\n";
$summary .= "Phase 1 (Quick wins)      : $facile fichiers | Gain estimé : -".(int)($facile*($totalSize/$total))." Ko\n";
$summary .= "Phase 2 (Composants)      : $partiel fichiers | Gain estimé : -".(int)($partiel*($totalSize/$total))." Ko\n";
$summary .= "Phase 3 (Pages spécifiques) : ".($total-$facile-$partiel-$conserver)." fichiers | Gain estimé : -".(int)(($total-$facile-$partiel-$conserver)*($totalSize/$total))." Ko\n";
$summary .= "Phase 4 (Styles complexes) : $conserver fichiers | Gain estimé : -".(int)($conserver*($totalSize/$total))." Ko\n";

$summary .= "\n💡 GAINS ESTIMÉS\n────────────────────────────────────────────────────────────\n";
$summary .= "Réduction de code CSS     : ~70% (-".(int)($totalSize*0.7)." Ko)\n";
$summary .= "Temps de migration estimé : 12-15 heures\n";

file_put_contents($reportDir.'/css_audit_summary.txt', $summary);

// Rapport détaillé JSON
$audit = [
    'audit_date' => date('c'),
    'totals' => [
        'files' => $total,
        'size_kb' => (int)$totalSize,
        'lines' => $totalLines
    ],
    'files' => $filesData,
    'duplicates' => $duplicates,
    'obsolete' => $obsolete,
    'recommendations' => []
];
file_put_contents($reportDir.'/css_audit_detailed.json', json_encode($audit, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

// Plan de migration (simplifié)
$plan = "# Plan de Migration CSS → Tailwind\n\n";
$plan .= "## Phase 1 : Quick Wins (2-3h)\n";
foreach ($filesData as $f) if ($f['tailwind_compatibility'] >= 80) $plan .= "- [ ] {$f['name']}\n";
$plan .= "\n## Phase 2 : Composants (4-5h)\n";
foreach ($filesData as $f) if ($f['category']==='components') $plan .= "- [ ] {$f['name']}\n";
$plan .= "\n## Phase 3 : Pages spécifiques (3-4h)\n";
foreach ($filesData as $f) if ($f['category']==='page-specific') $plan .= "- [ ] {$f['name']}\n";
$plan .= "\n## Phase 4 : Styles complexes (3-4h)\n";
foreach ($filesData as $f) if ($f['tailwind_compatibility'] < 40) $plan .= "- [ ] {$f['name']}\n";
$plan .= "\n## Checklist de validation\n- [ ] Test responsive (mobile, tablet, desktop)\n- [ ] Vérification accessibilité (contraste, focus)\n";
file_put_contents($reportDir.'/css_migration_plan.md', $plan);

// Exemples de migration (avant/après)
$examples = "# Exemples de Migration CSS → Tailwind\n\n";
$examples .= "## Exemple 1 : Conteneur centré\n\n### ❌ Avant (CSS custom)\n.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }\n\n### ✅ Après (Tailwind)\n<div class=\"max-w-screen-xl mx-auto px-5\">\n\n";
$examples .= "## Exemple 2 : Bouton primaire\n\n### ❌ Avant (CSS custom)\n.btn-primary { background: #059669; color: #fff; padding: 1rem 2rem; border-radius: 8px; }\n\n### ✅ Après (Tailwind)\n<button class=\"bg-emerald-600 text-white px-8 py-4 rounded-lg\">\n\n";
file_put_contents($reportDir.'/css_examples_migration.md', $examples);

// 5️⃣ Console finale
$elapsed = round(microtime(true) - $start, 2);
echo "\n\n✅ Analyse terminée en {$elapsed}s\n";
echo "\n📊 RÉSULTATS GLOBAUX\n────────────────────────────────────────────────────────────\n";
echo "Total fichiers        : $total\n";
echo "Taille totale         : ".formatSize($totalSize)."\n";
echo "Lignes totales        : $totalLines\n";
echo "✅ Facile (>80%)      : $facile fichiers (".round($facile/$total*100)."%)\n";
echo "⚠️ Partiel (40-80%)  : $partiel fichiers (".round($partiel/$total*100)."%)\n";
echo "❌ À conserver (<40%) : $conserver fichiers (".round($conserver/$total*100)."%)\n";
echo "\n📄 Rapports générés :\n";
echo "✅ $reportDir/css_audit_summary.txt\n";
echo "✅ $reportDir/css_audit_detailed.json\n";
echo "✅ $reportDir/css_migration_plan.md\n";
echo "✅ $reportDir/css_examples_migration.md\n";
echo "\n💡 Gain estimé migration : -".(int)($totalSize*0.7)." Ko (~70%)\n";
echo "⏱️ Temps estimé migration : 12-15h\n";
echo "\n🎉 AUDIT COMPLET TERMINÉ !\n";

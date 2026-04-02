<?php
// dev/tools/auto_sort_tools.php
// Script pour ranger automatiquement les outils dans dev/tools/* selon leur rôle


$base = __DIR__;
$folders = [
    'debug', 'import_export', 'maintenance', 'tests', 'migration', 'security', 'utils', 'docs', 'python_scripts'
];

$patterns = [
    'debug'         => ['debug', 'audit', 'diagnose', 'analy', 'check_headers'],
    'import_export' => ['import', 'export', 'convert', 'extract', 'dump'],
    'maintenance'   => ['clean', 'cleanup', 'fix', 'remove', 'restore', 'optimize', 'nettoyer', 'sanitize', 'update', 'fill', 'backup'],
    'tests'         => ['test', 'verify', 'describe', 'phpunit', 'quick_test'],
    'migration'     => ['migrate', 'migration', 'apply_migration', 'setup-db', 'teardown-db', 'sync'],
    'security'      => ['security', 'scan', 'check_env', 'check_composer', 'check-db-tables'],
    'utils'         => ['generate', 'create', 'list', 'show', 'number', 'find', 'script', 'utils', 'helper', 'force_', 'page', 'rapport', 'render'],
    'docs'          => ['README', 'GUIDE', 'INSTALL', 'RESOURCES', 'doc', 'example']
];

// Ne pas déplacer les dossiers déjà créés
$skip = array_merge($folders, ['db', 'test-results', '__pycache__']);

$files = scandir($base);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $base . DIRECTORY_SEPARATOR . $file;
    if (is_dir($path) && in_array($file, $skip)) continue;
    if (is_dir($path)) continue; // On ne traite que les fichiers à la racine de tools
    $moved = false;
    // Déplacement direct des .py non classés
    if (preg_match('/\.py$/i', $file)) {
        $dest = $base . DIRECTORY_SEPARATOR . 'python_scripts' . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($dest)) {
            rename($path, $dest);
            echo "[OK] $file -> python_scripts\n";
        }
        $moved = true;
    }
    // Rangement manuel pour les fichiers restants
    if (!$moved) {
        // Mapping manuel par nom de fichier (complété avec les derniers fichiers)
        $manual_map = [
            // debug
            'check_bom.php' => 'debug',
            'display_parsing_details.php' => 'debug',
            'investigate_487_exercises.php' => 'debug',
            'inspect_braces.php' => 'debug',
            // import_export
            'affichage_exemple_3eme_math.php' => 'import_export',
            'affichage_exemple_6eme_anglais.php' => 'import_export',
            'affichage_exemple_6eme_anglais_enriched.php' => 'import_export',
            'affichage_exemple_exercices_6eme_md.php' => 'import_export',
            'creer_exercices_anglais.php' => 'import_export',
            // maintenance
            'clear_session.php' => 'maintenance',
            'delete_philosophie_exercises.php' => 'maintenance',
            'supprimer_philosophie.php' => 'maintenance',
            'normalize_exercise_levels.php' => 'maintenance',
            'improve_exercises_structure.php' => 'maintenance',
            'reactiver_anglais.php' => 'maintenance',
            'setup_local_database.php' => 'maintenance',
            // tests
            'run_api_cli.php' => 'tests',
            'ex1_check_coherence.php' => 'tests',
            'ex2_enrich_instructions.php' => 'tests',
            'ex4_set_difficulty.php' => 'tests',
            'ex5_detect_answertype.php' => 'tests',
            'validateur_exercices.php' => 'tests',
            'validate_exercises.php' => 'tests',
            'validate_exercises_coherence.php' => 'tests',
            'check_empty_answers.php' => 'tests',
            'check_exercises_counts.php' => 'tests',
            'check_exercises_counts_all.php' => 'tests',
            // utils
            'api_proxy.php' => 'utils',
            'setup_users_for_parents.php' => 'utils',
            'setup_scheduled_tasks.ps1' => 'utils',
            'setup_hybrid_hostinger.sh' => 'utils',
            'login_admin_quick.php' => 'utils',
            'check_courses_tables.php' => 'utils',
            'check_exercises_in_file.php' => 'utils',
            'check_exercise_answers.php' => 'utils',
            'check_answers.php' => 'utils',
            'check_bac_exercises.php' => 'utils',
            'check_613.php' => 'utils',
            'check_id_625.php' => 'utils',
            'check_inline_styles.php' => 'utils',
            'check_non_fournie.php' => 'utils',
            'check_router_integration.php' => 'utils',
            'check_topbar_footer_css.php' => 'utils',
            'confirmed_unused_css_classes.txt' => 'utils',
            'empty_exercises_report.txt' => 'utils',
            'pdf_data.json' => 'utils',
            'preview_webm.html' => 'utils',
            'report_hybrid_before_after.php' => 'utils',
            'setup_hybrid_hostinger.sh' => 'utils',
            'snapshot.php' => 'utils',
            'snapshot.ps1' => 'utils',
            'stats_exercises.php' => 'utils',
            'unused_css_classes.txt' => 'utils',
            'verification_hebdomadaire.bat' => 'utils',
            // demo & outils d'accès
            'demo_dashboard_api.php' => 'debug',
            'demo_login.php' => 'utils',
            // audit ciblé
            'examine_suspects.php' => 'debug',
            // config/env
            'set_production_env.php' => 'maintenance',
        ];
        if (isset($manual_map[$file])) {
            $dest = $base . DIRECTORY_SEPARATOR . $manual_map[$file] . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($dest)) {
                rename($path, $dest);
                echo "[OK] $file -> {$manual_map[$file]}\n";
            }
            $moved = true;
        }
    }
    if (!$moved) {
        foreach ($patterns as $folder => $keywords) {
            foreach ($keywords as $kw) {
                if (stripos($file, $kw) !== false) {
                    $dest = $base . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $file;
                    if (!file_exists($dest)) {
                        rename($path, $dest);
                        echo "[OK] $file -> $folder\n";
                    }
                    $moved = true;
                    break 2;
                }
            }
        }
    }
    if (!$moved) {
        echo "[SKIP] $file (non classé)\n";
    }
}
echo "--- Tri automatique terminé ---\n";

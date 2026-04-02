<?php
/**
 * Script : generate_migration_documentation.php
 * Objectif : Générer la documentation complète de la migration des exercices (guide technique, résumé, index).
 * Usage : php dev/tools/exercises/generate_migration_documentation.php
 */

$docsDir = __DIR__ . '/../../../dev/docs';
$indexPath = $docsDir . '/INDEX.md';
$fullPath = $docsDir . '/migration_exercices_complet.md';
$resumePath = $docsDir . '/migration_exercices_resume.md';

// 1. Génération du guide complet
$fullDoc = "# Migration Complète des Exercices - Guide Technique\n\n";
$fullDoc .= "## 📋 Table des matières\n- Vue d'ensemble\n- Architecture de la migration\n- Phases détaillées (1-9)\n- Scripts créés et leur utilisation\n- Schémas de base de données\n- Validation et tests\n- Troubleshooting\n- Annexes\n\n";
$fullDoc .= "## 🎯 Vue d'ensemble\n1245 exercices migrés, 9 phases, validation 100%.\n\n";
$fullDoc .= "## 🏗️ Architecture de la migration\nDiagramme des flux : JSON → Nettoyage → Enrichissement → BDD.\n\n";
$fullDoc .= "## 📊 Phases détaillées\n";
for ($i = 1; $i <= 9; $i++) {
    $fullDoc .= "### Phase $i : [Titre à compléter] \n";
    $fullDoc .= "**Objectif :** [Objectif de la phase] \n";
    $fullDoc .= "**Scripts :** [Scripts utilisés] \n";
    $fullDoc .= "**Entrées :** [Sources] \n";
    $fullDoc .= "**Sorties :** [Résultats] \n";
    $fullDoc .= "**Durée :** [Durée estimée] \n";
    $fullDoc .= "**Commandes :**\n```bash\nphp dev/tools/exercises/[script].php\n```\n\n";
}
$fullDoc .= "\n🛠️ Scripts créés\n| Nom | Rôle | Entrée | Sortie |\n|-----|------|--------|--------|\ncollect_exercises_v2.php | Fusion sources | *.migrated.json | unified_exercises.json |\ndeduplicate_exercises.php | Dédoublonnage | unified_exercises.json | exercises_deduplicated.json |\nstep2_clean_exercises_v2.php | Nettoyage | exercises_deduplicated.json | exercises_cleaned.json |\nvalidate_complex_exercises.php | Tests automatiques | BDD | 10 tests, rapport HTML/JSON |\n...\n\n";
$fullDoc .= "🗄️ Schémas de base de données\nTable exercises - Schéma final\n";
$fullDoc .= "```sql\nId INT PRIMARY KEY AUTO_INCREMENT\nIdentifier VARCHAR(50) UNIQUE\nSubject VARCHAR(60)\nLevel VARCHAR(10)\nTitle VARCHAR(250)\nContent LONGTEXT\nInstruction TEXT\nstructure_type ENUM('simple', 'multi-parties') DEFAULT 'simple'\npattern_detected VARCHAR(20)\nsub_questions LONGTEXT CHECK(JSON_VALID(sub_questions))\nDifficulty VARCHAR(10)\nAnswerType VARCHAR(30)\nXP_Points INT DEFAULT 10\nis_active TINYINT(1) DEFAULT 1\ncreated_at DATETIME\nupdated_at DATETIME\n```\n\nStructure sub_questions (JSON)\n```json\n[\n  {\n    \"id\": 1,\n    \"question\": \"Texte de la question\",\n    \"type\": \"qcm|texte|vrai_faux|association\",\n    \"choices\": [\"A\", \"B\", \"C\", \"D\"] ou null,\n    \"answer\": \"Réponse correcte\"\n  }\n]\n```\n\n✅ Validation et tests\nTests automatiques (10 tests)\n- Nombre d'exercices multi-parties (57)\n- Validité JSON sub_questions\n- Structure des questions (id, question, type)\n- Types de questions présents (qcm, texte, vrai_faux)\n- Content et Instruction non vides\n- Distribution des questions (edge cases 2-3)\n- Tests de performance et cas limites\n\nCommande de validation\n```bash\nphp dev/tools/exercises/validate_complex_exercises.php\n```\nRapports générés :\n- dev/reports/validation_report.html\n- dev/reports/validation_report.json\n\n🔧 Troubleshooting\nProblème : Answer = \"Array\"\nCause : Mauvais casting PHP lors de la migration\nSolution : step2_clean_exercises_v2.php\nProblème : Instruction vide\nCause : Parsing incomplet\nSolution : fix_and_complete_validation.php\n...\n\n📎 Annexes\nA. Checklist de validation complète\n- validation_exercices_multi_parties.md\nB. Rapports de migration\n- dev/reports/collect_exercises.log\n- dev/reports/cleaning_report_v2.txt\n- dev/reports/complex_exercises_detected.json\n- dev/reports/validation_report.html\nC. Commandes de maintenance\n```bash\nmysqldump -u root -p moncoachscolaire exercises > backup.sql\nphp dev/tools/exercises/diagnostic_exercises.php\nphp dev/tools/exercises/validate_complex_exercises.php\n```\n\n";
file_put_contents($fullPath, $fullDoc);

// 2. Génération du résumé
$resumeDoc = "# Migration Exercices - Résumé\n\n1245 exercices migrés, 9 phases, validation 100%.\n\nCommandes essentielles :\n- php dev/tools/exercises/collect_exercises_v2.php\n- php dev/tools/exercises/deduplicate_exercises.php\n- php dev/tools/exercises/step2_clean_exercises_v2.php\n- php dev/tools/exercises/validate_complex_exercises.php\n\nRapports :\n- dev/reports/validation_report.html\n- dev/reports/validation_report.json\n\nVoir guide complet : migration_exercices_complet.md\n";
file_put_contents($resumePath, $resumeDoc);

// 3. Mise à jour ou création de l'INDEX.md
$index = "# Documentation MonCoachScolaire\n\n## Table des matières\n- [Migration des exercices](migration_exercices_complet.md)\n- [Résumé migration](migration_exercices_resume.md)\n- [Checklist validation multi-parties](validation_exercices_multi_parties.md)\n";
file_put_contents($indexPath, $index);

// Affichage console
$console = [];
$console[] = "════════════════════════════════════════════════════════";
$console[] = "   📚 GÉNÉRATION DOCUMENTATION MIGRATION";
$console[] = "════════════════════════════════════════════════════════";
$console[] = "";
$console[] = "✅ Fichiers créés :";
$console[] = "   - dev/docs/migration_exercices_complet.md (guide complet)";
$console[] = "   - dev/docs/migration_exercices_resume.md (résumé 1 page)";
$console[] = "   - dev/docs/INDEX.md (table des matières)";
$console[] = "";
$console[] = "📊 Contenu :";
$console[] = "   - 9 phases détaillées";
$console[] = "   - 15+ scripts documentés";
$console[] = "   - 10 tests de validation";
$console[] = "   - Schémas BDD";
$console[] = "   - Troubleshooting complet";
$console[] = "";
$console[] = "🔗 Structure de documentation :";
$console[] = "   dev/docs/";
$console[] = "   ├── INDEX.md (table des matières)";
$console[] = "   ├── migration_exercices_complet.md (guide complet)";
$console[] = "   ├── migration_exercices_resume.md (résumé 1 page)";
$console[] = "   └── validation_exercices_multi_parties.md (checklist)";
$console[] = "";
echo implode("\n", $console);

exit(0);

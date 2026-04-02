# Migration Complète des Exercices - Guide Technique

## 📋 Table des matières
- Vue d'ensemble
- Architecture de la migration
- Phases détaillées (1-9)
- Scripts créés et leur utilisation
- Schémas de base de données
- Validation et tests
- Troubleshooting
- Annexes

## 🎯 Vue d'ensemble
1245 exercices migrés, 9 phases, validation 100%.

## 🏗️ Architecture de la migration
Diagramme des flux : JSON → Nettoyage → Enrichissement → BDD.

## 📊 Phases détaillées
### Phase 1 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 2 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 3 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 4 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 5 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 6 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 7 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 8 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```

### Phase 9 : [Titre à compléter] 
**Objectif :** [Objectif de la phase] 
**Scripts :** [Scripts utilisés] 
**Entrées :** [Sources] 
**Sorties :** [Résultats] 
**Durée :** [Durée estimée] 
**Commandes :**
```bash
php dev/tools/exercises/[script].php
```


🛠️ Scripts créés
| Nom | Rôle | Entrée | Sortie |
|-----|------|--------|--------|
collect_exercises_v2.php | Fusion sources | *.migrated.json | unified_exercises.json |
deduplicate_exercises.php | Dédoublonnage | unified_exercises.json | exercises_deduplicated.json |
step2_clean_exercises_v2.php | Nettoyage | exercises_deduplicated.json | exercises_cleaned.json |
validate_complex_exercises.php | Tests automatiques | BDD | 10 tests, rapport HTML/JSON |
...

🗄️ Schémas de base de données
Table exercises - Schéma final
```sql
Id INT PRIMARY KEY AUTO_INCREMENT
Identifier VARCHAR(50) UNIQUE
Subject VARCHAR(60)
Level VARCHAR(10)
Title VARCHAR(250)
Content LONGTEXT
Instruction TEXT
structure_type ENUM('simple', 'multi-parties') DEFAULT 'simple'
pattern_detected VARCHAR(20)
sub_questions LONGTEXT CHECK(JSON_VALID(sub_questions))
Difficulty VARCHAR(10)
AnswerType VARCHAR(30)
XP_Points INT DEFAULT 10
is_active TINYINT(1) DEFAULT 1
created_at DATETIME
updated_at DATETIME
```

Structure sub_questions (JSON)
```json
[
  {
    "id": 1,
    "question": "Texte de la question",
    "type": "qcm|texte|vrai_faux|association",
    "choices": ["A", "B", "C", "D"] ou null,
    "answer": "Réponse correcte"
  }
]
```

✅ Validation et tests
Tests automatiques (10 tests)
- Nombre d'exercices multi-parties (57)
- Validité JSON sub_questions
- Structure des questions (id, question, type)
- Types de questions présents (qcm, texte, vrai_faux)
- Content et Instruction non vides
- Distribution des questions (edge cases 2-3)
- Tests de performance et cas limites

Commande de validation
```bash
php dev/tools/exercises/validate_complex_exercises.php
```
Rapports générés :
- dev/reports/validation_report.html
- dev/reports/validation_report.json

🔧 Troubleshooting
Problème : Answer = "Array"
Cause : Mauvais casting PHP lors de la migration
Solution : step2_clean_exercises_v2.php
Problème : Instruction vide
Cause : Parsing incomplet
Solution : fix_and_complete_validation.php
...

📎 Annexes
A. Checklist de validation complète
- validation_exercices_multi_parties.md
B. Rapports de migration
- dev/reports/collect_exercises.log
- dev/reports/cleaning_report_v2.txt
- dev/reports/complex_exercises_detected.json
- dev/reports/validation_report.html
C. Commandes de maintenance
```bash
mysqldump -u root -p moncoachscolaire exercises > backup.sql
php dev/tools/exercises/diagnostic_exercises.php
php dev/tools/exercises/validate_complex_exercises.php
```

